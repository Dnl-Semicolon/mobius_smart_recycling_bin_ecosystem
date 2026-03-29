"""Abstract detector interface. All detectors implement this."""

from __future__ import annotations

from abc import ABC, abstractmethod
from dataclasses import dataclass

import numpy as np


@dataclass
class ClassificationResult:
    waste_type: str       # paper_cup, plastic_cup, lid, straw, napkin, liquid_waste, unknown
    confidence: int       # 0-100
    brand: str = ""       # detected cup brand slug (e.g. "starbucks", "mixue"), empty if unknown
    raw_response: str = ""  # debug: raw model output


class BaseDetector(ABC):
    @abstractmethod
    async def classify(self, image: np.ndarray) -> ClassificationResult:
        """Given a camera frame (BGR numpy array), return waste classification."""
        ...

    @abstractmethod
    async def health_check(self) -> bool:
        """Return True if the detector is ready to classify."""
        ...
