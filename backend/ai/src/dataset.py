#!/usr/bin/env python3
"""
[6] DATASET — Manage your image collection.

WHY THIS FILE EXISTS:
As you collect more images for training, you need tools to:
1. Add new images to the dataset (copying + CSV update)
2. Check that CSV and disk are in sync (no missing/orphan files)

This file combines both functions with a simple CLI.

USAGE:
    python ai/src/dataset.py add ~/photo.jpg cup          # Add an image
    python ai/src/dataset.py add ~/photo.jpg cup --dry-run # Preview only
    python ai/src/dataset.py check                        # Verify integrity

PREVIOUS: report.py [5] for viewing inference results.
THIS IS THE LAST FILE — you've read the whole codebase!
"""
import argparse
import csv
import shutil
from pathlib import Path

from config import SEED_DIR, SEED_LABELS_CSV, VALID_IMAGE_EXTENSIONS


# =============================================================================
# [6.1] ADD IMAGE
# =============================================================================

def get_next_filename(extension: str) -> str:
    """
    Generate the next available cup_XX filename.

    WHY AUTO-NUMBERING:
    Consistent naming makes sorting and debugging easier.
    cup_01.jpg, cup_02.jpg, ... instead of IMG_4521.jpg, photo(1).jpg
    """
    existing = list(SEED_DIR.glob("cup_*.jpg")) + list(SEED_DIR.glob("cup_*.png"))
    max_num = 0

    for path in existing:
        parts = path.stem.split("_")
        if len(parts) >= 2 and parts[1].isdigit():
            max_num = max(max_num, int(parts[1]))

    return f"cup_{max_num + 1:02d}{extension}"


def add_image(source_path: Path, waste_type: str, custom_name: str | None, dry_run: bool) -> int:
    """
    Add a new image to the seed dataset.

    Steps:
    1. Validate the source file exists and is an image
    2. Generate or use custom filename
    3. Copy to seed directory
    4. Append row to CSV
    """

    # [6.1.1] Validate source
    source = source_path.expanduser().resolve()

    if not source.exists():
        print(f"Error: File not found: {source}")
        return 1

    if not source.is_file():
        print(f"Error: Not a file: {source}")
        return 1

    if source.suffix.lower() not in VALID_IMAGE_EXTENSIONS:
        print(f"Error: Invalid extension: {source.suffix}")
        print(f"Valid: {', '.join(VALID_IMAGE_EXTENSIONS)}")
        return 1

    # [6.1.2] Validate destination
    if not SEED_DIR.exists():
        print(f"Error: Seed directory not found: {SEED_DIR}")
        return 1

    if not SEED_LABELS_CSV.exists():
        print(f"Error: Labels CSV not found: {SEED_LABELS_CSV}")
        return 1

    # [6.1.3] Determine filename
    dest_name = custom_name if custom_name else get_next_filename(source.suffix.lower())
    dest_path = SEED_DIR / dest_name

    # [6.1.4] Check for duplicates
    if dest_path.exists():
        print(f"Error: File already exists: {dest_path}")
        return 1

    with SEED_LABELS_CSV.open("r", encoding="utf-8") as f:
        existing_names = {row["filename"].strip() for row in csv.DictReader(f)}

    if dest_name in existing_names:
        print(f"Error: Filename already in CSV: {dest_name}")
        return 1

    # [6.1.5] Dry run
    if dry_run:
        print("DRY RUN — no changes made")
        print(f"Would copy: {source}")
        print(f"       to: {dest_path}")
        print(f"Would add to CSV: {dest_name},{waste_type}")
        return 0

    # [6.1.6] Execute
    shutil.copy2(source, dest_path)
    print(f"Copied: {source.name} → {dest_name}")

    with SEED_LABELS_CSV.open("a", encoding="utf-8", newline="") as f:
        csv.writer(f).writerow([dest_name, waste_type])
    print(f"Added to CSV: {dest_name} → {waste_type}")

    print()
    print("Next steps:")
    print("  python ai/src/dataset.py check    # Verify integrity")
    print("  python ai/src/infer_seed.py       # Run inference")
    print("  python ai/src/report.py           # See results")

    return 0


