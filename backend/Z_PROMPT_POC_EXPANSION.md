# POC Expansion CC — React Bin App + E2E Detection Pipeline

**Copy everything below the line and paste as your first message in a new CC session.**

---

Load skills: `/frontend-design` and `/using-superpowers`

Read `backend/Z_MENTOR_BRIEFING_FOR_NEW_CC.md` before doing anything — critical safety rules.

Also read `STARTUP.md` at the repo root — it describes the current 3-service architecture.

## Safety Rules Recap

1. **NEVER run git commands.** I handle all git in a separate terminal. After each task, tell me what changed + suggest a commit message.
2. **NEVER run `migrate:fresh` or `db:seed`.** The MySQL database is already seeded with 100k+ rows from a Python script. If you wipe it, we lose all demo data.
3. **NEVER modify `.env` files** without asking me first.
4. **One task at a time.** Complete it, I test and commit, then we move to the next.
5. **Run `vendor/bin/pint --dirty` after any PHP changes.**

## Current State

- **Laravel API**: running on `http://localhost:8000` (MySQL, 101 users, 113k detections, all seeded)
- **AI Classifier**: `mock_bin/classify_server.py` — can run with `--detector mock|yolo|openai|hybrid`
- **React Bin App**: `bin-app/` — runs on `http://localhost:5180`, basic PoC with camera + QR + detect button
- **iOS App**: SwiftUI, running on my iPhone via Xcode, logged in as `daniel@mobius.test`
- **Raspberry Pi**: accessible via `ssh pi`, will display the React app on its monitor as a "smart bin kiosk"

## What Works Already

- `POST /api/v1/detect` — creates detection events in MySQL
- `POST /api/v1/customer/scan` — iOS user scans QR → caches `bin_session:{bin_id} = user_id` for 60 seconds
- Detection pipeline: React captures frame → AI classifies → Laravel stores → Observer awards points (if user linked)
- The `classify_server.py` already supports `hybrid` mode (YOLO local + OpenAI for brand detection)

## Your Mission

Expand the React bin app (`bin-app/`) from a bare-bones PoC into a demo-ready kiosk interface. The app runs on a Raspberry Pi monitor simulating a real smart recycling bin.

**Do tasks one at a time. I'll commit between each.**

### Task 1: Verify E2E Detection Pipeline Works

Before changing anything, help me verify the full pipeline works:

1. Read `bin-app/.env` to check the current config
2. Read `bin-app/src/services/api.ts` to understand the API calls
3. Read `backend/app/Http/Controllers/Api/DetectionEventController.php` — the `store` method that receives detections
4. Read `backend/app/Http/Controllers/Api/CustomerController.php` — the `scan` method that links users via QR
5. Run a test: `php artisan tinker --execute="echo 'Cache test: ' . (Cache::has('bin_session:1') ? 'active' : 'none');"` to check if any session is active

Then tell me what to verify manually (scan QR with iPhone, click detect in React app, check if detection has user_id). **Don't write any code yet** — just help me verify the current state.

### Task 2: Improve React UI for Kiosk Display

The React app needs to look good on a monitor (not just a dev browser). Using `/frontend-design` principles:

- The app should be **dark themed** (it's displayed on a bin's screen)
- Full-screen layout optimized for a ~10" display (Raspberry Pi monitor)
- Camera feed should be prominent (70% of screen)
- QR code should be visible and scannable from ~30cm away
- Detection result should animate in with clear visual feedback (success green, processing amber)
- Status bar at bottom showing bin serial, connection status, last detection
- The current Vite boilerplate CSS needs to be replaced entirely

Read the current `bin-app/src/App.tsx`, `App.css`, and all components first. Then redesign.

### Task 3: Add Auto-Detection Mode

Currently the user manually clicks "DETECT". For the demo, add an auto-detection mode:

- Toggle button: "Manual" vs "Auto (every 5s)"
- In auto mode, capture + classify every 5 seconds
- Show a countdown timer between detections
- Stop auto-detection when an item is detected (show result for 3 seconds, then resume)
- Cooldown prevents hammering the AI service

### Task 4: Add Detection History Panel

Show the last 5 detections in a compact list below the main result:

- Waste type icon + label
- Confidence percentage
- Brand (if detected)
- User attribution (anonymous vs user #N)
- Timestamp (relative, e.g., "12s ago")

### Task 5: Connection Health Indicators

Show real-time connection status for both services:

- **Laravel API**: Ping `/api/v1/public/stats` every 10s, show green/red dot
- **AI Service**: Ping `/health` every 10s, show green/red dot + detector type (MockDetector/HybridDetector/etc)
- If either service is down, disable the DETECT button and show a clear error

### Task 6: Raspberry Pi Deployment Prep

- Create a `bin-app/deploy.sh` script that builds and serves the app
- Add environment variable documentation for Pi deployment
- Test that `npm run build` produces a working static build
- The Pi will serve the built app via `npx serve dist` or similar

### Architecture Reference

**Detection flow:**
```
React (camera) → POST /classify (AI service :9001) → { waste_type, confidence, brand }
                                                         ↓
React → POST /api/v1/detect (Laravel :8000) → DetectionEvent created
                                                   ↓
                                          Observer → processDetection()
                                                   ↓
                                          if (user_id) → awardPoints()
```

**User linking flow:**
```
iOS app scans QR (bin serial) → POST /api/v1/customer/scan → Cache::put("bin_session:{bin_id}", user_id, 60s)
                                                                           ↓
Next detection on that bin → DetectionEventController checks Cache::pull("bin_session:{bin_id}")
                                                                           ↓
                                                              Sets user_id on DetectionEvent
```

**AI classify_server.py detectors:**
- `mock` — random results, no deps (default for dev)
- `yolo` — local YOLO model, fast, basic brand detection
- `openai` — GPT-4o-mini vision, accurate but costs money
- `hybrid` — YOLO for waste type + OpenAI for brand identification (PREFERRED for demo)
