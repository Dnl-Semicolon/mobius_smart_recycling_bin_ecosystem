#!/usr/bin/env python3
"""
Drag-and-drop cup/lid detection UI.

Opens a browser tab where you can upload or drag a photo
and instantly see what the model detects.

Usage:
    python ai/src/detect_ui.py                                             # COCO pretrained
    python ai/src/detect_ui.py --model ai/models/yolov11n_waste_v1/weights/best.pt  # your model
"""
from __future__ import annotations

import argparse
import time
from pathlib import Path

import gradio as gr
import numpy as np
from ultralytics import YOLO

CLASS_COLORS = {
    "cup": "#22c55e",
    "lid": "#3b82f6",
    "straw": "#eab308",
    "liquid_waste": "#ef4444",
}


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser()
    parser.add_argument("--model", default="yolo11n.pt")
    parser.add_argument("--conf", type=float, default=0.25)
    parser.add_argument("--port", type=int, default=7860)
    return parser.parse_args()


def build_app(model_path: str, conf: float) -> gr.Blocks:
    model = YOLO(model_path)
    version = Path(model_path).stem
    if version in ("best", "last"):
        version = Path(model_path).parents[1].name

    def detect(image: np.ndarray | None) -> tuple[np.ndarray | None, str]:
        if image is None:
            return None, "Upload an image to get started."

        t0 = time.perf_counter()
        results = model.predict(source=image, conf=conf, imgsz=640, verbose=False)
        ms = (time.perf_counter() - t0) * 1000

        r = results[0]
        annotated = r.plot()

        lines = [f"**Inference: {ms:.0f} ms** | Model: `{version}`\n"]
        if r.boxes is None or len(r.boxes) == 0:
            lines.append("No detections. Try a clearer photo of a cup or lid.")
        else:
            for i in range(len(r.boxes)):
                cls = model.names[int(r.boxes.cls[i].item())]
                c = float(r.boxes.conf[i].item())
                lines.append(f"- **{cls}** — {c:.0%}")

        return annotated[:, :, ::-1], "\n".join(lines)

    with gr.Blocks(title="Mobius Cup Detector", theme=gr.themes.Soft()) as app:
        gr.Markdown("# Mobius Cup Detector\nDrag a photo or use your webcam.")

        with gr.Row():
            inp = gr.Image(label="Input", sources=["upload", "webcam", "clipboard"])
            out = gr.Image(label="Detections")

        info = gr.Markdown("")
        inp.change(fn=detect, inputs=inp, outputs=[out, info])

    return app


if __name__ == "__main__":
    args = parse_args()
    app = build_app(args.model, args.conf)
    app.launch(server_port=args.port, inbrowser=True)
