# Score Formula v1

> Status: FINAL (2026-02-06)
> Purpose: single comparable score for ranking bakeoff baselines

---

## Ranking Approach

Primary: **mAP@0.5 on sacred test set** (higher is better)

Tiebreaker order:
1. Per-class AP balance (prefer model where weakest class AP is highest)
2. Inference latency P95 (lower is better)
3. Model file size (smaller is better)

## Winner Selection Criteria

The winner is the model that:
1. Has the highest mAP@0.5, OR
2. Is within 5% mAP of the leader AND has meaningfully better latency or size

## Weighted Score (optional, for report)

```
weighted_score = 0.50 * mAP50_normalized
              + 0.20 * min_class_AP_normalized
              + 0.15 * (1 - latency_p95_normalized)
              + 0.15 * (1 - model_size_normalized)
```

Normalization: min-max across the 4 baselines (0.0 = worst, 1.0 = best).

---

## Resolved Items

- [x] **Weighting confirmed**: 50/20/15/15 split is appropriate for an FYP where detection accuracy is the primary deliverable. Project owner can adjust after seeing bakeoff results.
- [x] **Spill recall bonus**: No separate bonus weight. `liquid_waste` is already included in `min_class_AP` (the 20% weight rewards balanced per-class performance). If spill recall is notably poor, it will drag down `min_class_AP` and the overall score.
- [x] **"Meaningfully better" threshold**: A model is meaningfully better on a tiebreaker metric if the difference exceeds **10% relative** (e.g., latency 200ms vs 180ms is 10% — meaningful; 200ms vs 195ms is 2.5% — not meaningful).
