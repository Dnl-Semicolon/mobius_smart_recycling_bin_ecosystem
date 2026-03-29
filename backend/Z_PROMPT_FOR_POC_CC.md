# Response for the PoC Pipeline CC Instance

**Copy everything below the line and paste it into that session.**

---

**Inline execution**, one task at a time. No subagents, no parallel work.

Before we start, here's what's already done and what you MUST follow:

## Already Complete — Skip These

**Task 1 (MySQL) is fully done.** Another CC instance handled it. MySQL is running via Homebrew, the database `mobius` exists, `.env` is updated, migrations ran successfully, seed data is imported (101 users, all detection events, transactions, etc). `composer run dev` is running. **Do not touch the database setup.**

## Safety Rules — Non-Negotiable

1. **NEVER run git commands.** No `git checkout`, `git reset`, `git stash`, `git clean`, nothing. A previous CC instance ran `git checkout HEAD --` and wiped 20 days of uncommitted work. I handle all git myself in a separate terminal. After each task, just tell me:
   - What files changed
   - A suggested commit message
   - A reminder to push to GitHub

2. **One task at a time.** Complete Task 2 fully, I test and commit, then we move to Task 3. Do not start the next task until I confirm the previous one is committed.

3. **Do not modify files you haven't read.** Always `Read` a file before editing it.

4. **Do not run `migrate:fresh` or `migrate:fresh --seed`.** The database is already seeded with dense data from a Python script. If you wipe it, we lose 100k+ rows of realistic demo data. If schema changes are needed, tell me the exact artisan command and I'll run it.

5. **Do not modify `.env`.** It's already configured for MySQL. If you need env changes, show me what to change and I'll do it.

6. **Run `vendor/bin/pint --dirty` before declaring any PHP task done.**

## Seeding Context

The seed data does NOT come from Laravel's `DatabaseSeeder`. It comes from:
```bash
python3 scripts/generate_seed_data.py --dialect mysql --output database/seed_data.sql
mysql -u root mobius < database/seed_data.sql
```
If you ever need to re-seed, tell me those exact commands. Do NOT run `php artisan db:seed` or `migrate:fresh --seed`.

## Start

Begin with **Task 2: QR Code Laravel Endpoint**. Follow the plan exactly as written in `docs/superpowers/plans/2026-03-30-poc-pipeline.md`.
