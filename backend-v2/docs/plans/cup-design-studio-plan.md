# Cup Design Studio / Plan

> Brand owners self-serve a new cup design end to end without an engineer touching the dataset, the weights, or the deployment. Plan only. Implementation work is intentionally out of scope for this document.

---

## A. Problem framing

Every new cup design from a partner currently requires a Mobius engineer to relabel a dataset, retrain the cup-brand classifier, validate the new weights against the existing test set, then redeploy across the bin fleet. The round trip is measured in days, sometimes more, and the engineer time it consumes is the single biggest unit-economics blocker on the partner side. A brand that wants to launch a limited-edition seasonal cup needs to wait on Mobius before the bin network can recognise it, which means the brand multiplier that drives the whole loyalty story is not available during the campaign window the brand actually cares about.

The success criterion this plan commits to: a brand owner with no engineering help registers a new cup design, uploads training images, kicks off training, reviews the result, and has the new design recognised on real bins within one working day, end to end. The Mobius engineering team is not in the loop for any step of that flow.

This is a B2B platform feature for the brand-owner role only. Outlet owners, public users, and councils do not see the studio.

## B. Domain model

Three new tables. All foreign keys cascade on delete because a cup design without its parent brand is not a coherent record.

### `cup_designs`

| column | type | notes |
|---|---|---|
| `id` | bigInteger PK |
| `brand_id` | foreignId, references `brands.id`, on delete cascade |
| `name` | string, indexed alongside `brand_id` |
| `slug` | string, unique within brand |
| `status` | enum: `draft`, `training`, `active`, `archived`. Default `draft`. |
| `campaign_starts_at` | timestamp, nullable |
| `campaign_ends_at` | timestamp, nullable |
| `model_version_id` | foreignId nullable, references `cup_design_model_versions.id`. The currently deployed version. |
| `training_metrics_json` | json nullable. Last successful training run summary. |
| `created_at`, `updated_at` | timestamps |

Indexes: `(brand_id, status)` for the list page. `(brand_id, slug)` unique for slug lookup.

### `cup_design_images`

| column | type | notes |
|---|---|---|
| `id` | bigInteger PK |
| `cup_design_id` | foreignId, references `cup_designs.id`, on delete cascade |
| `s3_key` | string |
| `captured_at` | timestamp, nullable. `null` when uploaded from disk, set when captured live. |
| `angle_label` | enum: `front`, `side`, `top`, `back`, `unknown`. Default `unknown`. |
| `validated_bool` | boolean. Default false. Set to true once the dedupe and angle-variety checks pass. |
| `created_at`, `updated_at` | timestamps |

Indexes: `cup_design_id` for the upload manifest read, plus `(cup_design_id, validated_bool)` for the training-eligible filter.

### `cup_design_model_versions`

| column | type | notes |
|---|---|---|
| `id` | bigInteger PK |
| `cup_design_id` | foreignId, references `cup_designs.id`, on delete cascade |
| `roboflow_workflow_id` | string |
| `accuracy_metrics_json` | json. Per-class precision, recall, mAP. |
| `deployed_at` | timestamp, nullable |
| `rolled_back_at` | timestamp, nullable |
| `created_at`, `updated_at` | timestamps |

Indexes: `cup_design_id` for the version history view, plus `roboflow_workflow_id` unique for the polling worker.

A migration set in `database/migrations/` creates the three tables in order. A factory and seeder for `cup_designs` is required so feature tests can spin a draft without touching Roboflow.

## C. UX flow / four steps

The wizard runs as a single Inertia page with four step states held in client URL hash, so a brand owner can copy the link to a colleague and the same step opens. Server state is persisted on each step transition so a refresh never loses progress.

### 1. Define

