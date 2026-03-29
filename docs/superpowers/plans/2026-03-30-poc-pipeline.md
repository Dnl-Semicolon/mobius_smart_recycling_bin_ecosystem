# PoC Pipeline Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Wire the full QR scan → AI detection → points pipeline end-to-end so it can be demo'd on a Mac.

**Architecture:** Three services — Laravel API (port 8000, MySQL), Python AI classifier (port 9001, FastAPI), React bin kiosk app (port 5173, Vite). iOS app scans QR from the React app's screen, Python AI classifies webcam frames, Laravel stores detections and awards points.

**Tech Stack:** Laravel 12 / MySQL / PHP 8.x, FastAPI / Python 3.11+, Vite + React 19 + TypeScript, SwiftUI (existing)

**Spec:** `docs/superpowers/specs/2026-03-30-poc-pipeline-design.md`

---

## File Structure

### New Files
```
mock_bin/classify_server.py          — FastAPI AI classifier (1 file, imports existing detectors)

bin-app/                              — React bin kiosk app (Vite + React + TS)
├── index.html
├── package.json
├── vite.config.ts
├── tsconfig.json
├── tsconfig.app.json
├── .env
├── .env.example
├── .gitignore
└── src/
    ├── main.tsx
    ├── App.tsx
    ├── App.css
    ├── types.ts
    ├── services/
    │   └── api.ts
    └── components/
        ├── CameraFeed.tsx
        ├── QRDisplay.tsx
        └── DetectionPanel.tsx
```

### Modified Files
```
backend/.env                          — DB_CONNECTION=mysql, credentials
backend/config/cors.php               — ensure localhost:5173 allowed
backend/routes/api.php                — add QR code route
backend/app/Http/Controllers/Api/BinController.php — add qrCode() method
backend/composer.json                 — simplesoftwareio/simple-qrcode (via composer require)

mobile/Mobius/Sources/Services/APIClient.swift — UserDefaults-based URL override
```

### Test Files
```
backend/tests/Feature/Api/QrCodeTest.php — QR endpoint test
```

---

## Task 1: MySQL Setup

> **Note:** This task is infrastructure — run commands in your terminal, not automated.

**Files:**
- Modify: `backend/.env`

- [ ] **Step 1: Install MySQL via Homebrew**

```bash
brew install mysql
```

Expected: MySQL 8.x or 9.x installed.

- [ ] **Step 2: Start MySQL service**

```bash
brew services start mysql
```

Expected: `==> Successfully started mysql`

- [ ] **Step 3: Create the database**

```bash
mysql -u root -e "CREATE DATABASE mobius CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Expected: No output (success). If you get an error about root password, run `mysql -u root` first — Homebrew MySQL has no root password by default.

- [ ] **Step 4: Update backend/.env**

Change these lines in `backend/.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mobius
DB_USERNAME=root
DB_PASSWORD=
```

Leave `DB_PASSWORD` empty — Homebrew MySQL default.

- [ ] **Step 5: Run migrations with seed data**

```bash
cd /Users/danieltan/mobius_smart_recycling_bin_ecosystem/backend
php artisan migrate:fresh --seed
```

Expected: All migrations run, seeders populate data. Look for `Seeding: Database\Seeders\...` output.

- [ ] **Step 6: Verify Laravel works with MySQL**

```bash
cd /Users/danieltan/mobius_smart_recycling_bin_ecosystem/backend
php artisan serve --host=0.0.0.0 --port=8000
```

In another terminal:

```bash
curl -s http://localhost:8000/api/v1/public/stats | head -c 200
```

Expected: JSON response with stats data (not an error).

- [ ] **Step 7: Verify a seeded bin exists**

```bash
curl -s http://localhost:8000/api/v1/bins/resolve/MBR-2026-001
```

Expected: JSON with `{"data":{"id":1,"serial_number":"MBR-2026-001",...}}`. If 404, check which serials were seeded:

```bash
cd /Users/danieltan/mobius_smart_recycling_bin_ecosystem/backend
php artisan tinker --execute="echo \App\Models\Bin::first()?->serial_number;"
```

Note the serial — you'll use it in the React app's `.env`.

- [ ] **Step 8: Commit**

```bash
cd /Users/danieltan/mobius_smart_recycling_bin_ecosystem/backend
git add .env
git commit -m "chore: switch from SQLite to MySQL"
```

**Important:** Do NOT commit `.env` if it contains real secrets. For a local dev setup with empty root password, it's acceptable.

---

## Task 2: QR Code Laravel Endpoint

**Files:**
- Modify: `backend/routes/api.php`
- Modify: `backend/app/Http/Controllers/Api/BinController.php`
- Create: `backend/tests/Feature/Api/QrCodeTest.php`

- [ ] **Step 1: Write the failing test**

Create `backend/tests/Feature/Api/QrCodeTest.php`:

```php
<?php

