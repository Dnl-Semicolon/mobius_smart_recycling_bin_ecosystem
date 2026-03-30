"""Hybrid detector — YOLO + OpenAI Vision on EVERY inference.

Two parallel pipelines:
  1. YOLO (local, free) detects waste components (cups, lids, straws)
  2. OpenAI Vision (cloud, paid) classifies waste type AND identifies brand

Both run on every frame. Results are merged:
  - YOLO's waste_type used if confidence > 70%, else OpenAI's
  - Brand always from OpenAI (YOLO can't identify brands)
  - Confidence = max of both

Adding a new brand requires zero retraining — just update the prompt.
"""

from __future__ import annotations

import asyncio
import base64
import json
import os
from pathlib import Path

import cv2
import numpy as np

from bin_os.vision.base_detector import BaseDetector, ClassificationResult
from bin_os.vision.yolo_detector import CLASS_TO_WASTE_TYPE, PRIORITY, BRAND_CLASSES

CLASSIFY_PROMPT = """Classify this image of a waste/recyclable item deposited in a smart recycling bin.

Respond with ONLY a JSON object, no markdown:
{"waste_type": "plastic_cup", "confidence": 85, "brand": "starbucks"}

waste_type options: paper_cup, plastic_cup, lid, straw, napkin, liquid_waste, unknown
- paper_cup = hot cup (brown/white cardboard, paper beverage container)
- plastic_cup = cold cup, iced drink, water bottle, PET bottle, any clear plastic container
- lid = plastic lid or cap from a cup or bottle
- straw = plastic or paper straw
- napkin = tissue paper, napkins, paper towels
- liquid_waste = visible liquid being poured or spilled
- unknown = not a recyclable item or image too unclear

brand options: starbucks, zus-coffee, mixue, chagee, lucky-cup, unknown
- Identify brand from visible logos, text, or design patterns on cups
- Water bottles and generic items = "unknown"
- If brand unclear, use "unknown"

confidence: 0-100 how sure you are about the waste_type. Be generous."""

VALID_BRANDS = {"starbucks", "zus-coffee", "mixue", "chagee", "lucky-cup"}
VALID_TYPES = {"paper_cup", "plastic_cup", "lid", "straw", "napkin", "liquid_waste", "unknown"}


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
        # Run YOLO and OpenAI in parallel
        yolo_task = asyncio.to_thread(self._run_yolo, image)
        openai_task = self._run_openai(image)

        yolo_result, openai_result = await asyncio.gather(yolo_task, openai_task)

        yolo_type, yolo_conf, yolo_raw = yolo_result
        openai_type, openai_conf, openai_brand, openai_raw = openai_result

        # Merge: prefer YOLO waste_type if high confidence, else OpenAI
        if yolo_type != "unknown" and yolo_conf >= 70:
            waste_type = yolo_type
            confidence = yolo_conf
        elif openai_type != "unknown":
            waste_type = openai_type
            confidence = openai_conf
        elif yolo_type != "unknown":
            waste_type = yolo_type
            confidence = yolo_conf
        else:
            waste_type = "unknown"
            confidence = 0

        # Brand always from OpenAI
        brand = openai_brand

        # Use max confidence
        confidence = max(confidence, openai_conf if openai_type != "unknown" else 0)

        print(f"[hybrid] YOLO={yolo_type}@{yolo_conf}% | OpenAI={openai_type}@{openai_conf}%/{openai_brand} → {waste_type}@{confidence}%/{brand}")

        raw = json.dumps({
            "yolo": json.loads(yolo_raw),
            "openai": openai_raw,
            "merged": {"waste_type": waste_type, "confidence": confidence, "brand": brand},
        }, indent=2)

        return ClassificationResult(
            waste_type=waste_type,
            confidence=confidence,
            brand=brand,
            raw_response=raw,
        )

    def _run_yolo(self, image: np.ndarray) -> tuple[str, int, str]:
        """Run YOLO inference synchronously. Returns (waste_type, confidence, raw_json)."""
        model = self._get_model()
        results = model.predict(image, verbose=False, conf=0.25)
        result = results[0]

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

        # Determine waste type by priority
        waste_type = "unknown"
        confidence = 0
        for priority_class in PRIORITY:
            for det in detections:
                if det["class"] == priority_class and det["confidence"] > confidence:
                    waste_type = CLASS_TO_WASTE_TYPE[priority_class]
                    confidence = det["confidence"]

        # If only brand_logo detected but no component, treat as plastic_cup
        if waste_type == "unknown":
            for det in detections:
                if det["class"] in BRAND_CLASSES and det["confidence"] > confidence:
                    waste_type = "plastic_cup"
                    confidence = det["confidence"]

        raw = json.dumps({"detections": detections})
        return waste_type, confidence, raw

    async def _run_openai(self, image: np.ndarray) -> tuple[str, int, str, str]:
        """Run OpenAI Vision classification. Returns (waste_type, confidence, brand, raw_response)."""
        if not self.api_key:
            print("[hybrid] OPENAI_API_KEY not set — skipping OpenAI")
            return "unknown", 0, "", ""

        try:
            _, buffer = cv2.imencode(".jpg", image, [cv2.IMWRITE_JPEG_QUALITY, 80])
            b64_image = base64.b64encode(buffer).decode("utf-8")

            client = self._get_openai_client()
            response = await client.chat.completions.create(
                model=self.openai_model,
                messages=[
                    {
                        "role": "user",
                        "content": [
                            {"type": "text", "text": CLASSIFY_PROMPT},
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
                max_tokens=100,
                temperature=0,
            )

            raw = response.choices[0].message.content.strip()
            parsed = json.loads(raw)

            waste_type = str(parsed.get("waste_type", "unknown")).strip()
            confidence = int(parsed.get("confidence", 0))
            brand = str(parsed.get("brand", "")).strip().lower()

            if waste_type not in VALID_TYPES:
                waste_type = "unknown"
            confidence = max(0, min(100, confidence))

            # Only cups can have brands
            if waste_type not in ("paper_cup", "plastic_cup"):
                brand = ""
            elif brand not in VALID_BRANDS:
                brand = ""

            return waste_type, confidence, brand, raw

        except Exception as e:
            print(f"[hybrid] OpenAI classification failed: {e}")
            return "unknown", 0, "", ""

    async def health_check(self) -> bool:
        return self.model_path.exists()