Inputs: `name`, `brand_id` (auto-locked to the logged-in brand owner's brand, no dropdown), `campaign_starts_at`, `campaign_ends_at`. The campaign window is optional; leaving both blank flags the design as evergreen.

The submit creates a `cup_designs` row with `status = draft` and routes to step 2.

Validation: name is unique within brand, length 3 to 60, no emoji, no leading or trailing whitespace. Slug is generated from the name on the server; the brand owner does not see or edit it.

### 2. Upload

A drag-and-drop zone that also accepts the system clipboard paste, mimicking the bin-client capture flow exactly so the muscle memory transfers. Each image lands in the existing S3 bucket via the bin-capture upload helper, and a row is written to `cup_design_images` with `validated_bool = false` and `angle_label = unknown`.

Server-side validation runs after every upload batch:
- minimum 20 distinct images required before the train button enables
- angle-variety heuristic: pHash dedupe drops near-duplicates, and a server-side classifier marks images by inferred angle so the brand owner sees a four-quadrant indicator filling up
- rejected uploads return the exact API error message plus a one-line "what to try" string in the same response payload

The brand owner can delete or replace any image before training. Once training kicks off, the image set is frozen for that version.

### 3. Train

A single primary CTA on this step, "Start training". Clicking it dispatches a `StartCupDesignTraining` job onto the queue, which:
1. Bumps `cup_designs.status` to `training`.
2. Calls Roboflow's workflow-create endpoint with the validated image set.
3. Persists the returned `roboflow_workflow_id` on a fresh `cup_design_model_versions` row.
4. Schedules a polling job that runs every 30 seconds for up to 30 minutes.

The page shows a stage progress indicator with realistic ETA copy: "Training typically takes 10 to 20 minutes. You can close this tab and we will email you when it finishes." A websocket or Inertia poll fetches the latest status every 5 seconds while the page is open.

The polling job updates `cup_design_model_versions.accuracy_metrics_json` as soon as Roboflow reports done. If training fails, the row records the failure and the design returns to `draft` so the brand owner can fix uploads and retry.

### 4. Activate

The brand owner reviews a sample grid of validation predictions side-by-side with the source images. Below the grid, the accuracy metrics are shown: per-class precision and recall in JetBrains Mono, plus an overall mAP figure as the headline number.

The activate button is gated on the accuracy threshold: mAP must be ≥ 0.85 against a held-out test set, OR the brand owner explicitly acknowledges a lower-accuracy launch via a confirmation dialog. The dialog quotes the actual mAP number and surfaces the existing pre-deploy validation alert (see section D) inline.

On confirm, an `ActivateCupDesignVersion` job:
1. Updates `cup_designs.model_version_id` to the new version.
2. Sets `cup_designs.status = active` and `cup_design_model_versions.deployed_at = now()`.
3. Notifies bin clients to pull the new model on their next heartbeat.

Rollback is a single button on the version history view that nulls out `cup_designs.model_version_id` (or sets it to a previous version) and stamps `rolled_back_at` on the version being abandoned.

## D. Integration with the existing detection pipeline

Crucially: the existing brand-multiplier and points calculation rules do not change. The `DetectionService::calculatePoints()` logic that lives in `app/Services/DetectionService.php` does not gain new conditional branches. A new cup design simply registers under the same `brand_id`, so when the detector recognises it the existing brand-match logic fires unchanged. The studio changes the catalogue, not the rules.

A pre-deploy validation pass runs on every `ActivateCupDesignVersion` job before the version is marked active:

1. Pull every active cup design's reference test set from S3.
2. Run the candidate model against that aggregated test set.
3. For each existing design, compare detection rate against the previously deployed model's rate.
4. If any existing design's detection rate drops by more than 5 percentage points, the activation is blocked and the brand owner sees a message naming the affected designs.

The blocked-activation message is sentence-cased, names exact figures, and offers two paths: "retrain with more images of {affected_design.name}" or "contact engineering". The brand owner cannot bypass the alert; only an engineer can override it via an admin-side action.

## E. UI shape

Inertia + React + Tailwind, restyled to the sharp-future tokens already shipped on `feat/home-hifi-sharp-future`. The dashboard reskin is happening on a separate track (the C1 work the prompt mentions); when the studio is built, it should consume the same `[data-theme="sharp-future-product"]` scope rather than the marketing scope.

### List page, `/brand/cup-designs`

A table of the brand owner's cup designs.

| column | content |
|---|---|
| Name | sentence-case, link to detail page |
| Status | colored chip: `draft` muted neutral, `training` primary chroma with a quiet pulse, `active` filled primary, `archived` outline |
| Last trained | relative time, mono numerals |
| Accuracy (mAP) | mono percentage with a hairline bar showing position against the 0.85 threshold |
| Campaign window | start to end formatted like `Mar 04 to Apr 12`, or "evergreen" if both nulls |

A primary CTA button "Register a cup design" sits top-right, calling out the `/brand/cup-designs/new` route.

Empty state: an icon-free panel with a single sentence ("Your brand has no cup designs registered yet.") and a primary CTA "Register your first cup design". Sharp-future voice, sentence case, no exclamation marks.

### Detail / wizard page, `/brand/cup-designs/{slug}`

A four-step stepper across the top, generous spacing between steps, calm midnight-teal accents for the active step indicator. The step body fills the rest of the page. Steps 1 and 4 use full-width content; steps 2 and 3 use an asymmetric two-column layout (form on the left, contextual help on the right).

Accuracy figures and timestamps render in JetBrains Mono with tabular numerals. Long-form copy uses Switzer body weights. No emoji, no exclamation marks, no negative parallelism in the help copy.

### Notification surfaces

- A toast confirms each step transition: "Cup design saved. Step 2 of 4."
- A persistent notification card surfaces on the brand-owner overview when training completes, with the cup design name and a "Review and activate" link.
- Email goes out on training-complete and on activation-blocked events, using the same sharp-future voice.

## F. Engineering notes

- **Queue workers.** Roboflow polling and the activation job both run on the `default` queue, which already has a worker pool. No new queue is needed. If the polling load grows, split into a `cup-design-polling` queue with a dedicated worker.
- **S3 / storage.** Training images use the existing S3-compatible bucket and the `BinCaptureUploadHelper` already in `app/Services/Uploads/`. The helper signs URLs, validates content type, and handles the multipart finalise step.
- **Auth.** Every studio route is gated by the `brand_owner` role guard middleware that already lives at `app/Http/Middleware/EnsureBrandOwner.php`. The `cup_designs.brand_id` column is enforced on every read and write so brand A never sees brand B's designs, even by guessing slug.
- **Roboflow client.** A new `RoboflowClient` service wraps the workflow-create, workflow-status, and dataset-upload endpoints. The Roboflow API token lives in `.env` as `ROBOFLOW_API_KEY`.
- **Tests.** PestPHP feature tests cover each wizard step in isolation, plus a happy-path integration test that exercises define → upload → train → activate against a mocked Roboflow client. The mock returns a deterministic accuracy payload so the activation gate can be asserted both above and below threshold.
- **Telemetry.** Every step transition writes a row to the existing `audit_logs` table with the actor, the cup design id, and the step. This is what the assessor audits later when the grant evidence asks "did the partner self-serve."
- **Backwards compatibility.** No existing route, no existing model, and no existing service contract changes. The studio is purely additive.

## G. Out of scope for v1

- Self-hosted YOLOv8 fine-tune. Tracked separately as Track B v2 and only revisited if Roboflow latency turns out unacceptable in production. Note the existing `weights.pt` artifact pattern would still apply if Track B v2 lands.
- Lid, straw, and liquid-waste design submission. v1 is cup designs only. The other waste classes do not have a brand axis worth surfacing to a brand owner.
- A public-facing model marketplace. Brand owners can train their own cup designs against their own brand only. They cannot share, sell, or reuse another brand's models.
- Multi-design batch training. Each cup design trains independently for v1.
- A11y compliance audit beyond keyboard navigation and visible focus rings. Full WCAG AA pass is a separate workstream.

## H. Copy / messaging guidance

The studio voice extends the sharp-future PRODUCT.md voice into a product-register surface. It stays sharp and confident but never inflates the stakes of what the brand owner is doing.

- No exclamation marks anywhere. No emoji. Sentence case throughout including buttons.
- No tricolon punchy headers. No "X. Y. Z." three-fragment patterns.
- No negative parallelism ("X, not Y" or "the question isn't X. The question is Y").
- No Unicode arrows in body copy. The chevron `›` used elsewhere in sharp-future is the only allowed glyph for trailing links.
- No "ecosystem" outside the brand positioning sentence.
- Errors quote the exact API message in mono followed by a one-line "what to try" sentence in the body register. Example: when Roboflow returns a 422 on a malformed image manifest, the toast reads `Roboflow rejected the dataset: image dimensions inconsistent. Try removing any image larger than 4096 pixels on either side and uploading again.`
- Empty states use one declarative sentence plus a primary CTA. No motivational copy.
- The activation-blocked alert names the affected designs explicitly and shows the exact percentage drop.

## Sequencing

A reasonable build order, not a commitment:

1. Migrations for the three tables, plus the factory.
2. `RoboflowClient` service and the contract test against it.
3. `BrandOwner\CupDesignController` for the list and the detail page, no wizard yet.
4. Step 1 (define) end to end with feature test.
5. Step 2 (upload) end to end with feature test, including the dedupe and angle-variety heuristics.
6. Step 3 (train) with the polling job and the queue worker, plus the failure-path test.
7. Step 4 (activate) with the pre-deploy validation pass and the activation-blocked path.
8. List-page status chips and accuracy bar.
9. Email notifications.
10. Audit log writes.
11. Rollback and version history view.

Each step lands as its own PR against `main`, gated on the feature test it introduces. The wizard is shippable behind a feature flag once steps 1 and 2 are in.
