# Apply Password Strength to Reset Password Page

**Copy everything below the line and paste into the password-strength CC session.**

---

Another CC instance just added a "Forgot Password" feature. It works, but it used plain password inputs instead of your `<x-password-strength>` component. I've already committed the changes. Your job is to swap in the password strength component.

**Do not touch anything else. Only the files listed below. Read each file before editing.**

## What was just added (already committed)

1. `app/Http/Controllers/Auth/ForgotPasswordController.php` — sends reset link email (no password fields, leave alone)
2. `app/Http/Controllers/Auth/ResetPasswordController.php` — handles the reset. Uses plain `'password' => ['required', 'string', 'min:8', 'confirmed']` validation. **This needs `Password::defaults()` instead.**
3. `resources/views/auth/forgot-password.blade.php` — email-only form (no password fields, leave alone)
4. `resources/views/auth/reset-password.blade.php` — **has two plain password inputs that need replacing with `<x-password-strength>`**
5. `routes/web.php` — 4 new routes under guest middleware (leave alone)
6. `resources/views/auth/login.blade.php` — "Forgot password?" link added (leave alone)

## Task 1: Replace plain inputs in reset-password view

**File:** `resources/views/auth/reset-password.blade.php`

Find the two `x-data="{ show: false }"` password field blocks (new password + confirm). Replace them both with your `<x-password-strength>` component:

```blade
<x-password-strength label="New Password" :confirm="true" confirmLabel="Confirm New Password" />
```

This replaces approximately 30 lines of manual password + confirm inputs with your single component.

## Task 2: Update ResetPasswordController validation

**File:** `app/Http/Controllers/Auth/ResetPasswordController.php`

Change the password validation rule from:
```php
'password' => ['required', 'string', 'min:8', 'confirmed'],
```

To use `Password::defaults()` (import `Illuminate\Validation\Rules\Password`):
```php
'password' => ['required', 'string', Password::defaults(), 'confirmed'],
```

This enforces the same strength rules you configured in `AppServiceProvider::boot()`.

## Task 3: Verify

1. Go to `http://localhost:8000/login` → click "Forgot password?"
2. Enter `daniel@mobius.test` → check Mailtrap for the email → click the reset link
3. The reset form should now show your password strength component (real-time criteria indicators)
4. Try a weak password like "12345678" — should be rejected
5. Try a strong password — should work
6. Run `vendor/bin/pint --dirty`
7. Run `php artisan test --compact --filter=Authentication`

**After you're done, tell me the commit message and remind me to push + backup.**
