# Codex Context (Backend)

This file captures backend-specific context for AI agent work.

## Stack
- Laravel 12 on PHP 8.4.8.
- Fortify auth, Livewire v3, Flux UI Free v2, Tailwind v4, Pest v4.

## Key Docs
- `backend/CLAUDE.md`, `backend/AGENTS.md`, `backend/GEMINI.md` (Laravel Boost guidelines).
- `backend/AGENT_GUIDE.md`, `backend/ARCHITECTURE.md`, `backend/README.md`.

## Structure / Entry Points
- Routes: `routes/web.php`, `routes/settings.php`.
- Livewire settings components: `app/Livewire/Settings/`.
- User model: `app/Models/User.php`.
- Views: `resources/views/`.

## Conventions (from docs)
- Use `php artisan make:` with `--no-interaction` for new files.
- Prefer Form Requests and Eloquent relationships; avoid `DB::`.
- Run targeted Pest tests with `php artisan test --compact`.

## Current Behavior
- `/` shows welcome view; `/dashboard` is auth+verified.
- `/dump/users` returns `User::all()`.
