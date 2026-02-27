#!/usr/bin/env python3
"""
Normalize a Roboflow YOLOv5/v8 export into the project's expected dataset layout.

Roboflow exports:
    <source>/
    ├── data.yaml
    ├── train/  (images/ + labels/)
    ├── valid/  (images/ + labels/)   ← Roboflow uses "valid", we need "val"
    └── test/   (images/ + labels/)

This script:
    1. Reads Roboflow's data.yaml and verifies 4 classes
    2. Remaps class IDs in label files if Roboflow's class order differs from ours
    3. Copies train/, valid/→val/, test/ into the output directory
    4. Writes ai/data/data.yaml with our standard paths
    5. Creates test/empty/ placeholder
    6. Generates test/MANIFEST.txt (SHA-256 of every test image + label)
    7. Runs validate_dataset.py as the final gate

Usage:
    python ai/src/normalize_dataset.py --source ~/Downloads/roboflow-export/
    python ai/src/normalize_dataset.py --source ~/Downloads/roboflow-export/ --dry-run
"""
from __future__ import annotations

import argparse
import hashlib
import shutil
import subprocess
import sys
from pathlib import Path

import yaml

EXPECTED_NAMES = ["cup", "lid", "straw", "liquid_waste"]
IMAGE_EXTENSIONS = {".jpg", ".jpeg", ".png", ".bmp", ".webp"}


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(
        description="Normalize Roboflow YOLO export to project layout",
    )
    parser.add_argument(
        "--source", type=Path, required=True,
        help="Path to unzipped Roboflow export directory",
    )
    parser.add_argument(
        "--output", type=Path, default=Path("ai/data/cups_labeled/yolo_day1"),
        help="Output directory (default: ai/data/cups_labeled/yolo_day1)",
    )
    parser.add_argument(
        "--data-yaml-out", type=Path, default=Path("ai/data/data.yaml"),
        help="Where to write our data.yaml (default: ai/data/data.yaml)",
    )
    parser.add_argument(
        "--dry-run", action="store_true",
        help="Print plan without copying files",
    )
    return parser.parse_args()


def read_roboflow_yaml(source: Path) -> dict:
    """Read and parse the Roboflow data.yaml."""
    yaml_path = source / "data.yaml"
    if not yaml_path.exists():
        print(f"ERROR: No data.yaml found in {source}", file=sys.stderr)
        sys.exit(1)

    with open(yaml_path) as f:
        config = yaml.safe_load(f)

    if config is None:
        print(f"ERROR: data.yaml is empty or invalid", file=sys.stderr)
        sys.exit(1)

    return config


def build_class_remap(roboflow_names: list[str]) -> dict[int, int] | None:
    """Build a mapping from Roboflow class IDs to our class IDs.

    Returns None if no remapping needed (same order).
    Returns a dict {roboflow_id: our_id} if remapping is needed.
    Exits if classes don't match.
    """
    if not isinstance(roboflow_names, list):
        # Roboflow sometimes uses dict format {0: 'cup', 1: 'lid', ...}
        if isinstance(roboflow_names, dict):
            roboflow_names = [roboflow_names[i] for i in sorted(roboflow_names.keys())]
        else:
            print(f"ERROR: Unexpected names format: {type(roboflow_names)}", file=sys.stderr)
            sys.exit(1)

    # Normalize names for comparison
    rf_normalized = [n.strip().lower().replace(" ", "_").replace("-", "_") for n in roboflow_names]
    our_normalized = [n.strip().lower() for n in EXPECTED_NAMES]

    # Check all our classes exist in the Roboflow export
    rf_set = set(rf_normalized)
    our_set = set(our_normalized)

    if not our_set.issubset(rf_set):
        missing = our_set - rf_set
        print(f"ERROR: Roboflow export missing classes: {missing}", file=sys.stderr)
        print(f"  Roboflow has: {rf_normalized}", file=sys.stderr)
        print(f"  We need: {our_normalized}", file=sys.stderr)
        sys.exit(1)

    if rf_normalized == our_normalized:
        return None  # No remap needed

    # Build remap: roboflow_id → our_id
    remap: dict[int, int] = {}
    for rf_id, rf_name in enumerate(rf_normalized):
        if rf_name in our_normalized:
            our_id = our_normalized.index(rf_name)
            remap[rf_id] = our_id

    extra_rf = [rf_normalized[i] for i in range(len(rf_normalized)) if i not in remap]
    if extra_rf:
        print(f"  Note: Roboflow has extra classes not in our taxonomy: {extra_rf}")
        print(f"  Labels with these classes will be DROPPED from label files.")

    return remap


def remap_label_file(label_path: Path, remap: dict[int, int]) -> str:
    """Remap class IDs in a label file. Returns new content. Drops lines with unmapped classes."""
    lines = label_path.read_text().strip().splitlines()
    new_lines = []
    for line in lines:
        parts = line.strip().split()
        if len(parts) != 5:
            continue
        try:
            old_id = int(parts[0])
        except ValueError:
            continue
        if old_id not in remap:
            continue  # Drop lines for classes not in our taxonomy
        parts[0] = str(remap[old_id])
        new_lines.append(" ".join(parts))
    return "\n".join(new_lines) + "\n" if new_lines else ""


