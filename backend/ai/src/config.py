#!/usr/bin/env python3
"""
[1] CONFIG — Start here. This file defines all paths and constants.

WHY THIS FILE EXISTS:
Instead of hardcoding paths like "ai/data/cups_raw/seed/" in every script,
we define them once here. If you move folders or rename files, you only
change this file.

NEXT: Read core.py [2] to understand how inference works.
"""
from pathlib import Path

# =============================================================================
# [1.1] BASE PATHS
# All paths are relative to the backend/ directory (where you run scripts from)
# =============================================================================

AI_ROOT = Path("ai")
"""
The root of all AI-related files.
Everything lives under this: data, models, outputs, source code.
"""

DATA_DIR = AI_ROOT / "data"
"""
Raw and processed datasets live here.
Subfolders: cups_raw/ (unlabeled photos), cups_labeled/ (future COCO annotations).
"""

RUNS_DIR = AI_ROOT / "runs"
"""
Inference outputs go here.
Each run produces a JSONL file with predictions vs ground truth.
"""

# =============================================================================
# [1.2] SEED DATASET PATHS
# The "seed" dataset is your first 10-30 images for testing the pipeline.
# =============================================================================

SEED_DIR = DATA_DIR / "cups_raw" / "seed"
"""
Where seed images (cup_01.jpg, cup_02.jpg, ...) are stored.
These files are gitignored — only the CSV is committed.
"""

SEED_LABELS_CSV = SEED_DIR / "labels_seed.csv"
"""
Maps filename → waste_type. Example row: cup_01.jpg,cup
This is the ground truth for evaluation.
"""

SEED_RESULTS_JSONL = RUNS_DIR / "seed_inference_results.jsonl"
"""
Output of infer_seed.py. Each line is a JSON object:
{"filename": "cup_01.jpg", "predicted_waste_type": "coffee mug",
 "confidence": 0.12, "expected_waste_type": "cup"}
"""

# =============================================================================
# [1.3] VALID FILE TYPES
# =============================================================================

VALID_IMAGE_EXTENSIONS = {".jpg", ".jpeg", ".png", ".gif", ".webp"}
"""
Image formats we accept. MobileNetV2 can handle any of these via PIL.
"""

# =============================================================================
# [1.4] READING ORDER GUIDE
# =============================================================================
#
# You're reading:  [1] config.py
# Next read:       [2] core.py      — model loading, inference, matching logic
# Then:            [3] class_map.py — ImageNet → waste_type mappings
# Then:            [4] infer_seed.py — batch inference entry point
# Then:            [5] report.py    — human-readable output
# Finally:         [6] dataset.py   — adding images, checking integrity
#
# =============================================================================
