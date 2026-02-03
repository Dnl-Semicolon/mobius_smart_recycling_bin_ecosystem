# AI Source Code

## Reading Order

Read the files in this order. Each file has numbered comments `[1]`, `[2]`, etc. that guide you through the logic.

| # | File | Purpose | Lines |
|---|------|---------|-------|
| 1 | `config.py` | Paths and constants | ~70 |
| 2 | `core.py` | Model loading, inference, matching | ~150 |
| 3 | `class_map.py` | ImageNet → waste_type mappings | ~155 |
| 4 | `infer_seed.py` | Batch inference entry point | ~110 |
| 5 | `report.py` | Human-readable results | ~160 |
| 6 | `dataset.py` | Add images, check integrity | ~220 |

**Total: ~865 lines** (down from ~950 with clearer structure)

## Quick Commands

```bash
# From backend/ directory:
source ai/.venv/bin/activate

# Check data integrity
python ai/src/dataset.py check

# Run inference on all seed images
python ai/src/infer_seed.py

# View results
python ai/src/report.py

# Add a new image
python ai/src/dataset.py add ~/Desktop/photo.jpg cup

# See ImageNet → waste_type mappings
python ai/src/class_map.py
```

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                        config.py                            │
│                   (paths, constants)                        │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                         core.py                             │
│            (model loading, inference, matching)             │
│                              │                              │
│                    uses class_map.py                        │
│                 (ImageNet → waste_type)                     │
└─────────────────────────────────────────────────────────────┘
                              │
              ┌───────────────┼───────────────┐
              ▼               ▼               ▼
        infer_seed.py    report.py      dataset.py
        (batch run)      (display)      (manage data)
```

## Comment Style

Comments explain **WHY**, not WHAT. Each section is numbered:

```python
# [2.3.1] Add batch dimension
# Model expects shape [batch, channels, height, width].
# unsqueeze(0) turns [3, 224, 224] into [1, 3, 224, 224].
tensor = tensor.unsqueeze(0)
```

The number `[2.3.1]` means:
- File `[2]` = core.py
- Section `[2.3]` = INFERENCE
- Subsection `[2.3.1]` = Add batch dimension
