#!/usr/bin/env python3
"""
[2] CORE — The brain. Model loading, inference, and result matching.

WHY THIS FILE EXISTS:
Multiple scripts need to load the model and run inference. Instead of
copying the same code everywhere, we centralize it here. This also makes
it easy to swap MobileNetV2 for a fine-tuned model later — change one file.

PREVIOUS: config.py [1] for paths.
NEXT: class_map.py [3] for ImageNet → waste_type mappings.
"""
from pathlib import Path
from typing import NamedTuple

from PIL import Image
import torch
from torchvision import models

from class_map import map_to_waste_type


# =============================================================================
# [2.1] DATA STRUCTURES
# We use NamedTuple instead of dict so you get autocomplete and type checking.
# =============================================================================

class InferenceResult(NamedTuple):
    """
    The output of running one image through the model.

    Fields:
        predicted_label: What ImageNet thinks (e.g., "coffee mug")
        confidence: How sure the model is (0.0 to 1.0)
    """
    predicted_label: str
    confidence: float


class MatchResult(NamedTuple):
    """
    Compares a prediction against ground truth.

    Fields:
        is_exact: True if predicted_label == expected_label
        is_soft: True if predicted_label maps to expected_label via class_map
        mapped_label: The waste_type the prediction maps to (or None)
    """
    is_exact: bool
    is_soft: bool
    mapped_label: str | None


# =============================================================================
# [2.2] MODEL LOADING
# We use MobileNetV2 because it's fast and works on CPU.
# The model was trained on ImageNet (1000 classes like "coffee mug", "cat").
# It does NOT know "cup" as a waste type — that's why we need class_map.py.
# =============================================================================

_model_cache: tuple | None = None


def load_model() -> tuple:
    """
    Load MobileNetV2 with pretrained ImageNet weights.

    Returns:
        (model, weights) tuple. The weights contain the preprocessing
        transforms and the list of ImageNet category names.

    WHY CACHING:
    Loading the model takes ~2 seconds. If you're processing 500 images,
    you don't want to reload it 500 times. We cache it globally.
    """
    global _model_cache

    if _model_cache is not None:
        return _model_cache

    # [2.2.1] Load pretrained weights
    # DEFAULT = the best available weights for this architecture
    weights = models.MobileNet_V2_Weights.DEFAULT

    # [2.2.2] Build the model with those weights
    model = models.mobilenet_v2(weights=weights)

    # [2.2.3] Set to evaluation mode
    # This disables dropout and batch normalization updates.
    # Critical for consistent inference results.
    model.eval()

    _model_cache = (model, weights)
    return _model_cache


# =============================================================================
# [2.3] INFERENCE
# Take an image path, return what the model thinks it is.
# =============================================================================

def infer_image(image_path: Path) -> InferenceResult:
    """
    Run MobileNetV2 inference on a single image.

    Args:
        image_path: Path to an image file (jpg, png, etc.)

    Returns:
        InferenceResult with predicted_label and confidence.

    HOW IT WORKS:
    1. Load and preprocess the image (resize, normalize)
    2. Pass through the neural network
    3. Convert output logits to probabilities
    4. Return the highest-probability class
    """
    model, weights = load_model()

    # [2.3.1] Load image and convert to RGB
    # Some images are grayscale or RGBA; the model expects RGB.
    image = Image.open(image_path).convert("RGB")

    # [2.3.2] Apply preprocessing transforms
    # The weights know what transforms were used during training.
    # This includes resizing to 224x224 and normalizing pixel values.
    tensor = weights.transforms()(image)

    # [2.3.3] Add batch dimension
    # Model expects shape [batch, channels, height, width].
    # unsqueeze(0) turns [3, 224, 224] into [1, 3, 224, 224].
    tensor = tensor.unsqueeze(0)

    # [2.3.4] Run inference without gradient tracking
    # torch.no_grad() saves memory and speeds up inference.
    with torch.no_grad():
        logits = model(tensor)
        probabilities = torch.nn.functional.softmax(logits, dim=1)

    # [2.3.5] Get the top prediction
    confidence, class_index = probabilities.max(dim=1)

    # [2.3.6] Convert class index to human-readable label
    categories = weights.meta.get("categories", [])
    if categories:
        label = categories[class_index.item()]
    else:
        label = f"class_{class_index.item()}"

    return InferenceResult(
        predicted_label=label,
        confidence=float(confidence.item()),
    )


# =============================================================================
# [2.4] MATCHING
# Compare predictions to ground truth using exact and soft matching.
# =============================================================================

def match_prediction(predicted: str, expected: str) -> MatchResult:
    """
    Check if a prediction matches the expected label.

    Args:
        predicted: What the model said (e.g., "coffee mug")
        expected: Ground truth label (e.g., "cup")

    Returns:
        MatchResult with is_exact, is_soft, and mapped_label.

    TWO TYPES OF MATCHING:
    - Exact: "cup" == "cup" (unlikely with ImageNet model)
    - Soft: "coffee mug" maps to "cup" via class_map.py

    Soft matching lets us measure progress without training a custom model.
    See class_map.py [3] for the mapping table.
    """
    # [2.4.1] Check exact match
    if predicted == expected:
        return MatchResult(is_exact=True, is_soft=True, mapped_label=predicted)

    # [2.4.2] Check soft match via class_map
    mapped = map_to_waste_type(predicted)
    if mapped is not None and mapped == expected:
        return MatchResult(is_exact=False, is_soft=True, mapped_label=mapped)

    # [2.4.3] No match
    return MatchResult(is_exact=False, is_soft=False, mapped_label=mapped)


# =============================================================================
# [2.5] READING ORDER
# =============================================================================
#
# You're reading:  [2] core.py
# Previous:        [1] config.py    — paths and constants
# Next read:       [3] class_map.py — how "coffee mug" becomes "cup"
#
# =============================================================================
