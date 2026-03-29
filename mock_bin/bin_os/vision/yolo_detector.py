"""YOLO detector — classifies waste using a locally trained YOLOv11 model."""

from __future__ import annotations

import json
from pathlib import Path

import numpy as np

from bin_os.vision.base_detector import BaseDetector, ClassificationResult


# Map Roboflow-trained classes → backend waste_type enum
CLASS_TO_WASTE_TYPE = {
    "cup_body": "plastic_cup",   # trained on transparent plastic cups
    "lid": "lid",
    "straw": "straw",
}

# Priority order: if multiple components detected, pick the primary one
PRIORITY = ["cup_body", "lid", "straw"]

# Classes that indicate a brand is visible (not a waste_type themselves)
BRAND_CLASSES = {"brand_logo"}

# For now, single-brand model — all brand_logo detections = starbucks.
# When multi-brand is added, this becomes a classifier on the cropped region.
DEFAULT_BRAND = "starbucks"


class YOLODetector(BaseDetector):
    def __init__(self, model_path: str = "models/mobius-cup-v1.pt"):
        self.model_path = Path(model_path)
        self._model = None

    def _get_model(self):
        if self._model is None:
            from ultralytics import YOLO
            print(f"[yolo] Loading model from {self.model_path}")
            self._model = YOLO(str(self.model_path))
            print(f"[yolo] Model loaded — classes: {self._model.names}")
        return self._model

    async def classify(self, image: np.ndarray) -> ClassificationResult:
        model = self._get_model()

        # Single-frame inference (snapshot, not video stream)
        results = model.predict(image, verbose=False, conf=0.25)
        result = results[0]

        # Parse all detections
        detections = []
        for box in result.boxes:
            cls_id = int(box.cls[0])
            cls_name = model.names[cls_id]
            conf = float(box.conf[0])
            bbox = box.xyxy[0].tolist()
            detections.append({
                "class": cls_name,
                "confidence": round(conf * 100),
                "bbox": [round(v, 1) for v in bbox],
            })

        # Find brand from brand_logo detections
        brand = ""
        brand_conf = 0
        for det in detections:
            if det["class"] in BRAND_CLASSES:
                brand = DEFAULT_BRAND
                brand_conf = det["confidence"]

        # Find primary waste item by priority
        waste_type = "unknown"
        confidence = 0
        for priority_class in PRIORITY:
            for det in detections:
                if det["class"] == priority_class and det["confidence"] > confidence:
                    waste_type = CLASS_TO_WASTE_TYPE[priority_class]
                    confidence = det["confidence"]

        # If only brand_logo was detected but no component, still flag as plastic_cup
        if waste_type == "unknown" and brand:
            waste_type = "plastic_cup"
            confidence = brand_conf

        raw = json.dumps({"detections": detections}, indent=2)

        return ClassificationResult(
            waste_type=waste_type,
            confidence=confidence,
            brand=brand,
            raw_response=raw,
        )

    async def health_check(self) -> bool:
        return self.model_path.exists()