use App\Models\Bin;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('qr code endpoint returns SVG for valid bin', function () {
    $bin = Bin::factory()->create(['serial_number' => 'MBR-TEST-001']);

    $response = $this->getJson("/api/v1/bins/{$bin->id}/qr");

    $response->assertOk();
    $response->assertHeader('content-type', 'image/svg+xml');
    expect($response->getContent())->toContain('<svg');
    expect($response->getContent())->toContain('MBR-TEST-001');
});

test('qr code endpoint returns 404 for missing bin', function () {
    $response = $this->getJson('/api/v1/bins/99999/qr');

    $response->assertNotFound();
});
```

- [ ] **Step 2: Run test to verify it fails**

```bash
cd /Users/danieltan/mobius_smart_recycling_bin_ecosystem/backend
php artisan test --filter=QrCodeTest
```

Expected: FAIL — route not defined.

- [ ] **Step 3: Install QR code package**

```bash
cd /Users/danieltan/mobius_smart_recycling_bin_ecosystem/backend
composer require simplesoftwareio/simple-qrcode
```

Expected: Package installed. If Laravel 12 compatibility issues, use `chillerlan/php-qrcode` instead:

```bash
composer require chillerlan/php-qrcode
```

- [ ] **Step 4: Add the route**

In `backend/routes/api.php`, add this line in the public (no auth) section, after the `bins/{bin}/heartbeat` route:

```php
Route::get('bins/{bin}/qr', [BinController::class, 'qrCode'])->name('bins.qr');
```

- [ ] **Step 5: Add the controller method**

In `backend/app/Http/Controllers/Api/BinController.php`, add this method:

**If using `simplesoftwareio/simple-qrcode`:**

```php
use SimpleSoftwareIO\QrCode\Facades\QrCode;

public function qrCode(Bin $bin): \Illuminate\Http\Response
{
    $size = request()->integer('size', 300);
    $size = min(max($size, 100), 1000);

    $svg = QrCode::format('svg')
        ->size($size)
        ->errorCorrection('M')
        ->generate($bin->serial_number);

    return response($svg, 200, [
        'Content-Type' => 'image/svg+xml',
        'Cache-Control' => 'public, max-age=86400',
    ]);
}
```

**If using `chillerlan/php-qrcode` (fallback):**

```php
use chillerlan\QRCode\{QRCode as QRCodeLib, QROptions};

public function qrCode(Bin $bin): \Illuminate\Http\Response
{
    $options = new QROptions([
        'outputType' => QROptions::OUTPUT_MARKUP_SVG,
        'svgViewBoxSize' => request()->integer('size', 300),
        'addQuietzone' => true,
        'eccLevel' => QROptions::ECC_M,
    ]);

    $svg = (new QRCodeLib($options))->render($bin->serial_number);

    return response($svg, 200, [
        'Content-Type' => 'image/svg+xml',
        'Cache-Control' => 'public, max-age=86400',
    ]);
}
```

- [ ] **Step 6: Run tests**

```bash
cd /Users/danieltan/mobius_smart_recycling_bin_ecosystem/backend
php artisan test --filter=QrCodeTest
```

Expected: 2 tests pass.

- [ ] **Step 7: Manual verification**

With Laravel running (`php artisan serve --host=0.0.0.0 --port=8000`):

```bash
curl -o /tmp/test-qr.svg http://localhost:8000/api/v1/bins/1/qr
open /tmp/test-qr.svg
```

Expected: SVG opens in browser, shows a scannable QR code. Scan it with your phone — should decode to the bin's serial number (e.g. `MBR-2026-001`).

- [ ] **Step 8: Commit**

```bash
cd /Users/danieltan/mobius_smart_recycling_bin_ecosystem/backend
git add app/Http/Controllers/Api/BinController.php routes/api.php tests/Feature/Api/QrCodeTest.php composer.json composer.lock
git commit -m "feat: add QR code generation endpoint for bins"
```

---

## Task 3: Python AI Classify Server

**Files:**
- Create: `mock_bin/classify_server.py`

- [ ] **Step 1: Create the classify server**

Create `mock_bin/classify_server.py`:

```python
"""
Thin FastAPI server that exposes waste classification via HTTP.
Reuses existing detector classes from bin_os/vision/.

Usage:
    cd mock_bin
    python classify_server.py                    # mock detector (no deps)
    python classify_server.py --detector yolo    # YOLO model
    python classify_server.py --detector openai  # OpenAI Vision
    python classify_server.py --detector hybrid  # YOLO + OpenAI
"""

