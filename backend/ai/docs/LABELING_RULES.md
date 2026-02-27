# Labeling Rules

> Status: FINAL — ready for Phase 1B annotation (2026-02-06)
> Used by: anyone annotating images for YOLO training

---

## Classes

| ID | Name | What to annotate |
|----|------|-----------------|
| 0 | cup | Any paper or plastic cup, regardless of brand or size |
| 1 | lid | Any cup lid (flat or dome), on or off the cup |
| 2 | straw | Any straw, visible portion only |
| 3 | liquid_waste | Any visible liquid spill, pool, or drip |

## Bounding Box Rules

- Draw the tightest axis-aligned rectangle that contains the entire visible object
- If an object is partially occluded, draw the box around the VISIBLE portion only
- Minimum box size: 10x10 pixels (smaller objects are likely too small for the model)

## Multi-Object Rules

- **Cup with lid attached:** Draw 2 boxes — one for the cup body, one for the lid
- **Straw inside a cup:** Draw a straw box IF the straw is visible (even partially)
- **Multiple cups in frame:** Each cup gets its own box
- **Cup on its side:** Still label as cup, box around visible portion

## Edge Cases (resolved)

- **Crumpled/crushed cups:** Label as `cup`. The model should learn to recognize cups in any condition since bins receive crushed cups frequently. Box the visible mass tightly.
- **Straw wrapper:** Do NOT label as `straw`. Only label the straw itself (the tube). Wrappers are paper waste and not in scope.
- **Liquid inside a transparent cup:** Do NOT label the liquid separately if it is fully contained inside a cup. Only label `liquid_waste` for spills, pools, or drips that are **outside** a container.
- **Minimum visibility:** An object must have at least **25% of its area visible** to be labeled. Objects with less than 25% visibility (e.g., barely peeking from behind another object) should be skipped. The 10x10 pixel minimum box size also applies.

## Annotation Format

YOLO `.txt` format: one file per image, one line per object:
```
<class_id> <center_x> <center_y> <width> <height>
```
All values normalized to 0.0-1.0 relative to image dimensions.

## Quality Checks

- [ ] Inter-annotator agreement target: >= 0.85 on 30 shared images
- [ ] No class ID outside range 0-3
- [ ] No bbox coordinates outside 0.0-1.0
