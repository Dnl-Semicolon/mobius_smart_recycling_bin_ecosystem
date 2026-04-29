# Mobius Smart Recycling Bin - Backend (backend-v2)

The Laravel monolith server for the Mobius Smart Recycling Bin Ecosystem.
Hosts the JSON API consumed by the bin client, and serves the React 19 frontend
through Inertia for the administrator dashboard, the store and brand owner
dashboards, and the Progressive Web App shell used by the public end user and
the collector.

## Stack

- PHP 8.3
- Laravel framework 13
- Inertia 3 (Laravel and React adaptors)
- React 19, TypeScript, Vite 8
- MySQL 8 (local, via Homebrew)
- Tooling: Composer, npm, Pint (code style), Pail (log tail), Sanctum, Fortify, Cashier (Stripe)

## Third-party services

| Service | Purpose | Where to get keys |
| --- | --- | --- |
| Roboflow | Hosted serving of the custom-trained RF-DETR waste type detector | https://app.roboflow.com |
| OpenAI (gpt-4o) | Cup brand classification on the same captured image | https://platform.openai.com/api-keys |
| Google Maps + Directions | Distance, duration, polyline geometry for the route module | https://console.cloud.google.com |
| Stripe (Cashier) | Subscription billing for store and brand owners (test mode) | https://dashboard.stripe.com/test/apikeys |
| Twilio Verify | One-time password verification for public user account creation | https://www.twilio.com/console |
| Mailtrap | Sandbox SMTP that captures outgoing email during development | https://mailtrap.io |

## First-time setup

```sh
# 1. PHP dependencies
composer install

# 2. Environment file
cp .env.example .env
# then edit .env and fill in the keys (see the table above)

# 3. Application key
php artisan key:generate

# 4. Database (MySQL via Homebrew)
brew services start mysql
mysql -u root -e "CREATE DATABASE mobius_v3;"
php artisan migrate --force

# 5. Frontend dependencies and build
npm install
npm run build
```

The `--force` flag on `php artisan migrate` is required because Laravel refuses
to run migrations against a non-development environment without an interactive
confirmation prompt, and the setup runs the command non-interactively.

## Running locally

```sh
php artisan serve --host 0.0.0.0 --port 8000
```

Binding the development server to `0.0.0.0` lets other devices on the same Wi-Fi
network reach the API by visiting the host machine's local IP address (for
example `http://192.168.1.50:8000`). This is the address the bin client and any
phone running the Progressive Web App should point at.

For the developer experience with hot-reloading frontend, queue listener, and
log tail running in parallel:

```sh
composer run dev
```

This runs the Laravel server, the queue listener, the log tail, and Vite in dev
mode under a single process supervisor.

## Common tasks

| Task | Command |
| --- | --- |
| Run unit and feature tests | `php artisan test` |
| Apply pending migrations | `php artisan migrate` |
| Roll back the last batch of migrations | `php artisan migrate:rollback` |
| Inspect logs | `php artisan pail` |
| Format PHP source | `composer run lint` |
| Lint TypeScript and React | `npm run lint` |
| Type-check the frontend | `npm run types:check` |

## Project layout (high level)

| Path | What lives there |
| --- | --- |
| `app/Models` | Eloquent models that map MySQL tables to PHP objects |
| `app/Services` | Service classes for detection, points calculation, route, and brand integrations |
| `app/Enums` | Closed enumerations (waste types, user roles, route status, ...) |
| `app/Http/Controllers` | API and Inertia controllers |
| `database/migrations` | Schema definitions applied by `php artisan migrate` |
| `resources/js` | React 19 sources for the dashboards and the Progressive Web App shell |
| `resources/views` | Inertia entry view |
| `routes/api.php`, `routes/web.php`, `routes/settings.php` | Route registrations |

## Database notes

- The connection used in development is `DB_CONNECTION=mysql` against
  `127.0.0.1:3306`, database `mobius_v3`, user `root`, no password.
- Database contents can be inspected through phpMyAdmin (any local install) or
  through the `php artisan tinker` REPL.
- Schema access from PHP is through Eloquent, the framework's object relational
  mapper. Each table is a model class in `app/Models`, and records are queried
  and persisted as PHP objects rather than as raw SQL strings.