import argparse
import asyncio
import sys
from pathlib import Path

import cv2
import numpy as np
import uvicorn
from fastapi import FastAPI, File, UploadFile
from fastapi.middleware.cors import CORSMiddleware

from bin_os.vision.base_detector import BaseDetector, ClassificationResult
from bin_os.vision.mock_detector import MockDetector


def load_detector(detector_type: str, model_path: str | None = None) -> BaseDetector:
    """Load the requested detector. Imports are deferred to avoid pulling
    heavy deps (torch, openai) when using the mock detector."""
    if detector_type == "mock":
        return MockDetector()
    elif detector_type == "yolo":
        from bin_os.vision.yolo_detector import YOLODetector
        return YOLODetector(model_path=model_path or "models/best.pt")
    elif detector_type == "openai":
        from bin_os.vision.openai_detector import OpenAIDetector
        return OpenAIDetector()
    elif detector_type == "hybrid":
        from bin_os.vision.hybrid_detector import HybridDetector
        return HybridDetector(model_path=model_path or "models/best.pt")
    else:
        print(f"Unknown detector: {detector_type}. Using mock.")
        return MockDetector()


def create_app(detector: BaseDetector) -> FastAPI:
    app = FastAPI(title="Mobius AI Classifier", version="0.1.0")

    app.add_middleware(
        CORSMiddleware,
        allow_origins=["*"],
        allow_methods=["*"],
        allow_headers=["*"],
    )

    @app.get("/health")
    async def health():
        ok = await detector.health_check()
        return {"status": "ok" if ok else "unhealthy", "detector": type(detector).__name__}

    @app.post("/classify")
    async def classify(image: UploadFile = File(...)):
        contents = await image.read()
        nparr = np.frombuffer(contents, np.uint8)
        frame = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

        if frame is None:
            return {"error": "Could not decode image"}, 400

        result: ClassificationResult = await detector.classify(frame)
        return {
            "waste_type": result.waste_type,
            "confidence": result.confidence,
            "brand": result.brand,
            "detector": type(detector).__name__,
        }

    return app


def main():
    parser = argparse.ArgumentParser(description="Mobius AI Classify Server")
    parser.add_argument("--detector", default="mock", choices=["mock", "yolo", "openai", "hybrid"])
    parser.add_argument("--model-path", default=None, help="Path to YOLO .pt model file")
    parser.add_argument("--port", type=int, default=9001)
    parser.add_argument("--host", default="0.0.0.0")
    args = parser.parse_args()

    detector = load_detector(args.detector, args.model_path)
    app = create_app(detector)

    print(f"Starting classify server on {args.host}:{args.port} with {type(detector).__name__}")
    uvicorn.run(app, host=args.host, port=args.port)


if __name__ == "__main__":
    main()
```

- [ ] **Step 2: Test the server starts**

```bash
cd /Users/danieltan/mobius_smart_recycling_bin_ecosystem/mock_bin
pip install fastapi uvicorn opencv-python-headless numpy
python classify_server.py --detector mock --port 9001
```

Expected: `Starting classify server on 0.0.0.0:9001 with MockDetector`

- [ ] **Step 3: Test the health endpoint**

In another terminal:

```bash
curl http://localhost:9001/health
```

Expected: `{"status":"ok","detector":"MockDetector"}`

- [ ] **Step 4: Test classification with a sample image**

```bash
# Create a tiny test image
python3 -c "
import cv2, numpy as np
img = np.zeros((100,100,3), dtype=np.uint8)
cv2.imwrite('/tmp/test_frame.jpg', img)
print('Test image created')
"

