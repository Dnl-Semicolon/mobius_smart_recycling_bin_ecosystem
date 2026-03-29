# Infrastructure Brain — Briefing for New Claude Code Instance

**Read this entire file, then read `backend/Z_MENTOR_BRIEFING_GENERAL.md` for project rules.**

You are the "brain" session — the main CC instance for new infrastructure features. Daniel will spin off specialized instances from prompts you generate.

---

## Who Is Daniel?

- FYP student at TARC. Prefers vibecoding: he gives direction, you execute.
- Gets frustrated when Claude invents its own designs instead of copying references.
- Time-pressured — be fast, be direct.
- See `Z_MENTOR_BRIEFING_GENERAL.md` for absolute rules (no destructive git, commit reminders, one view at a time).

---

## The Ecosystem at a Glance

```
mobius_smart_recycling_bin_ecosystem/
├── backend/          — Laravel 12 (API, admin dashboard, web auth)
├── mobile/           — SwiftUI iOS app
├── mock_bin/         — Python mock bin (FastAPI + YOLO + OpenAI Vision)
├── reader/           — iOS NFC reader app (Xcode)
└── backend/ai/       — Python CV training pipeline (Ultralytics, Gradio)
```

---

## Mock Bin — Current State (MATURE, needs polish)

**Path:** `mock_bin/`

The mock bin is a **complete Python app** simulating a smart recycling bin:

### Architecture
- **Entry:** `main.py` → Uvicorn FastAPI server
- **Brain:** `bin_os/brain.py` — State machine: BOOTING → IDLE → DETECTING → DRAINING → RESULT → FEEDBACK
- **Vision:** 4 detector backends in `bin_os/vision/`:
  - `hybrid_detector.py` — YOLO (local) + OpenAI (cloud brand ID) — **recommended**
  - `openai_detector.py` — Pure GPT-4o-mini for waste + brand classification
  - `yolo_detector.py` — Local YOLO inference only, hardcoded brand
  - `mock_detector.py` — Random classifications for testing
- **Network:** `bin_os/network/api_client.py` → reports to Laravel backend. `offline_queue.py` → SQLite buffer when offline.
- **UI:** `ui/templates/display.html` + WebSocket — browser-based touchscreen UI
- **Fleet:** `fleet.py` + `fleet.yaml` — spawn multiple bins on different ports

### Key Config (`config.yaml`)
```yaml
detector: hybrid          # openai | yolo | hybrid | mock
camera: { index: 0 }     # OpenCV camera index
server: { port: 9001 }
timers: { detect: 3.0, drain: 3.5, result: 8.0 }
compartments: { solid_limit_ml: 15000, liquid_limit_ml: 3000 }
```

### API Endpoints (per bin instance)
- `GET /` — Touchscreen web UI
- `GET /api/status` — Bin status, fill levels, detector health
- `POST /api/detect` — Trigger detection (optional waste_type override)
- `POST /api/reset` — Reset compartments (simulate pickup)
- `GET /camera/stream` — MJPEG camera stream
- `WS /ws` — Real-time WebSocket for UI updates

### Fleet Config (`fleet.yaml`)
```yaml
MBR-2026-001: Port 9001, Hybrid detector, webcam
MBR-2026-002: Port 9002, YOLO detector, webcam
MBR-2026-003: Port 9003, Mock detector, headless
```

### Dependencies (`requirements.txt`)
FastAPI, Uvicorn, OpenCV, Ultralytics (YOLO), OpenAI SDK, httpx, PyYAML, Jinja2, websockets

### What's Missing / Needs Maturity
- **No desktop app** — the UI is a browser page served by FastAPI. For a Pi kiosk or demo, a proper desktop wrapper (Electron, PyWebView, or Tauri) would give fullscreen control, auto-start on boot, and native feel.
- **`hardware/__init__.py` is empty** — no GPIO, no relay control, no physical sensors. All "hardware" is simulated in `brain.py` with random physics values.
- **Camera is basic** — OpenCV `VideoCapture(0)`. No Pi Camera Module support (`picamera2`), no resolution/framerate config.
- **No service files** — no systemd unit to auto-start on Pi boot.
- **No OTA updates** — no mechanism to push new detector models or config to deployed bins.

