⏺ ---
Summary

1. Expected Tree

```text
ai/data/
├── data.yaml                           ← points to cups_labeled/yolo_day1
└── cups_labeled/yolo_day1/
    ├── train/
    │   ├── images/   (≥10 images)
    │   └── labels/   (1:1 matching .txt, YOLO format, class IDs 0-3)
    ├── val/
    │   ├── images/   (≥3 images)
    │   └── labels/
    └── test/
        ├── images/   (≥3 images, SACRED after Step 3)
        ├── labels/
        ├── empty/    (10 empty-bin photos, no label files)
        └── MANIFEST.txt  (SHA-256 hashes)
```

2. Actual Tree

```text
ai/data/
├── data.yaml              ← DOES NOT EXIST
└── cups_labeled/yolo_day1/ ← COMPLETELY EMPTY (0 files)
```

3. Fix: normalize_dataset.py (delivered and tested)

When you have your Roboflow export:

```bash
source ai/.venv/bin/activate
python ai/src/normalize_dataset.py --source ~/Downloads/your-roboflow-export/
```

It handles:
- valid/ → val/ rename
- Class ID remapping if Roboflow uses a different order (verified with test)
- Copies images + labels into the expected tree
- Writes ai/data/data.yaml
- Creates test/empty/ and test/MANIFEST.txt
- Runs validate_dataset.py as the final gate

Both the happy path (same class order) and remap path (different class order) pass end-to-end.
