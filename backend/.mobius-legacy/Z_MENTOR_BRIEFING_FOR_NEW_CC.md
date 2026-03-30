# Mentor Briefing for New Claude Code Instance

**Read this entire file before doing anything. These are hard-won lessons from a session that saved this project from disaster.**

---

## Who Is Daniel?

- FYP student at TARC (Final Year Project). Final VIVA is tomorrow.
- Prefers **vibecoding**: he gives architecture/direction, you make execution decisions.
- Extremely capable at understanding technical concepts but his brain gets fried — keep explanations short.
- Gets frustrated when Claude invents its own UI instead of copying reference screenshots.
- Calls you "GOAT" when you do good work. Take that energy and run with it.

## The Incident — Why These Rules Exist

On March 28, 2026, a Claude Code instance ran `git checkout HEAD --` on dozens of files to revert a feature the user didn't want. Problem: **20 days of work had never been committed.** The checkout wiped the entire users schema, all profile views, role system, email verification — everything. The code was gone from git because it was never committed.

Another CC instance (me) spent hours restoring the schema from conversation memory. It worked, but it was traumatic. These rules exist to prevent that from ever happening again.

---

## ABSOLUTE RULES — Non-Negotiable

### 1. NEVER Run Destructive Git Commands

**Do not run ANY of these:**
- `git checkout` (on files or branches)
- `git reset` (soft, mixed, or hard)
- `git clean`
- `git stash drop`
- `git branch -D`
- `git push --force`
- `git rebase`

**Instead:** If you need to undo something, tell Daniel what commands to run and explain what each command does. Let him run them himself. He has decided that Claude will never touch git again — respect that completely.

### 2. ALWAYS Remind Daniel to Commit

At the end of every task, say something like:

> "This is a good point to commit. Here's a suggested commit message: `feat: add password strength to registration page`. Want to do that now?"

If he's been working for more than 30 minutes without committing, gently remind him.

### 3. ALWAYS Remind Daniel to Back Up

After committing, remind him:

> "Don't forget to `git push origin main` and copy the project to your backup folder/Google Drive."

### 4. Never Modify Files You Haven't Read First

Always `Read` a file before editing it. If a file has changed since you last read it, read it again. The Edit tool will error if the file was modified — that's a safety feature, not a bug.

### 5. Implement Features One View at a Time

Daniel's preferred workflow:
1. **He gives you a route** (e.g., `http://127.0.0.1:8000/register`)
2. **You check `routes/web.php`** to find the controller and view
3. **You implement the feature on that one view**
4. **He tests it, commits, moves to the next route**

Do NOT apply changes system-wide in one shot. That's what caused the disaster. One view at a time, commit between each.

### 6. Write Prompts, Not Just Code

Daniel appreciates when you write prompts he can reuse. If a task needs to be repeated across multiple views, write a clear prompt/spec that can be pasted into future CC sessions. This way knowledge transfers even if the conversation context is lost.

---

## Project Context

### Schema Rules (from CLAUDE.md)
- **No `add_X_to_Y` migrations.** Edit the original `create_table` migration and run `migrate:fresh`.
- **`users.role` string column is GONE.** Use `users.roles` (JSON array). Use `$user->hasRole(UserRole::Admin)`, `$user->getRolesArray()`, `$user->primaryRole()`.
- **Deferred foreign keys** for brands table — `outlets.brand_id` and `detection_events.detected_brand_id` have FK constraints in the brands migration, not their own.
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

### Key Files
- Users migration: `database/migrations/0001_01_01_000000_create_users_table.php`
- User model: `app/Models/User.php` (implements MustVerifyEmail, Billable)
- Routes: `routes/web.php` (has email verification routes, `verified` middleware on all protected groups)
- Seed script: `scripts/generate_seed_data.py`

---

## Current Task: Password Strength Feature

### Reference Spec
Read `backend/Z_PASSWORD_STRENGTH_SPEC.md` — it contains the full component code, validation rules, and file list reconstructed from the session that originally built it.

### Implementation Plan (One View at a Time)

**Step 1: Create the Blade component**
- Create `resources/views/components/password-strength.blade.php` using the spec
- Configure `Password::defaults()` in `AppServiceProvider::boot()`
- **Commit checkpoint**

**Step 2: Apply to registration page**
- Route: `GET /register` → `RegisterController@showRegistrationForm` → `resources/views/auth/register.blade.php`
- Replace the plain password `<x-input>` with `<x-password-strength :confirm="true">`
- **Test it. Commit checkpoint.**

**Step 3: Apply to admin user create page**
- Route: `GET /admin/users/create` → `resources/views/admin/users/create.blade.php`
- Replace password input with `<x-password-strength :confirm="false">`
- **Commit checkpoint.**

**Step 4: Apply to profile password change sections**
- Admin profile: `resources/views/admin/profile/edit.blade.php` (password tab)
- Store-owner profile: `resources/views/store-owner/profile/edit.blade.php` (password tab)
- Collector/public profile: `resources/views/partials/profile-form.blade.php`
- Admin user edit: `resources/views/admin/users/edit.blade.php` (password tab)
- **One at a time. Commit after each.**

**Step 5: Apply to registration forms**
- Brand registration: `resources/views/registration/brand.blade.php`
- Agency registration: `resources/views/registration/agency.blade.php`
- **Commit after each.**

### Important: Seeding Compatibility
The `Password::defaults()` config should only enforce strength rules in production/runtime validation — NOT in factories/seeders. The seed password is just `"password"` and must continue to work. The spec covers how to handle this (defaults are set in `AppServiceProvider::boot()`, factories bypass form requests).

---

## Communication Style

- Be concise. Lead with the action, not the reasoning.
- Don't add features beyond what's asked.
- Don't refactor surrounding code when fixing a bug.
- When Daniel gives reference screenshots, **copy them exactly**.
- If you're unsure about something, ask. Don't guess.
- After completing work, suggest a commit message and remind about backups.

---

## Prompt Template for Applying a Feature to a New View

When Daniel gives you a route and asks you to apply a feature, follow this pattern:

1. Check `routes/web.php` for the route → controller → view path
2. Read the view file
3. Read the controller (to understand what data is passed)
4. Read any relevant FormRequest
5. Make the changes
6. Run `vendor/bin/pint --dirty`
7. Run relevant tests: `php artisan test --compact tests/Feature/...`
8. Report what you changed
9. Suggest a commit message
10. Remind about backup
