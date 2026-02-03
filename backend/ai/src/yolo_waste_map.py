#!/usr/bin/env python3
"""
Minimal COCO/YOLO class → waste_type mapping for Phase 3 demo compatibility.

This is a temporary, explicit bridge for demo output only.
It does not imply model training or accuracy.
"""
from __future__ import annotations


_COCO_TO_WASTE_TYPE: dict[str, str] = {
    # Cups
    "cup": "paper_cup",
    "coffee cup": "paper_cup",
    "paper cup": "paper_cup",

    # Bottles (treated as plastic cups in the demo taxonomy)
    "bottle": "plastic_cup",
    "water bottle": "plastic_cup",
    "plastic bottle": "plastic_cup",
    "soda bottle": "plastic_cup",
    "pop bottle": "plastic_cup",

    # Straws
    "straw": "straw",
}


def map_to_waste_type(raw_class: str) -> str | None:
    """
    Map a YOLO/COCO class name to a waste_type enum.

    Args:
        raw_class: Class label predicted by the detector (e.g., "cup")

    Returns:
        A waste_type string (e.g., "paper_cup") or None if unmapped.
    """
    return _COCO_TO_WASTE_TYPE.get(raw_class.strip().lower())
