# Evaluation Protocol v1

> Status: FINAL (2026-02-06)
> All bakeoff baselines MUST use this identical protocol

---

## Sacred Test Set

- Location: `ai/data/cups_labeled/yolo_day1/test/`
- Manifest: `ai/data/cups_labeled/yolo_day1/test/MANIFEST.txt`
- Rule: NEVER add, remove, or relabel images after Step 3 split lock

## Evaluation Parameters (fixed for all experiments)

| Parameter | Value | Rationale |
|-----------|-------|-----------|
| Confidence threshold | 0.25 | Ultralytics default, captures all candidates |
| NMS IoU threshold | 0.45 | Standard YOLO NMS |
| Image size | 640 | Match training size |
| Device | CPU (for comparable latency) | Unless noted |

## Required Metrics (every experiment must report ALL)

| Metric | Source | Notes |
|--------|--------|-------|
| mAP@0.5 | `yolo val` | Primary quality metric |
| mAP@0.5:0.95 | `yolo val` | Stricter multi-IoU metric |
| AP@0.5 per class | `yolo val` | cup, lid, straw, liquid_waste |
| Precision per class | Computed | TP / (TP + FP) |
| Recall per class | Computed | TP / (TP + FN) |
| Inference latency P50 | 50 runs, `time.perf_counter()` | Wall time on test image |
| Inference latency P95 | 50 runs | |
| Model file size (MB) | `os.path.getsize()` | |
| FP on empty frames | 10 empty-bin images | Count of false detections |

## Output Format

Each experiment produces:
1. `ai/runs/bakeoff/<experiment_name>.jsonl` — one JSON line per test image
2. `ai/runs/bakeoff/<experiment_name>_metrics.json` — aggregated metrics
3. Row in `ai/runs/bakeoff/comparison.md`

## Comparison Table Template

See `SCORE_FORMULA_v1.md` for how to rank experiments.

---

## Resolved Items

- [x] **Empty bin images**: Human will capture 10 empty-bin photos during Phase 1B data collection (Step 2). Place in `ai/data/cups_labeled/yolo_day1/test/empty/` with no label files.
- [x] **Evaluation script**: `ai/src/evaluate_bakeoff.py` — computes all metrics automatically.
- [x] **Reproducibility**: A rerun is reproducible if it uses the same model weights, the same sacred test set (verified by SHA-256 manifest), and the same evaluation parameters from the table above. The `evaluate_bakeoff.py` script logs all parameters in its output JSON for auditability.
