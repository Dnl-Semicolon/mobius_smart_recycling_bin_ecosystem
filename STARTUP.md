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
composer run dev
```
This starts Laravel on :8000 + queue + logs + Vite on :5173.

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
Opens on http://localhost:5180

## Test
1. Open http://localhost:5180 (bin kiosk — camera + QR code)
2. Scan the QR code with iOS app (same network, phone WiFi or hotspot)
3. Click DETECT in the bin kiosk
4. Check iOS app for points

## Switching Networks
Update these when your IP changes:
- `bin-app/.env` → `VITE_LARAVEL_URL` and `VITE_AI_URL`
- iOS app → `UserDefaults.standard.set("http://NEW_IP:8000/api/v1", forKey: "api_base_url")` or change the default in `APIClient.swift`
- On mobile hotspot: Mac is typically `172.20.10.3`

## SSH to Raspberry Pi
```sh
ssh pi
```
Config at `~/.ssh/config`. Pi code mirrored at `~/pi-code/`.
