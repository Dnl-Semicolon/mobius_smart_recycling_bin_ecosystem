# Prompt for Login-tangent CC Instance

Read `backend/Z_MENTOR_BRIEFING_GENERAL.md` before doing anything.

## Task

Improve button and link styling on `/login` and `/register`. Both pages have buttons that feel dead and links that are hard to distinguish.

## Reference

Look at `/Users/danieltan/Downloads/Screenshot 2026-03-28 at 12.35.53 AM.png` — this is WhatsApp Web. Copy the feel of its UI.

## What to Change

### Buttons — DO NOT modify `components/button.blade.php`

Instead, override styles **inline** on the specific `<x-button>` tags in each view. Add these classes directly to the `<x-button>` element in both `auth/login.blade.php` and `auth/register.blade.php`:

```
cursor-pointer py-3 px-6 transition-all duration-150 active:scale-[0.98] shadow-sm hover:shadow-md
```

Example:
```blade
<x-button type="submit" class="w-full justify-center cursor-pointer py-3 px-6 transition-all duration-150 active:scale-[0.98] shadow-sm hover:shadow-md">
```

### Links

On both pages, the bottom "Sign in" / "Register" `<a>` tags should feel more like WhatsApp's underlined links. Replace the current link classes with:

```
text-emerald-600 hover:text-emerald-700 font-medium underline decoration-emerald-300 underline-offset-2 hover:decoration-emerald-500
```

## Scope

Only modify these two files:
- `resources/views/auth/login.blade.php`
- `resources/views/auth/register.blade.php`

**DO NOT touch any component files** (`button.blade.php`, `input.blade.php`, etc.) — changes must stay isolated to these two views.

**DO NOT touch the email fields or password fields** — they already have inline Alpine.js validation. Only modify the `<x-button>` tags and the `<a>` link tags.