curl -X POST -F "image=@/tmp/test_frame.jpg" http://localhost:9001/classify
```

Expected: JSON response like `{"waste_type":"paper_cup","confidence":85,"brand":"starbucks","detector":"MockDetector"}` (random values from MockDetector).

- [ ] **Step 5: Commit**

```bash
cd /Users/danieltan/mobius_smart_recycling_bin_ecosystem
git add mock_bin/classify_server.py
git commit -m "feat: add standalone AI classify server for bin app"
```

---

## Task 4: React Bin App — Scaffold + Camera

**Files:**
- Create: entire `bin-app/` directory

- [ ] **Step 1: Scaffold the Vite + React project**

```bash
cd /Users/danieltan/mobius_smart_recycling_bin_ecosystem
npm create vite@latest bin-app -- --template react-ts
cd bin-app
npm install
npm install react-qr-code
```

Expected: Project created, dependencies installed.

- [ ] **Step 2: Create .env and .env.example**

Create `bin-app/.env`:

```
VITE_LARAVEL_URL=http://localhost:8000/api/v1
VITE_AI_URL=http://localhost:9001
VITE_BIN_SERIAL=MBR-2026-001
```

Create `bin-app/.env.example`:

```
VITE_LARAVEL_URL=http://localhost:8000/api/v1
VITE_AI_URL=http://localhost:9001
VITE_BIN_SERIAL=MBR-2026-001
```

- [ ] **Step 3: Create types**

Create `bin-app/src/types.ts`:

```typescript
export interface ClassifyResult {
  waste_type: string;
  confidence: number;
  brand: string;
  detector: string;
}

export interface DetectionResponse {
  data: {
    id: number;
    bin_id: number;
    user_id: number | null;
    waste_type: string;
    confidence: number;
    detected_at: string;
  };
  message: string;
}

export interface BinResolveResponse {
  data: {
    id: number;
    serial_number: string;
    name: string;
  };
}
```

- [ ] **Step 4: Create the API service**

Create `bin-app/src/services/api.ts`:

```typescript
import type { ClassifyResult, DetectionResponse, BinResolveResponse } from "../types";

const LARAVEL_URL = import.meta.env.VITE_LARAVEL_URL;
const AI_URL = import.meta.env.VITE_AI_URL;

export async function resolveBin(serial: string): Promise<BinResolveResponse> {
  const res = await fetch(`${LARAVEL_URL}/bins/resolve/${serial}`);
  if (!res.ok) throw new Error(`Bin not found: ${serial}`);
  return res.json();
}

export async function classifyImage(imageBlob: Blob): Promise<ClassifyResult> {
  const formData = new FormData();
  formData.append("image", imageBlob, "frame.jpg");

  const res = await fetch(`${AI_URL}/classify`, {
    method: "POST",
    body: formData,
  });
  if (!res.ok) throw new Error(`Classification failed: ${res.status}`);
  return res.json();
}

export async function reportDetection(
  binId: number,
  result: ClassifyResult
): Promise<DetectionResponse> {
  const res = await fetch(`${LARAVEL_URL}/detect`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      bin_id: binId,
      waste_type: result.waste_type,
      confidence: result.confidence,
      detected_brand: result.brand || undefined,
    }),
  });
  if (!res.ok) throw new Error(`Report failed: ${res.status}`);
  return res.json();
}
```

- [ ] **Step 5: Create CameraFeed component**

Create `bin-app/src/components/CameraFeed.tsx`:

```tsx
import { useEffect, useRef, forwardRef, useImperativeHandle, useState } from "react";

export interface CameraFeedHandle {
  captureFrame: () => Promise<Blob | null>;
}

export const CameraFeed = forwardRef<CameraFeedHandle>(function CameraFeed(_, ref) {
  const videoRef = useRef<HTMLVideoElement>(null);
  const [error, setError] = useState<string>("");

  useEffect(() => {
    let stream: MediaStream | null = null;

    navigator.mediaDevices
      .getUserMedia({ video: { facingMode: "environment", width: 640, height: 480 } })
      .then((s) => {
        stream = s;
        if (videoRef.current) {
          videoRef.current.srcObject = s;
        }
      })
      .catch((err) => {
        setError(`Camera error: ${err.message}`);
      });

    return () => {
      stream?.getTracks().forEach((t) => t.stop());
    };
  }, []);

  useImperativeHandle(ref, () => ({
    captureFrame: async () => {
      const video = videoRef.current;
      if (!video || video.readyState < 2) return null;

      const canvas = document.createElement("canvas");
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      canvas.getContext("2d")!.drawImage(video, 0, 0);

      return new Promise<Blob | null>((resolve) => {
        canvas.toBlob(resolve, "image/jpeg", 0.85);
      });
    },
  }));

  if (error) {
    return <div style={{ padding: 20, color: "red", border: "1px solid red" }}>{error}</div>;
  }

  return (
    <video
      ref={videoRef}
      autoPlay
      playsInline
      muted
      style={{ width: "100%", maxHeight: 480, background: "#000", display: "block" }}
    />
  );
});
```

- [ ] **Step 6: Verify camera works**

Replace `bin-app/src/App.tsx` temporarily:

```tsx
import { useRef } from "react";
import { CameraFeed, type CameraFeedHandle } from "./components/CameraFeed";