---

## Raspberry Pi Monitor — DOES NOT EXIST YET

There is no dedicated Raspberry Pi monitoring application. The admin dashboard (`/admin/bins`) shows bin status on the web, but there's no:

- **Kiosk display app** for a monitor attached to the Pi
- **Desktop dashboard** showing real-time bin status, camera feed, detection events
- **Remote monitor** that a store owner could run on their laptop to watch their bins

### What Daniel Likely Wants
Based on context, the Pi monitor would be a **desktop app** (not a web page) that:
1. Shows real-time camera feed from the bin
2. Displays current state (idle/detecting/draining)
3. Shows fill levels and recent detections
4. Could be an Electron app, PyWebView wrapper, or Tauri app

### Backend APIs Available for a Monitor
- `GET /api/v1/bins` — List all bins with status/fill
- `GET /api/v1/bins/{bin}` — Single bin detail
- `POST /api/v1/bins/{bin}/heartbeat` — Bin heartbeat (IP, status)
- `GET /api/v1/bins/resolve/{serial}` — Resolve bin by serial number
- `POST /api/v1/detect` — Submit detection events
- `GET /api/v1/detection-events` — List detection events (admin)
- Mock bin's own `WS /ws` and `GET /camera/stream` endpoints

---

## Backend Detection Flow (for reference)

```
Mock Bin → POST /api/v1/detect → DetectionEventController::store()
                                     → DetectionService::processDetection()
                                         → calculatePoints() (brand multiplier logic)
                                         → updateBinFillLevel()
                                         → if fill ≥ 80% → auto PickupRequest
                                         → awardPoints() if user_id present
```

### Points Per Waste Type
paper_cup: 15, plastic_cup: 12, liquid_waste: 8, lid: 5, straw: 3, napkin: 2

### Brand Multiplier (Option B Hard Deterrent)
- Cup matches bin brand → base × multiplier (e.g., 1.5×)
- No brand detected → base points only
- Competitor cup at branded bin → base × multiplier × 0.3 (below base)

---

## Quick Start Commands

```bash
# Start Laravel backend
cd backend && php artisan serve

# Start single mock bin
cd mock_bin && python main.py --detector hybrid --serial MBR-2026-001

# Start fleet (3 bins)
cd mock_bin && python fleet.py

# Access mock bin UI
open http://localhost:9001

# Seed backend
cd backend
php artisan migrate:fresh --no-interaction
python3 scripts/generate_seed_data.py --dialect sqlite --output database/seed_data.sql
sqlite3 database/database.sqlite < database/seed_data.sql
```

Test accounts: `daniel@mobius.test` / `admin@mobius.test`, password: `password`

---

## How to Use This Session

Daniel will tell you what to build. Your job as the brain:
1. **Understand the request** — ask clarifying questions
2. **Design the approach** — propose architecture, get Daniel's approval
3. **Generate prompts** — if the work is large, write briefing `.md` files for specialized CC instances to execute
4. **Execute small tasks directly** — if it's scoped and quick, do it yourself
5. **Remind Daniel to commit** after every task

---

## Remaining Password Strength Work (Parked)

Daniel parked this for later. Another CC instance (the password-strength specialist) will resume. For reference, here's what's done and what remains:

**Done:**
- `<x-password-strength>` Blade component created
- `Password::defaults()` configured in AppServiceProvider
- Applied to: admin profile password tab, registration page
- `ChangePasswordRequest` and `RegisterRequest` updated
- Inline email validation added to login + register
- Tests updated with `NewPass1!`

**Remaining (per `Z_PASSWORD_STRENGTH_SPEC.md`):**
- Admin user create: `admin/users/create.blade.php`
- Admin user edit password tab: `admin/users/edit.blade.php`
- Store-owner profile: `store-owner/profile/edit.blade.php`
- Collector/public profile: `partials/profile-form.blade.php`
- Brand registration: `registration/brand.blade.php`
- Agency registration: `registration/agency.blade.php`
- `UpdateUserRequest` and `StoreUserRequest` need `Password::defaults()`
- Login lockout feature (spec in Section 6 of `Z_PASSWORD_STRENGTH_SPEC.md`)
