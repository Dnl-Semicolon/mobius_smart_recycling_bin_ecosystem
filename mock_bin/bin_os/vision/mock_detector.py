"""Mock detector — returns random classifications for testing without API keys."""

from __future__ import annotations

import random

import numpy as np

from bin_os.vision.base_detector import BaseDetector, ClassificationResult

WASTE_TYPES = ["paper_cup", "plastic_cup", "lid", "straw", "napkin", "liquid_waste"]
WEIGHTS = [0.30, 0.25, 0.15, 0.15, 0.10, 0.05]
MOCK_BRANDS = ["starbucks", "mixue", "tealive", "zus-coffee", "chagee", ""]


class MockDetector(BaseDetector):
    async def classify(self, image: np.ndarray) -> ClassificationResult:
        waste_type = random.choices(WASTE_TYPES, weights=WEIGHTS, k=1)[0]
        confidence = random.randint(70, 98)
        brand = random.choice(MOCK_BRANDS) if waste_type in ("paper_cup", "plastic_cup") else ""
        return ClassificationResult(
            waste_type=waste_type,
            confidence=confidence,
            brand=brand,
            raw_response=f"mock: {waste_type} @ {confidence}%" + (f" [{brand}]" if brand else ""),
        )

    async def health_check(self) -> bool:
        return True