function App() {
  const cameraRef = useRef<CameraFeedHandle>(null);

  return (
    <div style={{ padding: 20 }}>
      <h1>Camera Test</h1>
      <CameraFeed ref={cameraRef} />
      <button onClick={async () => {
        const blob = await cameraRef.current?.captureFrame();
        alert(blob ? `Captured ${blob.size} bytes` : "No frame");
      }}>
        Test Capture
      </button>
    </div>
  );
}

export default App;
```

```bash
cd /Users/danieltan/mobius_smart_recycling_bin_ecosystem/bin-app
npm run dev
```

Open `http://localhost:5173` in browser. Allow camera access. Click "Test Capture" — should alert with byte count.

- [ ] **Step 7: Commit scaffold + camera**

```bash
cd /Users/danieltan/mobius_smart_recycling_bin_ecosystem
git add bin-app/
git commit -m "feat: scaffold React bin app with camera feed component"
```

---

## Task 5: React Bin App — QR Display + Detection Panel

**Files:**
- Create: `bin-app/src/components/QRDisplay.tsx`
- Create: `bin-app/src/components/DetectionPanel.tsx`
- Create: `bin-app/src/App.css`
- Modify: `bin-app/src/App.tsx`

- [ ] **Step 1: Create QRDisplay component**

Create `bin-app/src/components/QRDisplay.tsx`:

```tsx
import QRCode from "react-qr-code";

interface Props {
  serial: string;
}

export function QRDisplay({ serial }: Props) {
  return (
    <div style={{ textAlign: "center" }}>
      <div style={{ background: "white", padding: 16, display: "inline-block" }}>
        <QRCode value={serial} size={200} level="M" />
      </div>
      <p style={{ fontFamily: "monospace", marginTop: 8, fontSize: 14 }}>
        {serial}
      </p>
      <p style={{ fontSize: 12, color: "#666" }}>Scan with Mobius app</p>
    </div>
  );
}
```

- [ ] **Step 2: Create DetectionPanel component**

Create `bin-app/src/components/DetectionPanel.tsx`:

```tsx
import type { ClassifyResult } from "../types";

interface Props {
  result: ClassifyResult | null;
  userId: number | null;
  detecting: boolean;
  onDetect: () => void;
  error: string;
}

export function DetectionPanel({ result, userId, detecting, onDetect, error }: Props) {
  return (
    <div>
      <button
        onClick={onDetect}
        disabled={detecting}
        style={{
          width: "100%",
          padding: "16px 32px",
          fontSize: 18,
          fontWeight: "bold",
          cursor: detecting ? "wait" : "pointer",
          background: detecting ? "#ccc" : "#000",
          color: "#fff",
          border: "none",
          marginBottom: 16,
        }}
      >
        {detecting ? "Detecting..." : "DETECT"}
      </button>

      {error && (
        <div style={{ padding: 12, background: "#fee", border: "1px solid red", marginBottom: 12 }}>
          {error}
        </div>
      )}

      {result && (
        <table style={{ width: "100%", borderCollapse: "collapse", fontFamily: "monospace" }}>
          <tbody>
            <Row label="Waste Type" value={result.waste_type} />
            <Row label="Confidence" value={`${result.confidence}%`} />
            <Row label="Brand" value={result.brand || "—"} />
            <Row label="Detector" value={result.detector} />
            <Row label="User" value={userId ? `#${userId} (points awarded)` : "anonymous"} />
          </tbody>
        </table>
      )}
    </div>
  );
}

function Row({ label, value }: { label: string; value: string }) {
  return (
    <tr style={{ borderBottom: "1px solid #ddd" }}>
      <td style={{ padding: "8px 12px", fontWeight: "bold", color: "#666" }}>{label}</td>
      <td style={{ padding: "8px 12px" }}>{value}</td>
    </tr>
  );
}
```

- [ ] **Step 3: Create App.css**

Create `bin-app/src/App.css`:

```css
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: system-ui, -apple-system, sans-serif;
  background: #fff;
  color: #000;
}

.app {
  display: grid;
  grid-template-columns: 1fr 360px;
  height: 100vh;
  gap: 0;
}

.camera-section {
  display: flex;
  flex-direction: column;
  background: #000;
}

