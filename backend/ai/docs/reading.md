⏺ Step 4B Summary

- Endpoint: POST /api/v1/detect — separate from the existing detection-events apiResource (which remains read-only index/show)
- Validates: bin_id (required, integer, must exist in bins table), image (required, jpeg/png/jpg, max 5MB), detected_at (optional, defaults to now)
- Writes to disk: Image saved to storage/app/public/detection_images/YYYY/MM/DD/{hash}.ext via the public disk
- DB row created: detection_events row with bin_id (from request), waste_type = null, confidence = null, image_path (storage path), detected_at (from request or now)
- Response JSON (201): { data: { id, bin_id, detections: [], model_version: null, latency_ms: null, image_path, detected_at }, message: "Detection event created successfully." } — matches the inference contract schema with empty detections
- Migration changed: waste_type (string) and confidence (unsignedTinyInteger) made nullable on detection_events table, indexes preserved
- Tests prove: 12 tests — store returns 201 with correct structure, image persisted to disk, custom/default detected_at, PNG support, rejects missing bin_id, missing image, non-existent bin, non-image files, oversized files, empty request, and confirms POST /api/v1/detection-events still returns 405
- NOT implemented: No AI inference wiring — waste_type and confidence are always null, detections array is always empty, model_version and latency_ms are null. The Front Agent will wire in YOLO inference to populate these fields.

---
Files Changed

1) API

- app/Http/Controllers/Api/DetectionEventController.php — added store() method
- routes/api.php — added Route::post('detect', ...)

2) Validation

- app/Http/Requests/StoreDetectionRequest.php — new file

3) DB/Migration

- database/migrations/2026_02_06_082428_make_waste_type_and_confidence_nullable_on_detection_events.php — new file

4) Tests

- tests/Feature/Api/DetectEndpointTest.php — new file (12 tests, 44 assertions)
