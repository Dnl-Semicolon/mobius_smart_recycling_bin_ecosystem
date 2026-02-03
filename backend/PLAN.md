# Plan (High-Level, Beginner-Friendly)

## 1. Problems to Solve First
- Clarify the core product goal: what the system should do for real users.
- Identify the most critical user flows (the smallest set that delivers value).
- Define data you must store: key entities, relationships, and lifecycle.
- Decide what “done” looks like for the first version (scope boundaries).
- Ensure you can run the app locally: environment setup, database, and basic deployment path.

## 2. Early Decisions to Make
- Target users and use cases (who uses it and why).
- The simplest system architecture (monolith vs. microservices). Start monolith.
- Data ownership and privacy expectations (especially for future AI use).
- The initial data model: core tables and relationships.
- How you will handle authentication and roles.
- The testing approach: minimal automated tests from day one.

## 3. What You Should NOT Do Yet
- Do not add AI features yet; you first need clean data and stable workflows.
- Do not build advanced scaling or microservices prematurely.
- Do not over-design the database; start with the minimum needed.
- Do not add complex frontend frameworks unless the UI demands it.
- Do not optimize performance before you have real usage data.

## 4. A “Small but Real” First Milestone
- A working app that lets a user:
  - Sign in
  - Create and view one core record (your main object)
  - See a simple dashboard or list page
- Data is stored in the database, not mock data.
- A few basic tests pass, proving the core flow works.
- You can run it locally from scratch and show it end-to-end.

## Preparing the System for Future AI (without implementing AI yet)
- **What data should be captured (based on existing tables/flows):** Keep complete, consistent records for `outlets`, `bins`, `bin_assignments`, and `detection_events`. That means accurate outlet location and operating details; bin status and fill level (0–100); assignment history with `assigned_at`/`unassigned_at`; and detection events with `bin_id`, `waste_type`, `confidence`, `detected_at`, and `image_path` pointing to the stored image. This preserves the core signals needed later for classification and routing without adding new features now.
- **What boundaries/interfaces should exist between Laravel and the AI module (contract only):** Define a single “AI adapter” boundary in Laravel that speaks JSON in/out, regardless of whether the AI module is invoked via CLI or HTTP. Contract shape should be stable and minimal: inference requests include `bin_id`, `detected_at`, and `image_path` (plus optional context like `outlet_id` if derivable); responses return `waste_type`, `confidence`, and a model/version tag. For routing, requests include a list of bins with location and readiness signals; responses return an ordered list with per-stop metadata. Implementation choice (CLI vs HTTP) can change later without changing this contract.
- **What assumptions must be preserved now so AI training/inference is possible later:** Do not overwrite or delete historical detection events or assignments (history is training data). Keep enums like `waste_type` and `status` stable and versioned if they ever change. Preserve consistent time semantics (`detected_at`, `assigned_at`, `unassigned_at`) and units (fill level 0–100, lat/long decimals). Maintain durable storage for `image_path` assets so future training can access the raw inputs.

## Phase 0: Camera → Inference on Mac (No Training)
- **Status:** Completed. Local inference script works.
- **Goal:** Prove you can capture a real image on your Mac, run a single local inference, and record the result using existing data terms (`waste_type`, `confidence`, `detected_at`, `image_path`).
- **Steps (setup → run → output):**
  - Confirm you can capture a photo from a Mac camera and save it locally with a clear filename.
  - Decide which existing `bin_id` the photo should be associated with (use an existing bin in the database).
  - Run a local inference flow (manual or script) that outputs a single prediction with `waste_type` and `confidence`.
  - Record one `detection_events` entry that references the saved `image_path` and `detected_at` time.
  - Verify the event appears in the admin UI list and detail view.
- **Done means:** One real photo leads to one visible `detection_events` record with plausible `waste_type` and `confidence`, tied to a real `bin_id`.
- **Do NOT do yet:** No model training, no automated pipelines, no new tables, no background jobs, no optimization or scaling work.

