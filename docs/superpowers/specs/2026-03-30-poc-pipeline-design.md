# Mobius PoC Pipeline — Design Spec

**Date:** 2026-03-30
**Goal:** End-to-end proof of concept: QR scan → AI detection → points awarded

## Context

The Mobius smart recycling bin ecosystem has all major backend pieces built (Laravel API, detection events, points system, QR session flow) but they've never been tested end-to-end. The bin-side application needs a React-based kiosk UI that accesses the webcam and talks to an AI classification service. The database needs to move from SQLite to MySQL. QR code generation is missing.

This spec covers the minimum viable pipeline to demonstrate: user scans bin QR → places cup → AI classifies it → user gets points.

## Architecture

```
┌─────────────┐     ┌────────────────────┐     ┌─────────────┐
│  iOS App    │     │   React Bin App    │     │  Laravel    │
│  (SwiftUI)  │     │  (Vite + React)    │     │  Admin Web  │
│  QR Scan    │     │  Camera + Results   │     │  Dashboard  │
└──────┬──────┘     └───┬────────────┬───┘     └──────┬──────┘
       │                │            │                │
       │           frame│            │detection       │
       │                ▼            ▼                │
       │         ┌──────────┐  ┌──────────┐           │
       └────────▶│ Python   │  │ Laravel  │◀──────────┘
                 │ AI Svc   │  │ API v1   │
                 │ FastAPI  │  │          │
                 └──────────┘  └────┬─────┘
                                    │
                              ┌─────┴─────┐
                              │   MySQL   │
                              └───────────┘
```

**Three services run simultaneously:**

| Service | Port | Tech | Role |
|---------|------|------|------|
| Laravel API + Admin Web | 8000 | PHP/Laravel 12 | API hub, sessions, points, data |
| Python AI Service | 9001 | FastAPI | Receives camera frames, returns classification |
| React Bin App | 5173 | Vite + React + TS | Bin kiosk UI in browser (webcam + results) |

## E2E Flow

