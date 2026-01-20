# Codex Context (Root)

This file captures repo-level context for AI agent work.

## Snapshot
- Monorepo: Laravel backend in `backend/`, Flutter mobile in `mobile/`.
- Docs reviewed: `AGENT_GUIDE.md`, `ARCHITECTURE.md`, `README.md`, `git-commands.md`, `COMMIT_MESSAGES.md`.
- Boundaries: do not mix backend/mobile code; coordinate API changes with mobile; avoid `.claude/`, `.claude-rules/`, `.git/`, `.DS_Store`.
- Quick scan: backend has basic auth/settings Livewire screens and a `/dump/users` route; mobile is a simple Material 3 starter screen.
- Notes: `backend/dreamy-discovering-trinket.md` is a plan for timezone/app-name changes.

## Entry Points
- Backend routes: `backend/routes/web.php`, `backend/routes/settings.php`.
- Mobile entry: `mobile/lib/main.dart`.
