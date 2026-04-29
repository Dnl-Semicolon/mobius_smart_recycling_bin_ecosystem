# Mobius Smart Recycling Bin Ecosystem

A smart recycling bin system that classifies disposed beverage waste with
computer vision, identifies the cup brand on the same captured image, credits
the disposing user with points whose value depends on the brand match between
the cup and the bin, and dispatches collectors along ordered routes generated
against a traffic-aware routing service.

## Active components

| Folder | Stack | Role |
| --- | --- | --- |
| `backend-v2/` | Laravel 13, PHP 8.3, Inertia 3, React 19, MySQL 8 | Backend API, dashboards (admin, store and brand owner), Progressive Web App for the public end user and the collector. The two-stage detection pipeline, the brand multiplier points calculation rule, the route module integration with Google's traffic-aware routing service, and the multi-role authentication flow all live here. |
| `bin-client/` | React 19, TypeScript, Vite 8, Tailwind | The client application that runs on the screen mounted at the physical recycling bin. Captures the image of the disposed item and posts it to the Laravel API. |

Each active folder has its own `README.md` with setup, environment variables,
build, and run instructions.

## Legacy components (not maintained)

| Folder | Status |
| --- | --- |
| `backend/` | Earlier Laravel prototype superseded by `backend-v2/`. Kept for reference; do not run. |
| `mobile/` | Native Swift iOS prototype, abandoned in favour of the Progressive Web App in `backend-v2/`. Kept for reference; not part of the submitted system. |

## Setup at a glance

```sh
# Backend
cd backend-v2
composer install
cp .env.example .env
# edit .env to fill in API keys (Stripe, Twilio, OpenAI, Roboflow, Google, Mailtrap, MySQL)
php artisan key:generate
brew services start mysql
mysql -u root -e "CREATE DATABASE mobius_v3;"
php artisan migrate --force
npm install
npm run build
php artisan serve --host 0.0.0.0 --port 8000

# Bin client (run in a separate terminal, on the bin screen or on a build machine)
cd ../bin-client
npm install
cp .env.example .env
# edit .env so that VITE_API_URL points at the Laravel server on the local Wi-Fi
npm run dev   # for live development
npm run build # for the production bundle to deploy onto the bin screen
```

The Laravel server, the dashboards, the Progressive Web App, and the bin client
all communicate over HTTP on the local Wi-Fi network.

## Trained model artefact

The custom-trained RF-DETR detector for the waste type stage is delivered as a
checkpoint file (`weights.pt`) alongside this submission. The checkpoint is
also hosted through Roboflow for serverless inference; the model identifier is
`mobius-v2/1` and is read from `ROBOFLOW_MODEL_ID` in the backend `.env`.