## Phase 1: Cup Object Detection Dataset + Labeling (No Training Yet)

### Phase 1A: Seed Dataset (10–30 images, no labeling tools)
- **Status:** Completed. Seed images captured with naming convention; README added. Images are not committed.
- **Goal:** A stress-free, same-day starter set that proves capture and basic organization.
- **Images to collect:** 10–30 photos across simple variations: paper/plastic cups, lids, straws, napkins, liquid spills; empty vs. partially filled bins; mixed backgrounds and lighting.
- **Label format to use:** Simple file naming or a tiny CSV/JSON list that maps filename → `waste_type` (no boxes yet).
- **Tool to label with:** None. Manual notes only.
- **Where to store data safely (gitignore guidance):** Keep images in `ai/data/cups_raw/seed/` and make sure `ai/data/` is gitignored.
- **Done means:** 10–30 images captured, organized, and mapped to `waste_type` values.

### Phase 1B: Full Dataset (later)
- **Images to collect (quantity + variety):** 500–1,000 images covering real outlet conditions: different cup materials, lids/straws/napkins, liquid spills, empty/partial/full bins, varied lighting, angles, and backgrounds.
- **Label format to use:** COCO-style bounding boxes with categories matching `waste_type` (`paper_cup`, `plastic_cup`, `lid`, `straw`, `napkin`, `liquid_waste`).
- **Tool to label with:** CVAT (self-hosted or desktop) for consistent COCO export.
- **Where to store data safely (gitignore guidance):** Keep raw images and labels outside git or in `ai/data/` with gitignore protection.
- **Done means:** A COCO export, a stable category list aligned to `waste_type`, and a documented image count and split (train/val/test).

## Next Step (One Task Only)
- Add a simple filename → `waste_type` mapping list in `ai/data/cups_raw/seed/labels_seed.csv` (done).

---

## AI Tooling Workflow (How to Use the Scripts)

### Quick Reference

| Script | Purpose | Command |
|--------|---------|---------|
| `infer_one.py` | Single image inference | `python ai/src/infer_one.py <image_path>` |
| `infer_seed.py` | Batch inference on seed dataset | `python ai/src/infer_seed.py` |
| `evaluate_seed.py` | Show accuracy stats | `python ai/src/evaluate_seed.py` |
| `report_seed.py` | Human-readable report | `python ai/src/report_seed.py` |
| `check_seed_data.py` | Verify CSV matches files | `python ai/src/check_seed_data.py` |
| `add_image.py` | Add new image to dataset | `python ai/src/add_image.py <path> <label>` |

### Before Running Any Script

```bash
cd /Users/danieltan/mobius_smart_recycling_bin_ecosystem/backend
source ai/.venv/bin/activate
```

### Adding New Images (Phase 1B Workflow)

1. **Take a photo** of a cup/lid/straw/etc.
2. **Add it to the dataset:**
   ```bash
   python ai/src/add_image.py ~/Desktop/my_photo.jpg cup
   ```
   - Auto-generates filename like `cup_16.jpg`
   - Or use custom name: `--name cup_16_blue_plastic.jpg`
   - Preview first: `--dry-run`

3. **Verify integrity:**
   ```bash
   python ai/src/check_seed_data.py
   ```

4. **Re-run inference:**
   ```bash
   python ai/src/infer_seed.py
   python ai/src/report_seed.py
   ```

### Valid Waste Types (Labels)

Use consistent labels. Current convention:
- `cup` — any cup (paper or plastic)
- `plastic_cup` — specifically plastic
- `paper_cup` — specifically paper
- `lid` — cup lids
- `straw` — straws
- `napkin` — napkins
- `liquid_waste` — spilled liquids
- `unknown` — test images or unclear items

### Output Locations

- Inference results: `ai/runs/seed_inference_results.jsonl`
- Images: `ai/data/cups_raw/seed/` (gitignored)
- Labels: `ai/data/cups_raw/seed/labels_seed.csv` (committed)