.camera-section video {
  flex: 1;
  object-fit: contain;
}

.sidebar {
  padding: 24px;
  border-left: 2px solid #000;
  display: flex;
  flex-direction: column;
  gap: 24px;
  overflow-y: auto;
}

.sidebar h1 {
  font-size: 20px;
  font-weight: 700;
  letter-spacing: -0.5px;
}

.status-bar {
  padding: 8px 16px;
  font-family: monospace;
  font-size: 12px;
  border-top: 1px solid #333;
  color: #0f0;
  background: #111;
}

.status-bar.error {
  color: #f00;
}

hr {
  border: none;
  border-top: 1px solid #ddd;
}
```

- [ ] **Step 4: Wire up the full App.tsx**

Replace `bin-app/src/App.tsx`:

```tsx
import { useRef, useState, useEffect } from "react";
import { CameraFeed, type CameraFeedHandle } from "./components/CameraFeed";
import { QRDisplay } from "./components/QRDisplay";
import { DetectionPanel } from "./components/DetectionPanel";
import { resolveBin, classifyImage, reportDetection } from "./services/api";
import type { ClassifyResult } from "./types";
import "./App.css";

const BIN_SERIAL = import.meta.env.VITE_BIN_SERIAL || "MBR-2026-001";

function App() {
  const cameraRef = useRef<CameraFeedHandle>(null);

  const [binId, setBinId] = useState<number | null>(null);
  const [status, setStatus] = useState("Resolving bin...");
  const [statusError, setStatusError] = useState(false);

  const [detecting, setDetecting] = useState(false);
  const [result, setResult] = useState<ClassifyResult | null>(null);
  const [userId, setUserId] = useState<number | null>(null);
  const [error, setError] = useState("");

  // Resolve bin serial → bin_id on mount
  useEffect(() => {
    resolveBin(BIN_SERIAL)
      .then((res) => {
        setBinId(res.data.id);
        setStatus(`Bin #${res.data.id} — ${res.data.serial_number}`);
        setStatusError(false);
      })
      .catch((err) => {
        setStatus(`Failed to resolve bin: ${err.message}`);
        setStatusError(true);
      });
  }, []);

  async function handleDetect() {
    if (!binId) {
      setError("Bin not resolved yet");
      return;
    }

    setDetecting(true);
    setError("");
    setResult(null);
    setUserId(null);

    try {
      // 1. Capture frame from camera
      const blob = await cameraRef.current?.captureFrame();
      if (!blob) {
        setError("Failed to capture frame");
        return;
      }
      setStatus("Classifying...");

      // 2. Send to AI service for classification
      const classResult = await classifyImage(blob);
      setResult(classResult);
      setStatus("Reporting to backend...");

      // 3. Report detection to Laravel
      const detection = await reportDetection(binId, classResult);
      setUserId(detection.data.user_id);

      // Points are awarded server-side. The bin app shows user attribution;
      // the user checks exact points in their iOS app.
      setStatus(`Detection #${detection.data.id} recorded`);
      setStatusError(false);
    } catch (err) {
      setError(err instanceof Error ? err.message : "Detection failed");
      setStatus("Error — see details");
      setStatusError(true);
    } finally {
      setDetecting(false);
    }
  }

  return (
    <div className="app">
      <div className="camera-section">
        <CameraFeed ref={cameraRef} />
        <div className={`status-bar ${statusError ? "error" : ""}`}>{status}</div>
      </div>

      <div className="sidebar">
        <h1>MOBIUS BIN</h1>
        <QRDisplay serial={BIN_SERIAL} />
        <hr />
        <DetectionPanel
          result={result}
          userId={userId}
          detecting={detecting}
          onDetect={handleDetect}
          error={error}
        />
      </div>
    </div>
  );
}

export default App;
```

- [ ] **Step 5: Clean up default Vite files**

Delete the default Vite boilerplate that we don't need:

```bash
cd /Users/danieltan/mobius_smart_recycling_bin_ecosystem/bin-app
rm -f src/assets/react.svg public/vite.svg
```

Update `bin-app/index.html` title:

```html
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Mobius Bin</title>
  </head>
  <body>
    <div id="root"></div>
    <script type="module" src="/src/main.tsx"></script>
  </body>