# =============================================================================
# [6.2] CHECK INTEGRITY
# =============================================================================

def check_integrity() -> int:
    """
    Verify that CSV entries match files on disk.

    WHY THIS MATTERS:
    If CSV lists a file that doesn't exist, inference will fail.
    If disk has a file not in CSV, you're missing labels.
    Running this after adding images catches mistakes early.
    """

    if not SEED_LABELS_CSV.exists():
        print(f"Error: {SEED_LABELS_CSV} not found")
        return 1

    # [6.2.1] Get files from CSV
    csv_files = set()
    with SEED_LABELS_CSV.open("r", encoding="utf-8") as f:
        for row in csv.DictReader(f):
            csv_files.add(row["filename"].strip())

    # [6.2.2] Get files from disk
    disk_files = set()
    for path in SEED_DIR.iterdir():
        if path.suffix.lower() in VALID_IMAGE_EXTENSIONS:
            disk_files.add(path.name)

    # [6.2.3] Compare
    missing_on_disk = csv_files - disk_files
    missing_in_csv = disk_files - csv_files

    issues = 0

    if missing_on_disk:
        print("FILES IN CSV BUT NOT ON DISK (missing images):")
        for f in sorted(missing_on_disk):
            print(f"  - {f}")
        issues += len(missing_on_disk)
        print()

    if missing_in_csv:
        print("FILES ON DISK BUT NOT IN CSV (unlabeled):")
        for f in sorted(missing_in_csv):
            print(f"  - {f}")
        issues += len(missing_in_csv)
        print()

    if issues == 0:
        print(f"OK: {len(csv_files)} images in CSV, all present on disk")
        print("No missing or unlabeled files")
        return 0
    else:
        print(f"ISSUES: {issues} file(s) need attention")
        return 1


# =============================================================================
# [6.3] CLI
# =============================================================================

def main() -> int:
    """
    Command-line interface.

    WHY SUBCOMMANDS:
    'add' and 'check' are distinct operations. Subcommands make the CLI
    self-documenting: python ai/src/dataset.py --help shows both options.
    """
    parser = argparse.ArgumentParser(
        description="Manage the seed image dataset",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
Examples:
    python ai/src/dataset.py add ~/Desktop/photo.jpg cup
    python ai/src/dataset.py add ~/Desktop/photo.jpg cup --name cup_15_starbucks.jpg
    python ai/src/dataset.py add ~/Desktop/photo.jpg cup --dry-run
    python ai/src/dataset.py check
        """,
    )

    subparsers = parser.add_subparsers(dest="command", required=True)

    # [6.3.1] Add subcommand
    add_parser = subparsers.add_parser("add", help="Add a new image to the dataset")
    add_parser.add_argument("image_path", type=Path, help="Path to image file")
    add_parser.add_argument("waste_type", help="Label (cup, lid, straw, napkin, etc.)")
    add_parser.add_argument("--name", help="Custom filename (default: auto cup_XX.ext)")
    add_parser.add_argument("--dry-run", action="store_true", help="Preview without changes")

    # [6.3.2] Check subcommand
    subparsers.add_parser("check", help="Verify CSV matches files on disk")

    args = parser.parse_args()

    if args.command == "add":
        return add_image(args.image_path, args.waste_type, args.name, args.dry_run)
    elif args.command == "check":
        return check_integrity()

    return 1


if __name__ == "__main__":
    raise SystemExit(main())


# =============================================================================
# [6.4] READING ORDER — COMPLETE!
# =============================================================================
#
# You've finished reading the AI codebase. Here's the full map:
#
#   [1] config.py     — Paths and constants
#   [2] core.py       — Model loading, inference, matching
#   [3] class_map.py  — ImageNet → waste_type mappings
#   [4] infer_seed.py — Batch inference entry point
#   [5] report.py     — Human-readable results
#   [6] dataset.py    — Add images, check integrity (YOU ARE HERE)
#
# To run the full pipeline:
#   1. python ai/src/dataset.py check     # Ensure data is valid
#   2. python ai/src/infer_seed.py        # Run inference
#   3. python ai/src/report.py            # View results
#
# To add more data:
#   python ai/src/dataset.py add ~/photo.jpg cup
#
# =============================================================================
