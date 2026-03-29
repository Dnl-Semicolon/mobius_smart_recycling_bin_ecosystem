"""Hybrid detector — YOLO for waste type, OpenAI Vision for brand identification.

Two-stage pipeline:
  1. YOLO (local, free) detects waste components + brand_logo regions
  2. OpenAI Vision (cloud, paid) identifies which brand — only called on cropped logo region

This gives edge-AI speed for classification with cloud-AI accuracy for brand recognition.
Adding a new brand requires zero retraining — just update the prompt.
"""

from __future__ import annotations

import base64
import json
import os
from pathlib import Path

import cv2
import numpy as np

from bin_os.vision.base_detector import BaseDetector, ClassificationResult
from bin_os.vision.yolo_detector import CLASS_TO_WASTE_TYPE, PRIORITY, BRAND_CLASSES

BRAND_PROMPT = """Identify the brand on this cup from the logo or text visible in this cropped image.

Respond with ONLY a JSON object, no markdown:
{"brand": "starbucks"}

Options: starbucks, zus-coffee, mixue, unknown
If the brand is not clearly one of these, use "unknown"."""

# Padding factor for cropping around brand_logo bbox
CROP_PAD = 0.15


class HybridDetector(BaseDetector):
    def __init__(
        self,
        model_path: str = "models/mobius-cup-v1.pt",
        openai_model: str = "gpt-4o-mini",
        api_key: str | None = None,
    ):
        self.model_path = Path(model_path)
        self.openai_model = openai_model
        self.api_key = api_key or os.environ.get("OPENAI_API_KEY", "")
        self._model = None
        self._client = None

    def _get_model(self):
        if self._model is None:
            from ultralytics import YOLO
            print(f"[hybrid] Loading YOLO model from {self.model_path}")
            self._model = YOLO(str(self.model_path))
            print(f"[hybrid] Model loaded — classes: {self._model.names}")
        return self._model

    def _get_openai_client(self):
        if self._client is None:
            from openai import AsyncOpenAI
            self._client = AsyncOpenAI(api_key=self.api_key)
        return self._client

    async def classify(self, image: np.ndarray) -> ClassificationResult:
        model = self._get_model()

        # --- Stage 1: YOLO inference ---
        results = model.predict(image, verbose=False, conf=0.25)
        result = results[0]

        detections = []
        brand_logo_bbox = None

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
            # Capture the highest-confidence brand_logo bbox for cropping
            if cls_name in BRAND_CLASSES and (brand_logo_bbox is None or conf > brand_logo_bbox[1]):
                brand_logo_bbox = (bbox, conf)

        # Determine waste type by priority
        waste_type = "unknown"
        confidence = 0
        for priority_class in PRIORITY:
            for det in detections:
                if det["class"] == priority_class and det["confidence"] > confidence:
                    waste_type = CLASS_TO_WASTE_TYPE[priority_class]
                    confidence = det["confidence"]

        # If only brand_logo detected but no component, treat as plastic_cup
        if waste_type == "unknown" and brand_logo_bbox is not None:
            waste_type = "plastic_cup"
            confidence = round(brand_logo_bbox[1] * 100)

        yolo_raw = json.dumps({"detections": detections}, indent=2)

        # --- Stage 2: OpenAI brand identification (only if brand_logo detected) ---
        brand = ""
        brand_raw = ""

        if brand_logo_bbox is not None and self.api_key:
            bbox_coords = brand_logo_bbox[0]
            crop = self._crop_with_padding(image, bbox_coords)
            if crop is not None and crop.size > 0:
                print(f"[hybrid] brand_logo detected — calling OpenAI for brand ID "
                      f"(crop {crop.shape[1]}x{crop.shape[0]}px)")
                brand, brand_raw = await self._identify_brand(crop)
                if brand and brand != "unknown":
                    print(f"[hybrid] Brand identified: {brand}")
                else:
                    print("[hybrid] Brand not identified by OpenAI")
                    brand = ""
        elif brand_logo_bbox is not None and not self.api_key:
            print("[hybrid] brand_logo detected but OPENAI_API_KEY not set — skipping brand ID")

        if brand_logo_bbox is None:
            print(f"[hybrid] YOLO only → {waste_type} @ {confidence}%")

        # Combine raw responses
        raw = yolo_raw
        if brand_raw:
            raw = json.dumps({
                "yolo": json.loads(yolo_raw),
                "openai_brand": brand_raw,
            }, indent=2)

        return ClassificationResult(
            waste_type=waste_type,
            confidence=confidence,
            brand=brand,
            raw_response=raw,
        )

    def _crop_with_padding(self, image: np.ndarray, bbox: list[float]) -> np.ndarray | None:
        """Crop the brand_logo region with padding, clamped to image bounds."""
        h, w = image.shape[:2]
        x1, y1, x2, y2 = bbox

        # Add padding
        bw = x2 - x1
        bh = y2 - y1
        pad_x = bw * CROP_PAD
        pad_y = bh * CROP_PAD

        cx1 = max(0, int(x1 - pad_x))
        cy1 = max(0, int(y1 - pad_y))
        cx2 = min(w, int(x2 + pad_x))
        cy2 = min(h, int(y2 + pad_y))

        if cx2 <= cx1 or cy2 <= cy1:
            return None

        return image[cy1:cy2, cx1:cx2]

    async def _identify_brand(self, crop: np.ndarray) -> tuple[str, str]:
        """Send cropped brand region to OpenAI Vision. Returns (brand_slug, raw_response)."""
        try:
            _, buffer = cv2.imencode(".jpg", crop, [cv2.IMWRITE_JPEG_QUALITY, 85])
            b64_image = base64.b64encode(buffer).decode("utf-8")

            client = self._get_openai_client()
            response = await client.chat.completions.create(
                model=self.openai_model,
                messages=[
                    {
                        "role": "user",
                        "content": [
                            {"type": "text", "text": BRAND_PROMPT},
                            {
                                "type": "image_url",
                                "image_url": {
                                    "url": f"data:image/jpeg;base64,{b64_image}",
                                    "detail": "low",
                                },
                            },
                        ],
                    },
                ],
                max_tokens=50,
                temperature=0,
            )

            raw = response.choices[0].message.content.strip()
            parsed = json.loads(raw)
            brand = str(parsed.get("brand", "")).strip().lower()

            # Validate against known brands
            valid_brands = {"starbucks", "zus-coffee", "mixue"}
            if brand not in valid_brands:
                brand = ""

            return brand, raw

        except Exception as e:
            print(f"[hybrid] OpenAI brand identification failed: {e}")
            return "", ""

    async def health_check(self) -> bool:
        return self.model_path.exists()
