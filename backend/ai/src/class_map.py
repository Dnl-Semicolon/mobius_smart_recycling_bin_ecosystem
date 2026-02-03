#!/usr/bin/env python3
"""
[3] CLASS MAP — The bridge between ImageNet and your waste types.

WHY THIS FILE EXISTS:
MobileNetV2 was trained on ImageNet, which has 1000 classes like "coffee mug",
"beer glass", "bucket". But YOUR system uses waste types like "cup", "lid",
"straw". This file maps ImageNet labels to your labels.

Without this mapping, accuracy is 0% because the model never predicts "cup".
With this mapping, "coffee mug" → "cup" gives you ~60% soft accuracy.

PREVIOUS: core.py [2] for how inference works.
NEXT: infer_seed.py [4] to see batch inference in action.
"""

# =============================================================================
# [3.1] THE MAPPING TABLE
# Left side: What ImageNet/MobileNetV2 predicts
# Right side: What YOUR system calls it
#
# HOW TO EXTEND:
# Run report.py, look at misses, decide if the prediction is "close enough".
# Example: If model predicts "thermos" for a cup, add "thermos": "cup".
# =============================================================================

IMAGENET_TO_WASTE_TYPE: dict[str, str] = {

    # -------------------------------------------------------------------------
    # [3.1.1] CUP-LIKE OBJECTS
    # These ImageNet classes are visually similar to disposable cups.
    # -------------------------------------------------------------------------
    "cup": "cup",
    "coffee mug": "cup",
    "mug": "cup",
    "beer glass": "cup",
    "wine glass": "cup",
    "goblet": "cup",
    "measuring cup": "cup",
    "drinking glass": "cup",
    "tumbler": "cup",
    "pitcher": "cup",
    "water jug": "cup",
    "cocktail shaker": "cup",

    # -------------------------------------------------------------------------
    # [3.1.2] BOTTLE-LIKE OBJECTS
    # Bottles are cylindrical like cups. In a recycling context, they're often
    # processed similarly, so we map them to "cup" for now.
    # -------------------------------------------------------------------------
    "water bottle": "cup",
    "pop bottle": "cup",
    "beer bottle": "cup",
    "wine bottle": "cup",

    # -------------------------------------------------------------------------
    # [3.1.3] CONTAINER-LIKE OBJECTS
    # Loose matches — these are round/cylindrical but less cup-like.
    # You might want to remove these if they cause false positives.
    # -------------------------------------------------------------------------
    "bucket": "cup",
    "vase": "cup",
    "flowerpot": "cup",

    # -------------------------------------------------------------------------
    # [3.1.4] LID-LIKE OBJECTS
    # Flat, circular objects that resemble cup lids.
    # -------------------------------------------------------------------------
    "plate": "lid",
    "disk": "lid",
    "frisbee": "lid",

    # -------------------------------------------------------------------------
    # [3.1.5] STRAW-LIKE OBJECTS
    # Long, thin objects.
    # -------------------------------------------------------------------------
    "stick": "straw",
    "pencil": "straw",
    "pen": "straw",

    # -------------------------------------------------------------------------
    # [3.1.6] NAPKIN-LIKE OBJECTS
    # Soft, flat, fabric-like items.
    # -------------------------------------------------------------------------
    "tissue": "napkin",
    "paper towel": "napkin",
    "handkerchief": "napkin",
    "bib": "napkin",

    # -------------------------------------------------------------------------
    # [3.1.7] LIQUID-LIKE OBJECTS
    # For detecting spills or liquid waste.
    # -------------------------------------------------------------------------
    "water": "liquid_waste",
    "coffee": "liquid_waste",
}


# =============================================================================
# [3.2] LOOKUP FUNCTION
# =============================================================================

def map_to_waste_type(imagenet_label: str) -> str | None:
    """
    Convert an ImageNet label to a waste type.

    Args:
        imagenet_label: What MobileNetV2 predicted (e.g., "coffee mug")

    Returns:
        The waste type (e.g., "cup") or None if no mapping exists.

    WHY LOWERCASE:
    ImageNet labels are inconsistent in casing. We lowercase both
    the input and the keys to handle "Coffee Mug" vs "coffee mug".
    """
    return IMAGENET_TO_WASTE_TYPE.get(imagenet_label.lower())


# =============================================================================
# [3.3] CLI — Run this file directly to see all mappings
# =============================================================================

def main() -> int:
    """Print all mappings grouped by waste type."""
    print("ImageNet → Waste Type Mappings")
    print("=" * 50)

    # Group by waste type for readability
    by_waste_type: dict[str, list[str]] = {}
    for imagenet_label, waste_type in IMAGENET_TO_WASTE_TYPE.items():
        by_waste_type.setdefault(waste_type, []).append(imagenet_label)

    for waste_type, labels in sorted(by_waste_type.items()):
        print(f"\n{waste_type}:")
        for label in sorted(labels):
            print(f"  - {label}")

    print(f"\nTotal mappings: {len(IMAGENET_TO_WASTE_TYPE)}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())


# =============================================================================
# [3.4] READING ORDER
# =============================================================================
#
# You're reading:  [3] class_map.py
# Previous:        [2] core.py       — inference and matching logic
# Next read:       [4] infer_seed.py — batch inference entry point
#
# =============================================================================
