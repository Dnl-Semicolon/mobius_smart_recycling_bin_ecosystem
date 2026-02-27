#!/usr/bin/env python3
"""
Dataset validation for YOLO training data.

Checks:
- data.yaml exists and references valid paths
- No orphan images (image without label)
- No orphan labels (label without image)
- Train/val/test splits are disjoint (by filename stem)
- No class IDs outside expected range (0-3)
- No bbox coordinates outside 0.0-1.0
- Minimum images per split
- Per-class counts per split

Usage:
    python ai/src/validate_dataset.py [--data-yaml ai/data/data.yaml]

Exit codes:
    0 — all checks pass
    1 — one or more checks failed
"""
from __future__ import annotations

import argparse
import sys
from pathlib import Path

import yaml

IMAGE_EXTENSIONS = {".jpg", ".jpeg", ".png", ".bmp", ".webp"}
EXPECTED_CLASSES = {0, 1, 2, 3}
CLASS_NAMES = {0: "cup", 1: "lid", 2: "straw", 3: "liquid_waste"}
MIN_IMAGES_PER_SPLIT = {"train": 1, "val": 1, "test": 1}


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Validate YOLO dataset")
    parser.add_argument(
        "--data-yaml",
        type=Path,
        default=Path("ai/data/data.yaml"),
        help="Path to data.yaml",
    )
    return parser.parse_args()


def collect_stems(directory: Path, extensions: set[str]) -> set[str]:
    """Collect filename stems from a directory matching given extensions."""
    if not directory.exists():
        return set()
    return {
        f.stem
        for f in directory.iterdir()
        if f.is_file() and f.suffix.lower() in extensions
    }


def validate_labels(label_dir: Path, split_name: str, errors: list[str]) -> dict[int, int]:
    """Validate label files and return per-class image counts."""
    class_counts: dict[int, int] = {c: 0 for c in EXPECTED_CLASSES}
    if not label_dir.exists():
        return class_counts

    for label_file in sorted(label_dir.glob("*.txt")):
        classes_in_file: set[int] = set()
        for line_num, line in enumerate(label_file.read_text().strip().splitlines(), 1):
            parts = line.strip().split()
            if len(parts) != 5:
                errors.append(
                    f"[{split_name}] {label_file.name}:{line_num} — "
                    f"expected 5 fields, got {len(parts)}"
                )
                continue

            try:
                class_id = int(parts[0])
            except ValueError:
                errors.append(
                    f"[{split_name}] {label_file.name}:{line_num} — "
                    f"invalid class ID: {parts[0]}"
                )
                continue

            if class_id not in EXPECTED_CLASSES:
                errors.append(
                    f"[{split_name}] {label_file.name}:{line_num} — "
                    f"class ID {class_id} outside range 0-3"
                )

            try:
                coords = [float(x) for x in parts[1:]]
            except ValueError:
                errors.append(
                    f"[{split_name}] {label_file.name}:{line_num} — "
                    f"non-numeric bbox coordinates"
                )
                continue

            for i, val in enumerate(coords):
                if val < 0.0 or val > 1.0:
                    coord_name = ["cx", "cy", "w", "h"][i]
                    errors.append(
                        f"[{split_name}] {label_file.name}:{line_num} — "
                        f"{coord_name}={val:.4f} outside 0.0-1.0"
                    )

            classes_in_file.add(class_id)

        for c in classes_in_file:
            if c in class_counts:
                class_counts[c] += 1

    return class_counts


