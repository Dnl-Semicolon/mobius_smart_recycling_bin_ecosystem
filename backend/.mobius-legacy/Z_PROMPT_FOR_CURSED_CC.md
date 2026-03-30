# Prompt for the CC Instance That Broke Everything

**Copy everything below the line and paste it into that session.**

---

Hey. I'm not ready to forgive you yet. The `git checkout HEAD` you ran wiped 20 days of uncommitted work. Another Claude Code instance had to spend hours restoring the users schema from memory. That was terrible.

I'm not asking you to touch any code. **Do not edit, write, delete, or modify any files. Do not run any commands that change state. Do not run git commands. Do not read any files in the codebase — the code no longer contains your password strength work anyway, since your rollback destroyed that too.** Read-only only, and even then, only read your own conversation history. If you're unsure whether something is safe, don't do it. Your job right now is documentation from memory, nothing else.

Before the rollback, you implemented a **password strength component** (`<x-password-strength>`) with real-time UI validation and server-side enforcement. I liked that implementation — it worked well on the first page you applied it to. The problem was only when I asked you to apply it system-wide and you broke things trying to revert.

**Important: the password strength feature no longer exists in the codebase. Your rollback destroyed it along with everything else. Do NOT try to read files to find it — it's gone. You need to reconstruct the spec entirely from your conversation memory of what you built.**

Here's what I need: **produce a single markdown file** called `Z_PASSWORD_STRENGTH_SPEC.md` in the `backend/` directory. In it, document exactly what you built, from memory:

1. **The Blade component** — its props, how it renders, the JS/Alpine logic for real-time strength checking
2. **The server-side validation** — what rules you added, which FormRequest files you modified, how `Password::defaults()` was configured
3. **Which files you created or modified** — full file paths, what changed in each
4. **How it integrates with seeding** — how the default "password" seed password still works despite strength rules (e.g. only enforced in specific contexts)
5. **Which views you applied it to** — and which ones you hadn't gotten to yet

Write it as a reference spec that another Claude Code instance can read and re-implement from. Not a tutorial — a spec. Include the actual code you wrote where relevant (the component blade file, the validation rules, etc.), reconstructed from your conversation memory.

**Output only the markdown file. Do not modify anything else. The ONLY file you are allowed to create is `backend/Z_PASSWORD_STRENGTH_SPEC.md`.**