</html>
```

Update `bin-app/src/main.tsx`:

```tsx
import { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import App from "./App";

createRoot(document.getElementById("root")!).render(
  <StrictMode>
    <App />
  </StrictMode>
);
```

- [ ] **Step 6: Verify the full React app**

Start all three services:

```bash
# Terminal 1: Laravel (should already be running from Task 1)
cd /Users/danieltan/mobius_smart_recycling_bin_ecosystem/backend
php artisan serve --host=0.0.0.0 --port=8000

# Terminal 2: Python AI
cd /Users/danieltan/mobius_smart_recycling_bin_ecosystem/mock_bin
python classify_server.py --detector mock --port 9001

# Terminal 3: React app
cd /Users/danieltan/mobius_smart_recycling_bin_ecosystem/bin-app
npm run dev
```

Open `http://localhost:5173`. Verify:
- Camera feed appears (allow permission)
- QR code shows with bin serial
- Status bar says `Bin #1 — MBR-2026-001` (or similar)
- Click DETECT → status shows "Classifying..." then "Reporting..." then "Detection #N recorded"
- Result table shows waste_type, confidence, brand
- User shows "anonymous" (no QR scan happened)

- [ ] **Step 7: Commit**

```bash
cd /Users/danieltan/mobius_smart_recycling_bin_ecosystem
git add bin-app/
git commit -m "feat: complete React bin kiosk app with camera, QR, and detection"
```

---

## Task 6: Laravel CORS for React Dev Server

**Files:**
- Modify: `backend/config/cors.php` (if needed)

- [ ] **Step 1: Check current CORS config**

```bash
cd /Users/danieltan/mobius_smart_recycling_bin_ecosystem/backend
cat config/cors.php
```

Look at the `allowed_origins` array. If it contains `['*']`, skip to Step 3 — CORS is already open.

- [ ] **Step 2: Update CORS if needed**

If `allowed_origins` is NOT `['*']`, add the React dev server:

```php
'allowed_origins' => ['http://localhost:5173', 'http://localhost:8000'],
```

Also ensure:

```php
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
```

- [ ] **Step 3: Verify CORS works**

With Laravel running, test from the browser console at `http://localhost:5173`:

```javascript
fetch('http://localhost:8000/api/v1/public/stats')
  .then(r => r.json())
  .then(d => console.log('CORS OK:', d))
  .catch(e => console.error('CORS FAIL:', e));
```

Expected: `CORS OK: {...}` in console. If you see a CORS error, revisit Step 2.

- [ ] **Step 4: Commit (if changes were made)**

```bash
cd /Users/danieltan/mobius_smart_recycling_bin_ecosystem/backend
git add config/cors.php
git commit -m "fix: allow React dev server origin in CORS config"
```

---

## Task 7: Mobile Debug API URL Setting

**Files:**
- Modify: `mobile/Mobius/Sources/Services/APIClient.swift`

- [ ] **Step 1: Add UserDefaults-based URL override**

In `mobile/Mobius/Sources/Services/APIClient.swift`, change the `baseURL` property from:

```swift
#if DEBUG
static let baseURL = "http://172.20.10.3:8000/api/v1"
#else
static let baseURL = "https://api.mobius.my/api/v1"
#endif
```

To:

```swift
static var baseURL: String {
    #if DEBUG
    if let override = UserDefaults.standard.string(forKey: "api_base_url"), !override.isEmpty {
        return override
    }
    return "http://172.20.10.3:8000/api/v1"
    #else
    return "https://api.mobius.my/api/v1"
    #endif
}
```

This lets you change the URL at runtime from the debug console or a settings screen, without rebuilding.

- [ ] **Step 2: Regenerate Xcode project**

```bash
cd /Users/danieltan/mobius_smart_recycling_bin_ecosystem/mobile
xcodegen generate
```

- [ ] **Step 3: Commit**

```bash
cd /Users/danieltan/mobius_smart_recycling_bin_ecosystem
git add mobile/Mobius/Sources/Services/APIClient.swift
git commit -m "feat: add debug API URL override via UserDefaults"
```

---

## Task 8: E2E Integration Test

> This task verifies the full pipeline works. No code to write — just startup and testing.

- [ ] **Step 1: Start all services**

Open 3 terminal tabs:

**Tab 1 — Laravel:**
```bash
cd /Users/danieltan/mobius_smart_recycling_bin_ecosystem/backend
php artisan serve --host=0.0.0.0 --port=8000
```

**Tab 2 — Python AI (mock detector):**
```bash
cd /Users/danieltan/mobius_smart_recycling_bin_ecosystem/mock_bin
python classify_server.py --detector mock --port 9001
```

**Tab 3 — React bin app:**
```bash
cd /Users/danieltan/mobius_smart_recycling_bin_ecosystem/bin-app
npm run dev
```

- [ ] **Step 2: Verify React app loads**

Open `http://localhost:5173` in Chrome/Safari.

Check:
- [ ] Camera feed visible (grant permission)
- [ ] QR code visible with serial number
- [ ] Status bar shows resolved bin

- [ ] **Step 3: Test anonymous detection (no QR scan)**

Click DETECT in the React app.

Check:
- [ ] Status cycles through: Classifying → Reporting → Detection #N recorded
- [ ] Result table shows waste_type, confidence, brand, detector=MockDetector
- [ ] User shows "anonymous"

Verify in Laravel:

```bash
cd /Users/danieltan/mobius_smart_recycling_bin_ecosystem/backend
php artisan tinker --execute="echo \App\Models\DetectionEvent::latest()->first()->toJson(JSON_PRETTY_PRINT);"
```

Expected: Latest detection has `"user_id": null`.

- [ ] **Step 4: Test with QR scan (user attribution)**

On your iOS device (connected to same WiFi as your Mac):

1. Find your Mac's local IP: `ifconfig | grep "inet " | grep -v 127.0.0.1`
2. In the Mobius iOS app, set the API URL to `http://<YOUR_MAC_IP>:8000/api/v1`
   - From Xcode debug console: `UserDefaults.standard.set("http://<IP>:8000/api/v1", forKey: "api_base_url")`
   - Or change the hardcoded URL in `APIClient.swift` and rebuild
3. Log in to the iOS app
4. Go to the Scan tab → scan the QR code displayed on the React app
5. Expected: iOS app shows "Session started. Deposit your item now!"

Within 60 seconds, click DETECT in the React app.

Check:
- [ ] Detection result shows User as `#<user_id>` (NOT anonymous)
- [ ] In Laravel, the detection_event has the correct user_id

Verify points in the database:

```bash
cd /Users/danieltan/mobius_smart_recycling_bin_ecosystem/backend
php artisan tinker --execute="echo \App\Models\DetectionEvent::latest()->first()->toJson(JSON_PRETTY_PRINT);"
```

Expected: Latest detection has `"user_id": <non-null>`.

Also verify the user's points increased:

```bash
php artisan tinker --execute="\$d = \App\Models\DetectionEvent::latest()->first(); echo \App\Models\User::find(\$d->user_id)?->points_balance;"
```

Expected: A positive number.

- [ ] **Step 5: Document the startup procedure**

Create `STARTUP.md` at repo root:

```bash
cd /Users/danieltan/mobius_smart_recycling_bin_ecosystem
```

Create the file with these contents:

```markdown
# Mobius PoC — Quick Start

## Prerequisites
- MySQL running (`brew services start mysql`)
- Node.js 18+
- Python 3.11+ with pip
- PHP 8.x with Laravel

## Start Services (3 terminals)

### Terminal 1: Laravel API
```sh
cd backend
php artisan serve --host=0.0.0.0 --port=8000
```

### Terminal 2: AI Classifier
```sh
cd mock_bin
python classify_server.py --detector mock --port 9001
```

### Terminal 3: React Bin App
```sh
cd bin-app
npm run dev
```

## Test
1. Open http://localhost:5173 (bin kiosk)
2. Scan the QR code with iOS app (same WiFi)
3. Click DETECT in the bin kiosk
4. Check iOS app for points

## Switching Networks
Update `.env` files with new IP:
- `bin-app/.env` → VITE_LARAVEL_URL
- iOS app → UserDefaults `api_base_url` key
```

- [ ] **Step 6: Commit**

```bash
cd /Users/danieltan/mobius_smart_recycling_bin_ecosystem
git add STARTUP.md
git commit -m "docs: add PoC startup guide"
```

---

## Summary

| Task | What | Commit Message |
|------|------|----------------|
| 1 | MySQL setup | `chore: switch from SQLite to MySQL` |
| 2 | QR code endpoint | `feat: add QR code generation endpoint for bins` |
| 3 | Python AI server | `feat: add standalone AI classify server for bin app` |
| 4 | React scaffold + camera | `feat: scaffold React bin app with camera feed component` |
| 5 | React QR + detection | `feat: complete React bin kiosk app with camera, QR, and detection` |
| 6 | Laravel CORS | `fix: allow React dev server origin in CORS config` |
| 7 | Mobile debug URL | `feat: add debug API URL override via UserDefaults` |
| 8 | E2E test + docs | `docs: add PoC startup guide` |

After completing all 8 tasks, the full pipeline works: QR scan → AI detection → points awarded.