def main() -> int:
    args = parse_args()
    data_yaml: Path = args.data_yaml.expanduser().resolve()
    errors: list[str] = []
    warnings: list[str] = []

    # --- Check data.yaml exists ---
    if not data_yaml.exists():
        print(f"FAIL: {data_yaml} does not exist.")
        print("Create data.yaml before running validation.")
        return 1

    with open(data_yaml) as f:
        config = yaml.safe_load(f)

    if config is None:
        print(f"FAIL: {data_yaml} is empty or invalid YAML.")
        return 1

    # --- Resolve base path ---
    base_path = data_yaml.parent
    if "path" in config:
        base_path = Path(config["path"])
        if not base_path.is_absolute():
            base_path = data_yaml.parent / base_path

    # --- Validate class names ---
    names = config.get("names")
    nc = config.get("nc")
    if names is None:
        errors.append("data.yaml missing 'names' key")
    elif isinstance(names, list) and len(names) != len(EXPECTED_CLASSES):
        errors.append(
            f"data.yaml has {len(names)} classes, expected {len(EXPECTED_CLASSES)}"
        )
    if nc is not None and nc != len(EXPECTED_CLASSES):
        errors.append(f"data.yaml nc={nc}, expected {len(EXPECTED_CLASSES)}")

    # --- Validate each split ---
    splits = {}
    all_class_counts: dict[str, dict[int, int]] = {}

    for split in ["train", "val", "test"]:
        split_rel = config.get(split)
        if split_rel is None:
            errors.append(f"data.yaml missing '{split}' key")
            continue

        # Resolve image dir
        img_dir = base_path / split_rel
        if not img_dir.exists():
            errors.append(f"[{split}] image directory does not exist: {img_dir}")
            continue

        # Derive label dir (images/ → labels/)
        label_dir = Path(str(img_dir).replace("/images", "/labels"))
        if not label_dir.exists():
            label_dir = img_dir.parent / "labels"

        # Collect stems
        img_stems = collect_stems(img_dir, IMAGE_EXTENSIONS)
        lbl_stems = collect_stems(label_dir, {".txt"})

        splits[split] = img_stems

        # Check minimum images
        min_count = MIN_IMAGES_PER_SPLIT.get(split, 3)
        if len(img_stems) < min_count:
            errors.append(
                f"[{split}] only {len(img_stems)} images, "
                f"minimum is {min_count}"
            )

        # Orphan checks
        orphan_images = img_stems - lbl_stems
        orphan_labels = lbl_stems - img_stems

        if orphan_images:
            for stem in sorted(orphan_images):
                errors.append(f"[{split}] orphan image (no label): {stem}")

        if orphan_labels:
            for stem in sorted(orphan_labels):
                errors.append(f"[{split}] orphan label (no image): {stem}")

        # Validate label contents
        class_counts = validate_labels(label_dir, split, errors)
        all_class_counts[split] = class_counts

    # --- Disjointness check ---
    split_names = list(splits.keys())
    for i in range(len(split_names)):
        for j in range(i + 1, len(split_names)):
            a, b = split_names[i], split_names[j]
            overlap = splits[a] & splits[b]
            if overlap:
                examples = sorted(overlap)[:5]
                errors.append(
                    f"LEAK: {len(overlap)} images shared between "
                    f"{a} and {b}: {examples}"
                )

    # --- Print report ---
    print("=" * 60)
    print("  Dataset Validation Report")
    print("=" * 60)
    print(f"  data.yaml: {data_yaml}")
    print()

    for split in ["train", "val", "test"]:
        if split in splits:
            print(f"  [{split}] {len(splits[split])} images")
            if split in all_class_counts:
                for cls_id in sorted(all_class_counts[split]):
                    count = all_class_counts[split][cls_id]
                    name = CLASS_NAMES.get(cls_id, str(cls_id))
                    print(f"    class {cls_id} ({name}): {count} images")
        print()

    if warnings:
        print(f"  WARNINGS ({len(warnings)}):")
        for w in warnings:
            print(f"    - {w}")
        print()

    if errors:
        print(f"  ERRORS ({len(errors)}):")
        for e in errors:
            print(f"    - {e}")
        print()
        print("  RESULT: FAIL")
        print("=" * 60)
        return 1

    print("  RESULT: PASS — all checks passed")
    print("=" * 60)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
