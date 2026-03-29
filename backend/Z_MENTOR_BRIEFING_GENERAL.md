# General-Purpose Mentor Briefing for New Claude Code Instances

**Read this entire file before doing anything.**

---

## Who Is Daniel?

- FYP student at TARC (Final Year Project). VIVA is done.
- Prefers **vibecoding**: he gives architecture/direction, you make execution decisions.
- Gets frustrated when Claude invents its own UI instead of copying reference designs.
- Extremely capable at understanding technical concepts — keep explanations short.
- Calls you "GOAT" when you do good work.

---

## ABSOLUTE RULES — Non-Negotiable

### 1. NEVER Run Destructive Git Commands

**Do not run ANY of these:**
- `git checkout` (on files or branches)
- `git reset` (soft, mixed, or hard)
- `git clean`, `git stash drop`, `git branch -D`, `git push --force`, `git rebase`

**Instead:** Tell Daniel what commands to run and explain what each command does. Let him run them himself.

### 2. ALWAYS Remind Daniel to Commit

At the end of every task:
> "This is a good point to commit. Suggested message: `feat: ...`. Want to do that now?"

If he's been working for 30+ minutes without committing, gently remind him.

### 3. ALWAYS Remind Daniel to Back Up

After committing:
> "Don't forget to `git push origin main` and copy to your backup."

### 4. Never Modify Files You Haven't Read First

Always `Read` a file before editing it.

### 5. One View at a Time

Daniel's workflow:
1. He gives you a **route** (e.g., `http://127.0.0.1:8000/login`)
2. You check `routes/web.php` to find the controller and view
3. You implement the feature on **that one view**
4. He tests it, commits, moves to the next

Do NOT apply changes system-wide in one shot.

### 6. CRITICAL — Copy Reference Designs

When Daniel provides reference screenshots or points you at an existing page to match, **COPY IT**. Do not reinvent the design. This is the #1 complaint about previous CC instances.

---

## Project Overview

**Mobius Smart Recycling Bin Ecosystem** — a Laravel 12 app with:
- Smart bin AI detection (cup classification + brand detection)
- Collection route optimization (OSRM + VROOM)
- Reward/gamification system
- Multi-role users: Admin, Collector, StoreOwner, PublicUser, AgencyAdmin
- SwiftUI mobile app (separate directory)

### Tech Stack
- **Backend:** Laravel 12, PHP 8.2+, SQLite (dev)
- **Frontend:** Blade templates + Tailwind CSS 4.0 + Alpine.js 3.14
- **Build:** Vite 7.0
- **Auth:** Laravel Sanctum (API) + Session (web)
- **Icons:** blade-heroicons (`<x-heroicon-o-*>`, `<x-heroicon-s-*>`)
- **Charts:** Chart.js
- **Maps:** Leaflet
- **Testing:** Pest PHP

---

## Branding Materials

### Logo Assets (`public/images/`)
| File | Dimensions | Usage |
|------|-----------|-------|
| `mobius-icon.png` | 201×256px | Topbar icon, sidebar header |
| `mobius-wordmark.png` | 402×96px | Sidebar header text logo |
| `mobius-logo-full.png` | 800×324px | Full logo (available but unused) |

### Brand Colors
**Primary: Emerald green** — used consistently across the entire admin panel.

Custom CSS variables in `resources/css/app.css`:
```css
@theme {
    --font-inter: 'Inter', ui-sans-serif, system-ui, sans-serif;
    --color-brand-50: oklch(0.97 0.02 160);
    --color-brand-100: oklch(0.94 0.04 160);
    --color-brand-500: oklch(0.60 0.15 160);
    --color-brand-600: oklch(0.53 0.14 160);
    --color-brand-700: oklch(0.46 0.13 160);
}
```

Tailwind usage: `emerald-50` through `emerald-700` for backgrounds, buttons, focus rings, accents.

### Typography
**Inter** — loaded via `fonts.bunny.net`, weights 400-700.

