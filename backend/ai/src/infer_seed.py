#!/usr/bin/env python3
"""
[4] INFER SEED — Run inference on all seed images.

WHY THIS FILE EXISTS:
This is the main entry point for batch inference. It reads your labeled
dataset (CSV), runs each image through the model, and writes results to
a JSONL file. Other scripts (report.py) read this output.

USAGE:
    python ai/src/infer_seed.py

OUTPUT:
    ai/runs/seed_inference_results.jsonl

PREVIOUS: class_map.py [3] for the ImageNet → waste_type bridge.
NEXT: report.py [5] to see human-readable results.
"""
import csv
import json

from config import SEED_DIR, SEED_LABELS_CSV, SEED_RESULTS_JSONL
from core import infer_image


# =============================================================================
# [4.1] MAIN FUNCTION
# =============================================================================

def main() -> int:
    """
    Run inference on all images listed in the seed CSV.

    HOW IT WORKS:
    1. Read labels_seed.csv to get (filename, expected_waste_type) pairs
    2. For each image, run inference via core.infer_image()
    3. Write results to JSONL (one JSON object per line)

    WHY JSONL:
    JSON Lines format is easy to append to and stream-process.
    Each line is independent, so you can read line-by-line without
    loading the entire file into memory.
    """

    # [4.1.1] Validate input file exists
    if not SEED_LABELS_CSV.exists():
        print(f"Error: Labels file not found: {SEED_LABELS_CSV}")
        print("Make sure you have images in ai/data/cups_raw/seed/")
        return 1

    # [4.1.2] Ensure output directory exists
    SEED_RESULTS_JSONL.parent.mkdir(parents=True, exist_ok=True)

    # [4.1.3] Process each image
    results_written = 0

    with (
        SEED_LABELS_CSV.open("r", encoding="utf-8", newline="") as csv_file,
        SEED_RESULTS_JSONL.open("w", encoding="utf-8") as output_file,
    ):
        reader = csv.DictReader(csv_file)

        for row in reader:
            filename = row["filename"].strip()
            expected_waste_type = row["waste_type"].strip()
            image_path = SEED_DIR / filename

            # [4.1.4] Skip missing images with a warning
            if not image_path.exists():
                print(f"Warning: Image not found, skipping: {image_path}")
                continue

            # [4.1.5] Run inference
            result = infer_image(image_path)

            # [4.1.6] Build output record
            record = {
                "filename": filename,
                "expected_waste_type": expected_waste_type,
                "predicted_waste_type": result.predicted_label,
                "confidence": result.confidence,
            }

            # [4.1.7] Write as JSON line
            output_file.write(json.dumps(record) + "\n")
            results_written += 1

    # [4.1.8] Summary
    print(f"Inference complete: {results_written} images processed")
    print(f"Results written to: {SEED_RESULTS_JSONL}")
    print()
    print("Next step: python ai/src/report.py")

    return 0


if __name__ == "__main__":
    raise SystemExit(main())


# =============================================================================
# [4.2] READING ORDER
# =============================================================================
#
# You're reading:  [4] infer_seed.py
# Previous:        [3] class_map.py — ImageNet → waste_type mappings
# Next read:       [5] report.py    — human-readable output
#
# =============================================================================
