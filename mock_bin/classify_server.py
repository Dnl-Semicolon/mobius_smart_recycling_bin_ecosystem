"""
Thin FastAPI server that exposes waste classification via HTTP.
Reuses existing detector classes from bin_os/vision/.

Usage:
    cd mock_bin
    python classify_server.py                    # mock detector (no deps)
    python classify_server.py --detector yolo    # YOLO model
    python classify_server.py --detector openai  # OpenAI Vision
    python classify_server.py --detector hybrid  # YOLO + OpenAI
"""

import argparse

import cv2
import numpy as np
import uvicorn
from fastapi import FastAPI, File, UploadFile
from fastapi.middleware.cors import CORSMiddleware

from bin_os.vision.base_detector import BaseDetector, ClassificationResult
from bin_os.vision.mock_detector import MockDetector


def load_detector(detector_type: str, model_path: str | None = None) -> BaseDetector:
    """Load the requested detector. Imports are deferred to avoid pulling
    heavy deps (torch, openai) when using the mock detector."""
    if detector_type == "mock":
        return MockDetector()
    elif detector_type == "yolo":
        from bin_os.vision.yolo_detector import YOLODetector
        return YOLODetector(model_path=model_path or "models/best.pt")
    elif detector_type == "openai":
        from bin_os.vision.openai_detector import OpenAIDetector
        return OpenAIDetector()
    elif detector_type == "hybrid":
        from bin_os.vision.hybrid_detector import HybridDetector
        return HybridDetector(model_path=model_path or "models/best.pt")
    else:
        print(f"Unknown detector: {detector_type}. Using mock.")
        return MockDetector()


def create_app(detector: BaseDetector) -> FastAPI:
    app = FastAPI(title="Mobius AI Classifier", version="0.1.0")

    app.add_middleware(
        CORSMiddleware,
        allow_origins=["*"],
        allow_methods=["*"],
        allow_headers=["*"],
    )

    @app.get("/health")
    async def health():
        ok = await detector.health_check()
        return {"status": "ok" if ok else "unhealthy", "detector": type(detector).__name__}

    @app.post("/classify")
    async def classify(image: UploadFile = File(...)):
        contents = await image.read()
        nparr = np.frombuffer(contents, np.uint8)
        frame = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

        if frame is None:
            return {"error": "Could not decode image"}, 400

        result: ClassificationResult = await detector.classify(frame)
        return {
            "waste_type": result.waste_type,
            "confidence": result.confidence,
            "brand": result.brand,
            "detector": type(detector).__name__,
        }

    return app


def main():
    parser = argparse.ArgumentParser(description="Mobius AI Classify Server")
    parser.add_argument("--detector", default="mock", choices=["mock", "yolo", "openai", "hybrid"])
    parser.add_argument("--model-path", default=None, help="Path to YOLO .pt model file")
    parser.add_argument("--port", type=int, default=9001)
    parser.add_argument("--host", default="0.0.0.0")
    args = parser.parse_args()

    detector = load_detector(args.detector, args.model_path)
    app = create_app(detector)

    print(f"Starting classify server on {args.host}:{args.port} with {type(detector).__name__}")
    uvicorn.run(app, host=args.host, port=args.port)


if __name__ == "__main__":
    main()
