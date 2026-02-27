# Inference Contract v1

> Status: FROZEN
> Version: 1.0.0
> Frozen by: Front Agent (2026-02-06). Back Agent: pending counter-sign.

---

## Class Taxonomy

| YOLO Index | YOLO Class | WasteType Enum (Laravel) |
|------------|-----------|--------------------------|
| 0 | cup | paper_cup (default) |
| 1 | lid | lid |
| 2 | straw | straw |
| 3 | liquid_waste | liquid_waste |

Napkin: not in YOLO scope. `plastic_cup`: future sub-classifier, not YOLO.

## Confidence Conversion

Python float 0.0-1.0 → Laravel int 0-100: `int(round(float_conf * 100))`

## Confidence Thresholds

| Tier | Range (int) | Action |
|------|-------------|--------|
| Accept | >= 70 | Store waste_type as-is |
| Low confidence | 40-69 | Store waste_type + flag for review |
| Discard | < 40 | Store with waste_type = null |

## Python Output Schema

```json
{
  "detections": [
    {
      "waste_type": "cup",
      "confidence": 0.73,
      "bbox": [320.5, 240.1, 150.0, 200.0]
    }
  ],
  "model_version": "yolov11n_waste_v1",
  "latency_ms": 380
}
```

## Laravel Request Schema

```
POST /api/v1/detect
Content-Type: multipart/form-data

Fields:
  bin_id: integer (required, must exist in bins table)
  image: file (required, jpeg/png, max 5MB)
  detected_at: string (optional, ISO 8601, defaults to now)
```

## Laravel Response Schema

```json
{
  "data": {
    "id": 42,
    "bin_id": 1,
    "detections": [
      {
        "waste_type": "paper_cup",
        "confidence": 73,
        "bbox": [320.5, 240.1, 150.0, 200.0]
      }
    ],
    "model_version": "yolov11n_waste_v1",
    "latency_ms": 412,
    "image_path": "detection_images/2026/02/06/evt_42.jpg",
    "detected_at": "2026-02-06T14:30:00+08:00"
  },
  "message": "Detection event created successfully."
}
```

## Error Codes

| Code | Meaning |
|------|---------|
| 201 | Detection event created |
| 202 | Detection job queued (async mode) |
| 422 | Validation error (missing/invalid fields) |
| 500 | Inference failure |

## Null Detection

If model produces no detections above threshold:
- Response: `{"detections": [], "model_version": "...", "latency_ms": ...}`
- DB: Store event with `waste_type = null`, keep image_path for future labeling

---

## Decisions (locked at v1.0.0)

- [x] **Thresholds**: Locked from domain judgment (Accept >= 70, Low 40-69, Discard < 40). Will re-calibrate with real data after bakeoff if needed.
- [x] **Global vs per-class**: Using a **single global threshold** for v1. Per-class thresholds deferred to Step 8 when per-class calibration data exists.
- [x] **Front Agent review**: Signed off 2026-02-06.
- [ ] **Back Agent review**: Pending counter-sign.
