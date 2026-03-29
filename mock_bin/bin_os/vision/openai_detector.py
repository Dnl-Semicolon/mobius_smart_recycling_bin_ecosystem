"""OpenAI Vision API detector — classifies waste using GPT-4o vision."""

from __future__ import annotations

import base64
import os

import cv2
import numpy as np

from bin_os.vision.base_detector import BaseDetector, ClassificationResult

SYSTEM_PROMPT = """You are a waste classification system for a smart recycling bin.
The camera is inside the bin looking up at the item being deposited. The image may show a person's hand holding the item — focus on the item, ignore the person.

Classify the item into exactly ONE of these types:
- paper_cup (disposable paper coffee/tea cup, paper beverage container)
- plastic_cup (any clear plastic beverage container: plastic cup, plastic bottle, water bottle, iced drink cup, PET bottle)
- lid (plastic lid or cap from a cup or bottle)
- straw (plastic or paper straw)
- napkin (paper napkin or tissue)
- liquid_waste (visible liquid being poured, spilled drink)
- unknown (not a recyclable beverage item, or image is too unclear)

If the item is a cup (paper_cup or plastic_cup), also identify the brand from visible logos, text, or design patterns. Use the brand's lowercase slug (e.g. "starbucks", "mixue", "tealive", "zus-coffee", "chagee", "mcdonalds"). If the brand is not recognizable, use "".

Respond with ONLY a JSON object, no markdown, no explanation:
{"waste_type": "plastic_cup", "confidence": 85, "brand": "starbucks"}

confidence is 0-100. Be generous — if it looks like a beverage container, classify it.
For non-cup items, omit brand or set it to "".
If the image is truly unclear or shows no waste item at all, return {"waste_type": "unknown", "confidence": 0, "brand": ""}"""


class OpenAIDetector(BaseDetector):
    def __init__(self, model: str = "gpt-4o-mini", api_key: str | None = None):
        self.model = model
        self.api_key = api_key or os.environ.get("OPENAI_API_KEY", "")
        self._client = None

    def _get_client(self):
        if self._client is None:
            from openai import AsyncOpenAI
            self._client = AsyncOpenAI(api_key=self.api_key)
        return self._client

    async def classify(self, image: np.ndarray) -> ClassificationResult:
        # Encode frame as JPEG base64
        _, buffer = cv2.imencode(".jpg", image, [cv2.IMWRITE_JPEG_QUALITY, 80])
        b64_image = base64.b64encode(buffer).decode("utf-8")

        client = self._get_client()
        response = await client.chat.completions.create(
            model=self.model,
            messages=[
                {"role": "system", "content": SYSTEM_PROMPT},
                {
                    "role": "user",
                    "content": [
                        {"type": "text", "text": "Classify this item:"},
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

        try:
            import json
            parsed = json.loads(raw)
            waste_type = parsed.get("waste_type", "unknown")
            confidence = int(parsed.get("confidence", 0))
            brand = str(parsed.get("brand", "")).strip()

            valid_types = {"paper_cup", "plastic_cup", "lid", "straw", "napkin", "liquid_waste", "unknown"}
            if waste_type not in valid_types:
                waste_type = "unknown"
            confidence = max(0, min(100, confidence))

            # Only cups can have brands
            if waste_type not in ("paper_cup", "plastic_cup"):
                brand = ""
        except (json.JSONDecodeError, ValueError, TypeError):
            waste_type = "unknown"
            confidence = 0
            brand = ""

        return ClassificationResult(
            waste_type=waste_type,
            confidence=confidence,
            brand=brand,
            raw_response=raw,
        )

    async def health_check(self) -> bool:
        return bool(self.api_key)