1. React bin app displays a QR code on screen (the bin's serial number, e.g. `MBR-2026-001`)
2. User opens iOS app → camera scans QR → extracts serial
3. iOS app calls `POST /api/v1/customer/scan` with `{bin_serial}` → 60-second session cached
4. User places cup in front of webcam
5. React app captures frame via `getUserMedia()` → `POST http://localhost:9001/classify` (multipart image)
6. Python AI returns `{waste_type: "paper_cup", confidence: 87, brand: "starbucks"}`
7. React app reports to Laravel → `POST /api/v1/detect` with `{bin_id, waste_type, confidence, detected_brand}`
8. Laravel controller auto-fills `user_id` from session cache (`Cache::pull("bin_session:{bin_id}")`)
9. `DetectionEventObserver` fires → `DetectionService::processDetection()` → points awarded
10. User refreshes iOS app → sees updated points balance

## Phase 0: MySQL Setup

**What:** Replace SQLite with MySQL for the Laravel backend.

**Steps:**
1. Install MySQL via Homebrew: `brew install mysql`
2. Start service: `brew services start mysql`
3. Secure installation: `mysql_secure_installation`
4. Create database: `CREATE DATABASE mobius;`
5. Update `backend/.env`:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=mobius
   DB_USERNAME=root
   DB_PASSWORD=<chosen_password>
   ```
6. Run migrations: `php artisan migrate:fresh --seed`
7. Verify: `php artisan serve` → check admin dashboard + API endpoints

**Verification:** Hit `GET /api/v1/public/stats` and confirm data returns.

## Phase 1: QR Code Generation

**What:** Laravel endpoint that generates QR code images for bins.

**Backend changes:**
- Add package: `composer require simplesoftwareio/simple-qrcode`
- New endpoint: `GET /api/v1/bins/{bin}/qr`
  - Returns SVG image (content-type: `image/svg+xml`)
  - QR encodes the bin's `serial_number` string
  - Optional query param `?size=300` for pixel dimensions
- No auth required (bins need to fetch their own QR)

**Files to modify:**
- `routes/api.php` — add route
- New controller method or add to `BinController`

**Verification:** `curl http://localhost:8000/api/v1/bins/1/qr` returns a valid SVG that decodes to `MBR-2026-001`.

## Phase 2: Python AI Microservice

**What:** A thin FastAPI service that accepts an image and returns a waste classification.

**Reuses:** Existing detector classes from `mock_bin/bin_os/vision/` (MockDetector, YOLODetector, OpenAIDetector, HybridDetector).

**Location:** New `ai_service/` directory at repo root. Imports detector classes from `mock_bin/bin_os/vision/` to avoid code duplication.

**Single endpoint:**
```
POST /classify
Content-Type: multipart/form-data
Body: image file

Response:
{
  "waste_type": "paper_cup",
  "confidence": 87,
  "brand": "starbucks",
  "detector": "mock"
}
```

**Detector modes** (set via env var or CLI flag):
- `mock` — random results, no dependencies (for pipeline testing)
- `yolo` — your trained Roboflow model
- `openai` — ChatGPT Vision API (needs OPENAI_API_KEY)
- `hybrid` — YOLO for waste type + OpenAI for brand

**CORS:** Enabled for `http://localhost:5173` (React dev server)

**Startup:** `python -m ai_service.main --detector mock --port 9001`

**Verification:** `curl -X POST -F "image=@test.jpg" http://localhost:9001/classify` returns valid JSON.

## Phase 3: React Bin App

**What:** Vite + React + TypeScript SPA that acts as the bin's kiosk interface.

**New directory:** `bin-app/` at repo root

```
bin-app/
├── src/
│   ├── App.tsx              — layout: camera left, info right
│   ├── components/
│   │   ├── CameraFeed.tsx   — getUserMedia webcam stream
│   │   ├── QRDisplay.tsx    — renders bin QR code (fetched from Laravel)
│   │   ├── DetectButton.tsx — captures frame, sends to AI
│   │   └── ResultCard.tsx   — shows waste type, confidence, brand, points
│   ├── services/
│   │   └── api.ts           — fetch wrappers for Python AI + Laravel
│   ├── types.ts             — TypeScript interfaces
│   └── main.tsx
├── .env                     — VITE_LARAVEL_URL, VITE_AI_URL, VITE_BIN_SERIAL
├── package.json
├── tsconfig.json
└── vite.config.ts
```

**UI (bare minimum, black & white, light mode):**
- Full-screen layout, no navigation
- Left panel: live webcam feed + "Detect" button
- Right panel: QR code + last detection result + bin status
- No fancy styling — just borders, monospace text, functional

**Camera:** Browser `navigator.mediaDevices.getUserMedia({video: true})`
- Renders to a `<video>` element
- On "Detect" click: draw frame to hidden `<canvas>`, export as blob, POST to Python AI

**Bin ID resolution:** On startup, React app calls `GET /api/v1/bins/resolve/{serial}` (existing endpoint) to get the numeric `bin_id`. This is cached and used for all subsequent detection reports.

**Config via `.env`:**
```
VITE_LARAVEL_URL=http://localhost:8000/api/v1
VITE_AI_URL=http://localhost:9001
VITE_BIN_SERIAL=MBR-2026-001
```

Change these when switching networks. That's the MVP solution for the IP problem.

**Verification:** Open `http://localhost:5173`, see camera + QR code, click Detect, see result.

## Phase 4: E2E Integration Test

**What:** Run all services together, test the full pipeline.

**Startup checklist:**
```bash
# Terminal 1: MySQL (already running via brew services)
# Terminal 2: Laravel
cd backend && php artisan serve --host=0.0.0.0 --port=8000

# Terminal 3: Python AI
cd ai_service && python -m ai_service.main --detector mock --port 9001

# Terminal 4: React Bin App
cd bin-app && npm run dev
```

**Test script:**
1. Open React app at `http://localhost:5173` — verify camera + QR visible
2. On iOS device (same WiFi): open Mobius app → scan the QR on screen
3. Verify: iOS app shows "Session started. Deposit your item now!"
4. In React app: click "Detect"
5. Verify: detection result appears (waste type + confidence)
6. Verify: Laravel has new detection_event with user_id set
7. Verify: user's points_balance increased
8. Verify: iOS app history shows the detection

**Known constraint:** iOS app's API URL must point to the Mac's local IP. Add a debug settings screen in the iOS app (simple text field to enter the base URL).

## Phase 5: AI Pipeline (Future Session)

**What:** Replace mock detector with real AI.

- Import your Roboflow-trained YOLO model (`.pt` file) into `ai_service/models/`
- Configure `--detector yolo --model-path models/your-model.pt`
- Set `OPENAI_API_KEY` in `.env` for brand detection fallback
- Test `hybrid` mode: YOLO classifies waste type, OpenAI identifies brand from logo crop
- Retrain with 20 new cups via Roboflow → export → replace model file

## Network Strategy

| Environment | Laravel | Python AI | React App | iOS App API URL |
|-------------|---------|-----------|-----------|-----------------|
| Mac dev (all local) | localhost:8000 | localhost:9001 | localhost:5173 | 172.20.10.x:8000 |
| Mac + Pi | Mac IP:8000 | Pi IP:9001 | Pi IP:5173 | Mac IP:8000 |

MVP: `.env` files on each service. Change the IP when you switch networks.
Future: mDNS/zeroconf auto-discovery.

## Repo Structure After

```
mobius_smart_recycling_bin_ecosystem/
├── backend/         ← Laravel 12 (now MySQL)
├── mobile/          ← SwiftUI iOS app
├── mock_bin/        ← existing Python bin code (detectors reused by ai_service)
├── bin-app/         ← NEW: Vite + React kiosk app
├── ai_service/      ← NEW: thin FastAPI classifier
└── docs/            ← specs, plans
```

## Out of Scope

- Production deployment / cloud hosting
- Raspberry Pi kiosk mode setup (Chromium autostart, etc.)
- Model training (covered separately)
- Admin dashboard changes
- mDNS auto-discovery (future)
- Store owner features
