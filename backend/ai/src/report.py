#!/usr/bin/env python3
"""
[5] REPORT — Human-readable inference results.

WHY THIS FILE EXISTS:
The JSONL output from infer_seed.py is machine-readable but hard to scan.
This script loads the results and prints a formatted report showing:
- Overall accuracy (exact and soft)
- Confidence statistics
- Per-image breakdown with match status

USAGE:
    python ai/src/report.py

PREVIOUS: infer_seed.py [4] which generates the input data.
NEXT: dataset.py [6] to manage your image collection.
"""
import json
from collections import Counter

from config import SEED_RESULTS_JSONL
from core import match_prediction


# =============================================================================
# [5.1] LOAD RESULTS
# =============================================================================

def load_results() -> list[dict]:
    """
    Load inference results from the JSONL file.

    Returns:
        List of record dicts, each with:
        - filename
        - expected_waste_type
        - predicted_waste_type
        - confidence

    WHY NOT JUST READ IN MAIN:
    Separating loading from display makes testing easier and keeps main() clean.
    """
    if not SEED_RESULTS_JSONL.exists():
        return []

    records = []
    with SEED_RESULTS_JSONL.open("r", encoding="utf-8") as f:
        for line in f:
            line = line.strip()
            if line:
                records.append(json.loads(line))
    return records


# =============================================================================
# [5.2] COMPUTE STATISTICS
# =============================================================================

def compute_stats(records: list[dict]) -> dict:
    """
    Calculate accuracy and confidence statistics.

    Returns dict with:
        total: int
        exact_matches: int
        soft_matches: int
        confidences: list[float]
        predictions: Counter
        expected: Counter
    """
    stats = {
        "total": len(records),
        "exact_matches": 0,
        "soft_matches": 0,
        "confidences": [],
        "predictions": Counter(),
        "expected": Counter(),
    }

    for r in records:
        predicted = r["predicted_waste_type"]
        expected = r["expected_waste_type"]

        stats["confidences"].append(r["confidence"])
        stats["predictions"][predicted] += 1
        stats["expected"][expected] += 1

        match = match_prediction(predicted, expected)
        if match.is_exact:
            stats["exact_matches"] += 1
        if match.is_soft:
            stats["soft_matches"] += 1

    return stats


# =============================================================================
# [5.3] PRINT REPORT
# =============================================================================

def print_report(records: list[dict], stats: dict) -> None:
    """
    Print a formatted report to stdout.

    WHY STDOUT:
    Printing to stdout lets you pipe output to files or other tools.
    Example: python ai/src/report.py > report.txt
    """
    total = stats["total"]
    exact = stats["exact_matches"]
    soft = stats["soft_matches"]
    confidences = stats["confidences"]

    # [5.3.1] Header
    print("=" * 60)
    print("INFERENCE REPORT")
    print("=" * 60)
    print()

    # [5.3.2] Accuracy summary
    print(f"Total images:      {total}")
    print(f"Exact matches:     {exact} ({exact/total*100:.1f}%)")
    print(f"Soft matches:      {soft} ({soft/total*100:.1f}%)  ← with class mapping")
    print()

    # [5.3.3] Confidence stats
    print(f"Confidence range:  {min(confidences):.1%} – {max(confidences):.1%}")
    print(f"Avg confidence:    {sum(confidences)/len(confidences):.1%}")
    print()

    # [5.3.4] Label distribution
    print("Expected labels (ground truth):")
    for label, count in stats["expected"].most_common():
        print(f"  {label}: {count}")
    print()

    print("Top predicted labels:")
    from class_map import map_to_waste_type
    for label, count in stats["predictions"].most_common(5):
        mapped = map_to_waste_type(label)
        arrow = f" → {mapped}" if mapped else " (no mapping)"
        print(f"  {label}: {count}{arrow}")
    print()

    # [5.3.5] Per-image breakdown
    print("-" * 60)
    print("Per-image breakdown")
    print("-" * 60)

    for r in records:
        predicted = r["predicted_waste_type"]
        expected = r["expected_waste_type"]
        confidence = r["confidence"]

        match = match_prediction(predicted, expected)

        # [5.3.6] Status indicator
        if match.is_exact:
            status = "✓ exact"
        elif match.is_soft:
            status = "~ soft "
        else:
            status = "✗ miss "

        print(f"{status}  {r['filename']}")
        print(f"         expected:  {expected}")
        print(f"         predicted: {predicted} ({confidence:.1%})")

        if match.mapped_label and match.mapped_label != predicted:
            print(f"         mapped to: {match.mapped_label}")

    print()


# =============================================================================
# [5.4] MAIN
# =============================================================================

def main() -> int:
    """Entry point."""
    records = load_results()

    if not records:
        print(f"Error: No results found at {SEED_RESULTS_JSONL}")
        print("Run inference first: python ai/src/infer_seed.py")
        return 1

    stats = compute_stats(records)
    print_report(records, stats)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())


# =============================================================================
# [5.5] READING ORDER
# =============================================================================
#
# You're reading:  [5] report.py
# Previous:        [4] infer_seed.py — batch inference
# Next read:       [6] dataset.py   — adding images, checking integrity
#
# =============================================================================