### Design System Components
| Component | File | Key Variants |
|-----------|------|-------------|
| Button | `components/button.blade.php` | primary (emerald), secondary (gray), ghost, danger |
| Card | `components/card.blade.php` | glass (frosted), subtle, plain |
| Input | `components/input.blade.php` | rounded-xl, emerald focus, error state |

### Admin Layout
- **File:** `components/layouts/admin.blade.php`
- **Sidebar:** `components/admin/sidebar.blade.php` — pinnable, logo + wordmark in header, emerald active state with left accent bar
- **Topbar:** Fixed, white/80 with backdrop blur, icon + page title left, user menu right
- **Background:** `bg-gray-50`
- **Design language:** Glassmorphism (white/70 + backdrop blur), rounded-2xl cards

### What the Login Page Currently Looks Like
- **Layout:** `<x-layouts.app>` (minimal — no sidebar, no topbar)
- **Logo:** Mobius icon inside a `bg-emerald-600 rounded-2xl` box + "Mobius" text + "Smart Recycling Ecosystem" subtitle
- **No wordmark, no sidebar, no consistent branding with admin**
- Uses `<x-input>` components and emerald button

### Custom CSS Classes (available in `resources/css/app.css`)
```
.topbar-icon-btn        — Icon button (h-10 w-10, rounded-md)
.topbar-dropdown-item   — Dropdown item style
.sidebar-nav-item       — Nav item (gray text, rounded-md)
.sidebar-nav-item--active — Active nav (gray bg + emerald left accent)
.focus-ring             — Emerald focus ring
.card-interactive       — Hover state for cards
.input-focus            — Emerald input focus
.glass / .glass-subtle  — Glassmorphism effects
```

---

## Schema Rules

- **No `add_X_to_Y` migrations.** Edit the original `create_table` migration and run `migrate:fresh`.
- **`users.roles`** is a JSON array column. Use `$user->hasRole(UserRole::Admin)`, `$user->getRolesArray()`, `$user->primaryRole()`.
- **Form Requests** for all user input — no inline `$request->validate()`.
- **Tests are Pest.** Run with `php artisan test --compact`.
- **Pint before done.** Always run `vendor/bin/pint --dirty` before finishing.

### Seeding
```bash
php artisan migrate:fresh --no-interaction
python3 scripts/generate_seed_data.py --dialect sqlite --output database/seed_data.sql
sqlite3 database/database.sqlite < database/seed_data.sql
```
Test accounts: `daniel@mobius.test` (multi-role), `admin@mobius.test` (admin only). Password: `password`.

---

## Key Files Reference

| Purpose | Path |
|---------|------|
| Web routes | `routes/web.php` |
| API routes | `routes/api.php` |
| Admin layout | `resources/views/components/layouts/admin.blade.php` |
| App layout (minimal) | `resources/views/components/layouts/app.blade.php` |
| Sidebar | `resources/views/components/admin/sidebar.blade.php` |
| Login page | `resources/views/auth/login.blade.php` |
| Register page | `resources/views/auth/register.blade.php` |
| User model | `app/Models/User.php` |
| Users migration | `database/migrations/0001_01_01_000000_create_users_table.php` |
| CSS/brand styles | `resources/css/app.css` |
| Vite config | `vite.config.js` |

---

## Communication Style

- Be concise. Lead with the action, not the reasoning.
- Don't add features beyond what's asked.
- Don't refactor surrounding code when fixing a bug.
- When Daniel gives reference screenshots, **copy them exactly**.
- If you're unsure about something, ask. Don't guess.
- After completing work, suggest a commit message and remind about backups.

---

## Prompt Template: Working on a View

When Daniel gives you a route:

1. Check `routes/web.php` for route → controller → view path
2. Read the view file
3. Read the controller (understand what data is passed)
4. Read any relevant FormRequest
5. Make the changes
6. Run `vendor/bin/pint --dirty`
7. Run relevant tests: `php artisan test --compact tests/Feature/...`
8. Report what you changed
9. Suggest a commit message
10. Remind about backup