def copy_split(
    source_split: Path,
    dest_split: Path,
    remap: dict[int, int] | None,
    dry_run: bool,
) -> int:
    """Copy a split (images + labels) from source to destination.

    Returns the number of images copied.
    """
    src_images = source_split / "images"
    src_labels = source_split / "labels"

    if not src_images.exists():
        print(f"  WARNING: {src_images} does not exist, skipping", file=sys.stderr)
        return 0

    dst_images = dest_split / "images"
    dst_labels = dest_split / "labels"

    if not dry_run:
        dst_images.mkdir(parents=True, exist_ok=True)
        dst_labels.mkdir(parents=True, exist_ok=True)

    count = 0
    for img_file in sorted(src_images.iterdir()):
        if not img_file.is_file() or img_file.suffix.lower() not in IMAGE_EXTENSIONS:
            continue

        # Copy image
        if not dry_run:
            shutil.copy2(img_file, dst_images / img_file.name)

        # Copy/remap label
        label_file = src_labels / (img_file.stem + ".txt")
        if label_file.exists():
            if remap is not None:
                content = remap_label_file(label_file, remap)
                if not dry_run:
                    (dst_labels / label_file.name).write_text(content)
            else:
                if not dry_run:
                    shutil.copy2(label_file, dst_labels / label_file.name)

        count += 1

    return count


def generate_manifest(test_dir: Path) -> None:
    """Generate MANIFEST.txt with SHA-256 hashes of all test images and labels."""
    manifest_lines = []
    for subdir_name in ["images", "labels"]:
        subdir = test_dir / subdir_name
        if not subdir.exists():
            continue
        for f in sorted(subdir.iterdir()):
            if not f.is_file():
                continue
            sha = hashlib.sha256(f.read_bytes()).hexdigest()
            rel = f"{subdir_name}/{f.name}"
            manifest_lines.append(f"{sha}  {rel}")

    manifest_path = test_dir / "MANIFEST.txt"
    manifest_path.write_text("\n".join(manifest_lines) + "\n")
    print(f"  Generated {manifest_path} ({len(manifest_lines)} entries)")


def write_data_yaml(yaml_out: Path, output_dir: Path) -> None:
    """Write our standard data.yaml."""
    # Make path relative to yaml_out's parent
    try:
        rel_path = output_dir.resolve().relative_to(yaml_out.resolve().parent)
    except ValueError:
        rel_path = output_dir.resolve()

    config = {
        "path": str(rel_path),
        "train": "train/images",
        "val": "val/images",
        "test": "test/images",
        "nc": len(EXPECTED_NAMES),
        "names": EXPECTED_NAMES,
    }

    yaml_out.parent.mkdir(parents=True, exist_ok=True)
    with open(yaml_out, "w") as f:
        yaml.dump(config, f, default_flow_style=False, sort_keys=False)

    print(f"  Wrote {yaml_out}")


def main() -> int:
    args = parse_args()
    source: Path = args.source.expanduser().resolve()
    output: Path = args.output.expanduser().resolve()
    data_yaml_out: Path = args.data_yaml_out.expanduser().resolve()

    if not source.exists() or not source.is_dir():
        print(f"ERROR: Source directory does not exist: {source}", file=sys.stderr)
        return 1

    print(f"Source:  {source}")
    print(f"Output:  {output}")
    print(f"Yaml:    {data_yaml_out}")
    if args.dry_run:
        print("MODE:    DRY RUN (no files will be copied)")
    print()

    # Step 1: Read Roboflow data.yaml
    print("Reading Roboflow data.yaml...")
    rf_config = read_roboflow_yaml(source)
    rf_names = rf_config.get("names", [])
    print(f"  Roboflow classes: {rf_names}")
    print(f"  Our classes:      {EXPECTED_NAMES}")

    # Step 2: Build class remap
    remap = build_class_remap(rf_names)
    if remap is None:
        print("  Class order matches — no remapping needed.")
    else:
        print(f"  Remap needed: {remap}")
    print()

    # Step 3: Detect split directories
    # Roboflow uses "valid", we use "val"
    split_map = {
        "train": "train",
        "val": "valid" if (source / "valid").exists() else "val",
        "test": "test",
    }

    # Step 4: Copy splits
    for our_name, rf_name in split_map.items():
        src_split = source / rf_name
        dst_split = output / our_name
        print(f"Copying {rf_name}/ → {our_name}/...")
        if not src_split.exists():
            print(f"  WARNING: {src_split} does not exist — skipping")
            continue
        count = copy_split(src_split, dst_split, remap, args.dry_run)
        print(f"  {count} images")
    print()

    if args.dry_run:
        print("DRY RUN complete. No files were copied.")
        return 0

    # Step 5: Create test/empty/ placeholder
    empty_dir = output / "test" / "empty"
    empty_dir.mkdir(parents=True, exist_ok=True)
    print(f"  Created {empty_dir}/")

    # Step 6: Generate MANIFEST.txt
    print("Generating test MANIFEST.txt...")
    generate_manifest(output / "test")
    print()

    # Step 7: Write data.yaml
    print("Writing data.yaml...")
    write_data_yaml(data_yaml_out, output)
    print()

    # Step 8: Run validate_dataset.py
    print("Running validate_dataset.py...")
    script_dir = Path(__file__).resolve().parent
    result = subprocess.run(
        [sys.executable, str(script_dir / "validate_dataset.py"),
         "--data-yaml", str(data_yaml_out)],
        capture_output=False,
    )

    if result.returncode != 0:
        print("\nNormalization complete but validation FAILED.")
        print("Fix the issues above and re-run validation.")
        return 1

    print("\nDone. Dataset is ready for training.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
