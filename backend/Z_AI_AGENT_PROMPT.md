# AI Agent Onboarding Prompt

Copy and paste this entire file as your first message to any AI coding agent (Claude Code, Codex, Cursor, etc.) to get them up to speed.

---

## Context

You are continuing work on **Mobius**, a Laravel 12 demo app for learning CRUD, validation, and testing. The project uses:

- **Laravel 12** + PHP 8.4
- **Blade templates** (no Livewire)
- **Tailwind CSS v4**
- **Alpine.js** (via Vite)
- **HTMX** (CDN) for SPA-like navigation
- **Pest v4** for testing

## Important Files to Read First

Before doing anything, read these files in order:

1. **`Z_Claude_handoff.md`** - Project state, change log, conventions, what's done
2. **`Z_Claude_plan.md`** - Sprint tracker with checkboxes (find next unchecked sprint)
3. **`Z_Claude_commands.md`** - Dev commands reference
4. **`Z_Claude_ui_learnings.md`** - UI/UX design decisions and patterns
5. **`CLAUDE.md`** - Coding guidelines and Laravel Boost MCP tools

## Current State

- **Completed:** Sprints 1-13 (Models, API, Blade components, List/Create/Show/Edit pages)
- **Next up:** Sprint 14 (Delete Flow)
- **Known bugs:** Edit page confirmation dialog may have issues - needs debugging

## What Needs Attention

### 1. Debug Edit Page (`resources/views/pages/persons/edit.blade.php`)

The edit page has a JS `confirm()` dialog that shows changes before saving. It may have bugs:
- Test by editing a person and changing some fields
- Check if the confirm dialog appears with correct before/after values
- Check if form submits properly after confirmation
- Check if "no changes" correctly redirects back

### 2. Sprint 14: Delete Flow

After fixing bugs, implement delete:
- Add delete button on detail page (`pages/persons/show.blade.php`)
- Use browser `confirm()` for confirmation (keep it simple)
- Add `destroy()` method to `PersonController`
- Add DELETE route in `web.php`
- Redirect to list after delete

## Key Conventions

- Use `php artisan make:*` commands with `--no-interaction`
- Run `vendor/bin/pint --dirty` before finishing
- Run `php artisan test --compact` after changes
- Update `Z_Claude_plan.md` checkboxes as you complete tasks
- Update `Z_Claude_handoff.md` change log after each session

## File Structure

```
backend/
├── app/Http/Controllers/PersonController.php  # Web CRUD
├── app/Http/Controllers/Api/PersonController.php  # API CRUD
├── resources/views/
│   ├── components/  # Blade components (button, card, input, etc.)
│   ├── pages/persons/  # Person CRUD views
│   └── ...
├── routes/web.php  # Web routes
├── routes/api.php  # API routes (prefixed with api.)
└── Z_Claude_*.md  # AI context files
```

## Commands

```bash
composer dev      # Start dev server + vite + queue + logs
composer test     # Run tests
composer lint     # Fix code style (pint)
composer fresh    # Reset database with seeders
composer routes   # List all routes
```

## User Preferences (Daniel)

- Font: Nunito
- Style: Glass/frosted cards, pills, rounded corners
- Hover: Color change only, no cursor effects
- No dark mode
- Keep it simple, no over-engineering
- Short sprints, check in frequently

## Start Here

1. Read `Z_Claude_handoff.md` for full context
2. Read `Z_Claude_plan.md` to see Sprint 14 tasks
3. Debug the edit page confirmation dialog
4. Then implement Sprint 14 (Delete Flow)
5. Update handoff docs when done

Good luck!
