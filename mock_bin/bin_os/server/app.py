"""FastAPI application — serves the bin's touchscreen UI and control API."""

from __future__ import annotations

import asyncio
import base64
import json
import time
from pathlib import Path
from typing import Any

import cv2
import numpy as np
from fastapi import FastAPI, Request, WebSocket, WebSocketDisconnect
from fastapi.responses import HTMLResponse, StreamingResponse
from fastapi.staticfiles import StaticFiles
from starlette.templating import Jinja2Templates

from bin_os.brain import BinBrain, BinState
from bin_os.config import AppConfig
from bin_os.network.api_client import BackendClient
from bin_os.network.offline_queue import OfflineQueue
from bin_os.vision.base_detector import BaseDetector
from bin_os.vision.camera import Camera

UI_DIR = Path(__file__).parent.parent.parent / "ui"


def create_app(
    config: AppConfig,
    brain: BinBrain,
    camera: Camera,
    detector: BaseDetector,
    backend: BackendClient,
) -> FastAPI:
    app = FastAPI(title=f"Mobius Bin {config.bin.serial}")
    app.mount("/static", StaticFiles(directory=str(UI_DIR / "static")), name="static")
    templates = Jinja2Templates(directory=str(UI_DIR / "templates"))
    queue = OfflineQueue()

    # WebSocket connections for live UI updates
    ws_clients: list[WebSocket] = []

    async def broadcast(event: str, data: Any) -> None:
        msg = json.dumps({"event": event, "data": data})
        dead = []
        for ws in ws_clients:
            try:
                await ws.send_text(msg)
            except Exception:
                dead.append(ws)
        for ws in dead:
            ws_clients.remove(ws)

    # Wire brain events to WebSocket broadcast
    brain.on_event(broadcast)

    # ── UI ──────────────────────────────────────────────

    @app.get("/", response_class=HTMLResponse)
    async def display(request: Request):
        return templates.TemplateResponse(request, "display.html", {
            "config": config,
            "brain": brain,
        })

    # ── WebSocket ───────────────────────────────────────

    @app.websocket("/ws")
    async def websocket_endpoint(ws: WebSocket):
        await ws.accept()
        ws_clients.append(ws)
        # Send initial state
        await ws.send_text(json.dumps({
            "event": "init",
            "data": brain.status_dict(),
        }))
        try:
            while True:
                text = await ws.receive_text()
                try:
                    cmd = json.loads(text)
                    await handle_ws_command(cmd)
                except json.JSONDecodeError:
                    pass
        except WebSocketDisconnect:
            if ws in ws_clients:
                ws_clients.remove(ws)

    # Track last detection for feedback
    last_detection_event_id: int | None = None
    last_brand: str = ""
    last_upload_b64: str | None = None

    async def handle_ws_command(cmd: dict) -> None:
        nonlocal last_detection_event_id, last_brand, last_upload_b64
        action = cmd.get("action")
        if action == "simulate_deposit":
            waste_type = cmd.get("waste_type", "paper_cup")
            confidence = cmd.get("confidence", 85)
            last_upload_b64 = None
            await run_detection(waste_type_override=waste_type, confidence_override=confidence)
        elif action == "capture_and_detect":
            last_upload_b64 = None
            await run_detection()
        elif action == "upload_and_detect":
            image_b64 = cmd.get("image_base64", "")
            await run_detection_from_upload(image_b64)
        elif action == "advance_to_feedback":
            await brain.advance_to_feedback()
        elif action == "submit_feedback":
            accurate = cmd.get("accurate", True)
            if last_detection_event_id:
                await backend.send_feedback(last_detection_event_id, accurate)
            await brain.complete_feedback()
        elif action == "reset_fill":
            await brain.reset_compartments()
            await broadcast("reset", brain.status_dict())
        elif action == "maintenance_on":
            await brain.enter_maintenance()
        elif action == "maintenance_off":
            await brain.exit_maintenance()

    # ── Camera MJPEG stream ─────────────────────────────

    @app.get("/camera/stream")
    async def camera_stream():
        if not camera.is_open():
            return StreamingResponse(iter([b""]), media_type="text/plain")

        def generate():
            while True:
                frame = camera.capture_frame_sync()
                if frame is None:
                    break
                _, buffer = cv2.imencode(".jpg", frame, [cv2.IMWRITE_JPEG_QUALITY, 70])
                yield (
                    b"--frame\r\n"
                    b"Content-Type: image/jpeg\r\n\r\n"
                    + buffer.tobytes()
                    + b"\r\n"
                )
                time.sleep(0.066)  # ~15fps

        return StreamingResponse(generate(), media_type="multipart/x-mixed-replace; boundary=frame")

    # ── API ─────────────────────────────────────────────

    @app.get("/api/status")
    async def api_status():
        backend_ok = await backend.health_check()
        detector_ok = await detector.health_check()
        return {
            "bin": brain.status_dict(),
            "camera": camera.is_open(),
            "detector": detector_ok,
            "backend": backend_ok,
            "queued": queue.count(),
        }

    @app.post("/api/detect")
    async def api_detect(waste_type: str | None = None, confidence: int | None = None):
        """Trigger a detection cycle. Optionally override waste_type for simulation."""
        if waste_type and confidence is not None:
            result = await run_detection(waste_type_override=waste_type, confidence_override=confidence)
        else:
            result = await run_detection()
        return {"result": result}

    @app.post("/api/reset")
    async def api_reset():
        await brain.reset_compartments()
        return {"message": "Compartments reset", "fill_level": brain.fill_level}

    # ── Detection logic ─────────────────────────────────

    async def _post_classification(
        waste_type: str, confidence: int, brand: str, image_bytes: bytes | None,
    ) -> dict:
        """Shared logic after classification: threshold → deposit → report to backend."""
        nonlocal last_detection_event_id, last_brand

        last_brand = brand
        last_detection_event_id = None

        if waste_type == "unknown" or confidence < config.vision.confidence_threshold:
            await broadcast("detection_rejected", {"waste_type": waste_type, "confidence": confidence})
            return {"waste_type": waste_type, "confidence": confidence, "accepted": False}

        # Run the full deposit procedure (state machine handles timing)
        result = await brain.process_detection(waste_type, confidence, brand=brand)

        # Report to backend (queue on failure)
        bin_id = await backend.resolve_bin_id()
        backend_response = None
        if bin_id:
            backend_response = await backend.send_detection(
                bin_id=bin_id,
                waste_type=result.waste_type,
                confidence=result.confidence,
                weight_g=round(result.weight_g + (result.liquid_ml if result.needs_drain else 0)),
                image_bytes=image_bytes,
                brand=brand,
            )

            if backend_response is not None:
                last_detection_event_id = backend_response.get("data", {}).get("id")
            else:
                queue.enqueue(bin_id, result.waste_type, result.confidence, image_bytes)

        # Broadcast detection_event_id so UI can use it for feedback
        await broadcast("detection_meta", {
            "detection_event_id": last_detection_event_id,
            "brand": brand,
        })

        return {
            "waste_type": result.waste_type,
            "confidence": result.confidence,
            "fill_level": brain.fill_level,
            "liquid_drained_ml": round(result.liquid_ml) if result.needs_drain else 0,
            "accepted": True,
            "backend_reported": backend_response is not None,
            "queued": backend_response is None and bin_id is not None,
        }

    async def run_detection(
        waste_type_override: str | None = None,
        confidence_override: int | None = None,
    ) -> dict:
        if brain.state not in (BinState.IDLE, BinState.RESULT):
            return {"error": "Bin is busy", "state": brain.state.value}

        if brain.is_on_cooldown:
            return {"error": "Detection cooldown active"}

        # Classify (camera or override)
        brand = ""
        image_bytes = None
        if waste_type_override and confidence_override is not None:
            waste_type = waste_type_override
            confidence = confidence_override
        else:
            frame = await camera.capture_frame()
            if frame is None:
                return {"error": "Camera not available"}
            classification = await detector.classify(frame)
            waste_type = classification.waste_type
            confidence = classification.confidence
            brand = classification.brand
            _, buf = cv2.imencode(".jpg", frame)
            image_bytes = buf.tobytes()

        return await _post_classification(waste_type, confidence, brand, image_bytes)

    async def run_detection_from_upload(image_b64: str) -> dict:
        """Decode an uploaded base64 image and run detection on it."""
        nonlocal last_upload_b64

        if brain.state not in (BinState.IDLE, BinState.RESULT):
            await broadcast("upload_error", {"error": "Bin is busy"})
            return {"error": "Bin is busy"}

        if brain.is_on_cooldown:
            await broadcast("upload_error", {"error": "Detection cooldown active"})
            return {"error": "Detection cooldown active"}

        try:
            img_bytes = base64.b64decode(image_b64)
            nparr = np.frombuffer(img_bytes, np.uint8)
            frame = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
        except Exception:
            await broadcast("upload_error", {"error": "Invalid image data"})
            return {"error": "Invalid image data"}

        if frame is None:
            await broadcast("upload_error", {"error": "Could not decode image"})
            return {"error": "Could not decode image"}

        last_upload_b64 = image_b64

        # Broadcast uploaded image so UI can display it through state transitions
        await broadcast("uploaded_image", {"image_base64": image_b64})

        # Classify
        classification = await detector.classify(frame)

        # Encode for backend
        _, buf = cv2.imencode(".jpg", frame)
        image_bytes = buf.tobytes()

        return await _post_classification(
            classification.waste_type,
            classification.confidence,
            classification.brand,
            image_bytes,
        )

    # ── Offline queue drain ──────────────────────────────

    async def drain_queue() -> int:
        """Try to send all queued detections. Returns count of successfully sent."""
        sent = 0
        for item in queue.pending():
            resp = await backend.send_detection(
                bin_id=item.bin_id,
                waste_type=item.waste_type,
                confidence=item.confidence,
                image_bytes=item.image_bytes,
            )
            if resp is not None:
                queue.remove(item.id)
                sent += 1
            else:
                break  # backend still down, stop trying
        return sent

    # ── Background tasks ────────────────────────────────

    @app.on_event("startup")
    async def startup():
        await brain.boot()
        asyncio.create_task(heartbeat_loop())

    async def heartbeat_loop():
        while True:
            await asyncio.sleep(config.timers.heartbeat_interval_sec)
            bin_id = await backend.resolve_bin_id()
            if bin_id:
                ok = await backend.heartbeat(
                    bin_id=bin_id,
                    fill_level=brain.fill_level,
                    ip_address=f"{config.server.host}:{config.server.port}",
                    compartments=brain.compartments_dict(),
                )
                await broadcast("heartbeat", {"success": ok, "fill_level": brain.fill_level})

                # Drain offline queue on successful heartbeat
                if ok and queue.count() > 0:
                    sent = await drain_queue()
                    if sent > 0:
                        await broadcast("queue_drained", {"sent": sent, "remaining": queue.count()})

    return app
