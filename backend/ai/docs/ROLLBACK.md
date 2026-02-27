# Rollback Procedure

> Status: DRAFT — finalize at Step 9B
> Target: revert to previous model version within 10 minutes

---

## When to Rollback

- New model's live accuracy drops below previous version
- Inference latency exceeds budget for > 5 minutes
- Model produces systematic misclassifications (e.g., all cups → straw)

## Rollback Steps

1. Update `ai/models/manifest.json`: change `active_model_path` to previous version
2. Update `.env`: set `AI_MODEL_PATH` to previous model path
3. Restart queue workers: `php artisan queue:restart`
4. Verify: POST a test image, confirm `model_version` in response matches previous version
5. Log the rollback in `ai/logs/` with reason and timestamp

## Model Version History

| Version | Path | Date | Notes |
|---------|------|------|-------|
| (to be filled) | | | |

---

## TODO

- [ ] Write rollback verification script
- [ ] Define rollback drill procedure
- [ ] Test rollback in staging before production
