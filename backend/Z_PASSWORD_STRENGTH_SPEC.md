# Password Strength Feature — Reconstruction Spec

> **Context:** This feature was implemented by a Claude Code instance and then destroyed
> when that same instance ran `git checkout HEAD` on files with 20 days of uncommitted work.
> This spec is reconstructed entirely from conversation memory so another instance can
> re-implement it. The code no longer exists in the codebase.

---

## 1. Blade Component: `<x-password-strength>`

**File:** `resources/views/components/password-strength.blade.php` (new file, deleted during rollback)

### Props

| Prop | Type | Default | Purpose |
|------|------|---------|---------|
| `name` | string | `'password'` | Form field name attribute |
| `label` | string | `'Password'` | Label text above the input |
| `confirm` | bool | `false` | Whether to render a confirmation field below |
| `confirmLabel` | string | `'Confirm Password'` | Label for the confirmation field |
| `required` | bool | `true` | HTML required attribute |

### Full component code (from memory)

```blade
@props([
    'name' => 'password',
    'label' => 'Password',
    'confirm' => false,
    'confirmLabel' => 'Confirm Password',
    'required' => true,
])

<div
    x-data="{
        show: false,
        showConfirm: false,
        password: '',
        get hasMinLength() { return this.password.length >= 8 },
        get hasUppercase() { return /[A-Z]/.test(this.password) },
        get hasLowercase() { return /[a-z]/.test(this.password) },
        get hasNumber() { return /[0-9]/.test(this.password) },
        get hasSymbol() { return /[^A-Za-z0-9]/.test(this.password) },
        get criteria() {
            return [
                { label: 'At least 8 characters', passed: this.hasMinLength },
                { label: 'One uppercase letter', passed: this.hasUppercase },
                { label: 'One lowercase letter', passed: this.hasLowercase },
                { label: 'One number', passed: this.hasNumber },
                { label: 'One symbol (!@#$...)', passed: this.hasSymbol },
            ]
        },
        get passedCount() { return this.criteria.filter(c => c.passed).length },
        get allPassed() { return this.passedCount === 5 },
        get strengthLabel() {
            if (this.password.length === 0) return '';
            if (this.passedCount <= 2) return 'Weak';
            if (this.passedCount <= 4) return 'Fair';
            return 'Strong';
        },
        get strengthColor() {
            if (this.passedCount <= 2) return 'red';
            if (this.passedCount <= 4) return 'amber';
            return 'emerald';
        },
    }"
    class="space-y-4"
>
    {{-- Password field --}}
    <div class="space-y-1.5">
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-600">{{ $label }}</label>
        <div class="relative">
            <input
                :type="show ? 'text' : 'password'"
                name="{{ $name }}"
                id="{{ $name }}"
                x-model="password"
                @if($required) required @endif
                class="w-full rounded-xl border border-gray-200/80 bg-white/60 px-4 pr-10 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10"
            >
            <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors cursor-pointer">
                <x-heroicon-o-eye x-show="!show" class="w-4 h-4" />
                <x-heroicon-o-eye-slash x-show="show" x-cloak class="w-4 h-4" />
            </button>
        </div>

        {{-- Static hint when empty --}}
        <p x-show="password.length === 0" class="text-xs text-gray-400">Must include uppercase, lowercase, number, and symbol.</p>

        {{-- Criteria checklist --}}
        <div x-show="password.length > 0" x-cloak class="space-y-1">
            <template x-for="item in criteria" :key="item.label">
                <div class="flex items-center gap-1.5">
                    <svg x-show="item.passed" class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    <svg x-show="!item.passed" x-cloak class="w-3.5 h-3.5 text-gray-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span class="text-xs transition-colors" :class="item.passed ? 'text-emerald-600' : 'text-gray-400'" x-text="item.label"></span>
                </div>
            </template>
        </div>

        {{-- Strength bar --}}
        <div x-show="password.length > 0" x-cloak class="flex items-center gap-2.5">
            <div class="flex gap-1 flex-1">
                <template x-for="i in 5" :key="i">
                    <div class="h-1 flex-1 rounded-full transition-colors duration-200"
                         :class="i <= passedCount
                             ? (strengthColor === 'red' ? 'bg-red-400' : strengthColor === 'amber' ? 'bg-amber-400' : 'bg-emerald-500')
                             : 'bg-gray-200'">
                    </div>
                </template>
            </div>
            <span class="text-xs font-medium shrink-0 transition-colors"
                  :class="strengthColor === 'red' ? 'text-red-500' : strengthColor === 'amber' ? 'text-amber-500' : 'text-emerald-600'"
                  x-text="strengthLabel"></span>
        </div>

        @error($name)
            <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
        @enderror
    </div>

    {{-- Confirmation field --}}
    @if($confirm)
        <div class="space-y-1.5">
            <label for="{{ $name }}_confirmation" class="block text-sm font-medium text-gray-600">{{ $confirmLabel }}</label>
            <div class="relative">
                <input
                    :type="showConfirm ? 'text' : 'password'"
                    name="{{ $name }}_confirmation"
                    id="{{ $name }}_confirmation"
                    @if($required) required @endif
                    class="w-full rounded-xl border border-gray-200/80 bg-white/60 px-4 pr-10 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:border-emerald-400 focus:bg-white focus:ring-2 focus:ring-emerald-500/10"
                >
                <button type="button" @click="showConfirm = !showConfirm" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors cursor-pointer">
                    <x-heroicon-o-eye x-show="!showConfirm" class="w-4 h-4" />
                    <x-heroicon-o-eye-slash x-show="showConfirm" x-cloak class="w-4 h-4" />
                </button>
            </div>
        </div>
    @endif
</div>
```

### How the input classes were chosen

The classes `rounded-xl border border-gray-200/80 bg-white/60 px-4 pr-10 py-2.5 text-sm` match the existing `<x-input>` Blade component (defined in `resources/views/components/input.blade.php`), so the strength meter visually matches the rest of the app's forms.

---

## 2. Server-Side Validation

### The rule expression

```php
use Illuminate\Validation\Rules\Password;

Password::min(8)->mixedCase()->numbers()->symbols()
```

- `min(8)` — at least 8 characters
- `mixedCase()` — at least 1 uppercase AND 1 lowercase (uses Unicode `\p{Lu}` / `\p{Ll}` internally)
- `numbers()` — at least 1 digit
- `symbols()` — at least 1 symbol/punctuation/space

### Which FormRequests were modified

| File | Change |
|------|--------|
| `app/Http/Requests/ChangePasswordRequest.php` | `'min:8'` → `Password::min(8)->mixedCase()->numbers()->symbols()` + added `use` import |
| `app/Http/Requests/UpdateUserRequest.php` | `'min:8', 'max:255'` → `Password::defaults()` + added `use` import |
| `app/Http/Requests/RegisterRequest.php` | `'min:8'` → `Password::defaults()` (user/linter did this one) |
| `app/Http/Requests/StoreUserRequest.php` | `'min:8'` → `Password::defaults()` (user/linter did this one) |
| `app/Http/Requests/StoreAgencyRegistrationRequest.php` | Already used `Password::defaults()` — no change needed |
| `app/Http/Requests/StoreBrandRegistrationRequest.php` | Already used `Password::defaults()` — no change needed |

### Password::defaults() in AppServiceProvider

The user (or linter) configured `Password::defaults()` globally in `AppServiceProvider::boot()` so that any FormRequest using `Password::defaults()` automatically gets the full strength rules. This means `StoreAgencyRegistrationRequest` and `StoreBrandRegistrationRequest` get the rules for free.

The exact code that should be in `AppServiceProvider::boot()`:

```php
use Illuminate\Validation\Rules\Password;

public function boot(): void
{
    Password::defaults(fn () => Password::min(8)->mixedCase()->numbers()->symbols());

    // ... any other existing boot code (migrations, rate limiters, etc.)
}
```

`ChangePasswordRequest` used the **explicit** rule chain (not `Password::defaults()`) because it was implemented first, before the global default was set. Either approach works — the rules are identical.

---

## 3. Files Created or Modified (full list)

### New files created

| File | Purpose |
|------|---------|
| `resources/views/components/password-strength.blade.php` | Reusable Alpine.js strength meter component |
| `resources/views/auth/account-locked.blade.php` | Static page for permanently locked accounts (login lockout feature) |
| `tests/Feature/Auth/LoginLockoutTest.php` | Login lockout tests (9 tests) |
| `tests/Feature/Admin/UserLockoutTest.php` | Admin unlock tests (4 tests) |
| `docs/PASSWORD_STRENGTH_INTENT.md` | Intent doc for page-by-page rollout |
| `mobile/Mobius/Sources/Views/Shared/PasswordStrengthView.swift` | SwiftUI password strength component |

### Modified files — Backend

| File | What changed |
|------|-------------|
| `app/Http/Requests/ChangePasswordRequest.php` | Added `Password` import, replaced `'min:8'` with `Password::min(8)->mixedCase()->numbers()->symbols()` |
| `app/Http/Requests/UpdateUserRequest.php` | Added `Password` import, replaced `'min:8', 'max:255'` with `Password::defaults()` |
| `app/Models/User.php` | Added `Carbon` import, added 4 fillable fields (`failed_login_attempts`, `locked_until`, `last_lockout_at`, `is_permanently_locked`), added 3 casts (`locked_until` datetime, `last_lockout_at` datetime, `is_permanently_locked` boolean), added 5 methods (`isLocked`, `isTemporarilyLocked`, `lockoutSecondsRemaining`, `incrementFailedLogin`, `resetLoginAttempts`) |
| `database/migrations/0001_01_01_000000_create_users_table.php` | Added 4 columns after `$table->timestamps()`: `failed_login_attempts` (unsignedTinyInteger default 0), `locked_until` (timestamp nullable), `last_lockout_at` (timestamp nullable), `is_permanently_locked` (boolean default false) |
| `app/Http/Controllers/Auth/LoginController.php` | Added `User` import, added lockout checks before `Auth::attempt()`, added `incrementFailedLogin()` after failed attempt, added `failed_login_attempts` reset after success |
| `app/Http/Controllers/Api/AuthController.php` | Full rewrite with lockout logic — same pattern as LoginController but returns JSON 429 responses with `locked_until`, `permanent`, `retry_after` fields |
| `app/Http/Controllers/Admin/UserController.php` | Added `unlock(User $user)` method that calls `$user->resetLoginAttempts()` |
| `routes/web.php` | Added `Route::post('users/{user}/unlock', ...)` in admin group, added `Route::view('/account-locked', ...)` |

### Modified files — Blade views

| File | What changed |
|------|-------------|
| `resources/views/auth/register.blade.php` | Replaced 2 `<x-input>` (password + confirmation) with `<x-password-strength :confirm="true" confirmLabel="Confirm Password" />` |
| `resources/views/admin/users/create.blade.php` | Replaced 1 `<x-input>` (password) with `<x-password-strength :confirm="false" />` |
| `resources/views/admin/users/edit.blade.php` | (Password tab) Replaced manual input + "Minimum 8 characters" hint with `<x-password-strength label="New password" :confirm="false" />`. (Account tab) Added lockout status card with lock icon, status text, and "Unlock Account" button |
| `resources/views/admin/users/index.blade.php` | Added lock icon (`heroicon-s-lock-closed`) next to user name for locked users |
| `resources/views/registration/brand.blade.php` | Replaced 2 `<x-input>` with `<x-password-strength :confirm="true" confirmLabel="Confirm Password" />` |
| `resources/views/registration/agency.blade.php` | Same as brand |
| `resources/views/partials/profile-form.blade.php` | Replaced `<x-input>` for password + confirmation with `<x-password-strength label="New Password" :confirm="true" confirmLabel="Confirm New Password" />` (kept current_password `<x-input>` above it) |
| `resources/views/admin/profile/edit.blade.php` | Refactored inline Alpine.js strength meter to use `<x-password-strength>` component. Kept the current_password field as standalone `<x-input>` with eye toggle above it |
| `resources/views/auth/login.blade.php` | Added lockout countdown banner above the login card using Alpine.js, reading `session('locked_until')` ISO string |

### Modified files — Tests

| File | What changed |
|------|-------------|
| `tests/Feature/ProfileTest.php` | Changed all submitted passwords from `'newpassword123'` to `'NewPass1!'`. Added 4 violation tests (no uppercase, no lowercase, no number, no symbol) |
| `tests/Feature/Auth/AuthenticationTest.php` | Changed registration test password from `'password'` to `'NewPass1!'` |
| `tests/Feature/Admin/UserManagementTest.php` | Changed `'password123'` to `'NewPass1!'` in create-user tests |
| `tests/Feature/Api/AuthApiTest.php` | Changed `'password123'` to `'NewPass1!'` in registration tests, changed confirmation mismatch to `'DiffPass1!'` |
| `tests/Feature/Registration/BrandRegistrationTest.php` | Changed `'password123'` to `'NewPass1!'` |
| `tests/Feature/Registration/AgencyRegistrationTest.php` | Changed `'password123'` to `'NewPass1!'` |

### Modified files — Mobile (Swift)

| File | What changed |
|------|-------------|
| `mobile/Mobius/Sources/Views/Auth/RegisterView.swift` | Added `PasswordStrengthView(password: password)` below password field, changed `isFormValid` to use `PasswordStrengthView.meetsRequirements(password)` instead of `!password.isEmpty` |
| `mobile/Mobius/Sources/Views/Shared/ChangePasswordView.swift` | Changed `isValid` to use `PasswordStrengthView.meetsRequirements(newPassword)` instead of `newPassword.count >= 8`, replaced inline requirements with `PasswordStrengthView(password: newPassword)` |
| `mobile/Mobius/Sources/Services/APIClient.swift` | Added `case 429` to `validateResponse` throwing `APIError.tooManyAttempts`, added `.tooManyAttempts` case to `APIError` enum with description "Too many attempts. Please try again later or contact support." |

---

## 4. Seeding Compatibility

The seed password `'password'` does **NOT** meet the strength rules (no uppercase, no number, no symbol). This is fine because:

- **The Python seed script** (`scripts/generate_seed_data.py`) uses a pre-computed bcrypt hash inserted directly via SQL. It never goes through Laravel's FormRequest validation.
- **The User factory** (used in tests) sets `'password' => Hash::make('password')` directly in the factory definition. Factory-created users bypass FormRequest validation entirely.
- **Strength rules only apply** when a password is submitted through an HTTP request that hits a FormRequest. Direct `User::create()`, `User::factory()`, and raw SQL INSERTs are unaffected.
- **Login is unaffected** — the strength rules are on registration/password-change endpoints, not on the login endpoint. Users seeded with weak passwords can still log in.

### Test password convention

| Purpose | Password |
|---------|----------|
| Passes all rules | `NewPass1!` |
| No uppercase | `newpass1!` |
| No lowercase | `NEWPASS1!` |
| No number | `NewPass!!` |
| No symbol | `NewPass12` |
| Too short | `Np1!` |

---

## 5. What Was Applied vs. What Wasn't

### Applied (all verified working before the rollback)

- [x] Admin profile password tab — `admin/profile/edit.blade.php` (this was done first, as inline Alpine.js, then refactored to use the component)
- [x] Public registration — `auth/register.blade.php`
- [x] Admin create user — `admin/users/create.blade.php`
- [x] Admin edit user password tab — `admin/users/edit.blade.php`
- [x] Brand registration — `registration/brand.blade.php`
- [x] Agency registration — `registration/agency.blade.php`
- [x] Collector/store-owner profile — `partials/profile-form.blade.php`
- [x] All 6 backend FormRequests (ChangePassword, UpdateUser, Register, StoreUser, StoreAgency, StoreBrand)
- [x] All 5 test files updated with strong passwords
- [x] 4 new violation tests in ProfileTest.php
- [x] Mobile: RegisterView, ChangePasswordView, APIClient (Swift)
- [x] Mobile: New PasswordStrengthView.swift component

### NOT applied (planned but not reached)

- [ ] `Password::defaults()` configuration in `AppServiceProvider` (the user/linter may have done this independently — check current state)
- [ ] Login lockout was partially implemented but the tests were still being debugged when the rollback happened

---

## 6. Login Lockout Feature (partial — for reference)

This was in progress when the session went sideways. Documenting what was built so it can be re-evaluated.

### Schema columns (added to create_users_table migration)

```php
$table->unsignedTinyInteger('failed_login_attempts')->default(0);
$table->timestamp('locked_until')->nullable();
$table->timestamp('last_lockout_at')->nullable();
$table->boolean('is_permanently_locked')->default(false);
```

### User model methods

```php
public function isLocked(): bool
{
    return $this->is_permanently_locked || $this->isTemporarilyLocked();
}

public function isTemporarilyLocked(): bool
{
    return $this->locked_until && $this->locked_until->isFuture();
}

public function lockoutSecondsRemaining(): int
{
    if (! $this->locked_until || $this->locked_until->isPast()) {
        return 0;
    }
    return (int) Carbon::now()->diffInSeconds($this->locked_until);
}

public function incrementFailedLogin(): void
{
    $this->increment('failed_login_attempts');
    if ($this->failed_login_attempts >= 5) {
        if ($this->last_lockout_at && $this->last_lockout_at->isAfter(Carbon::now()->subDays(7))) {
            $this->update([
                'is_permanently_locked' => true,
                'failed_login_attempts' => 0,
            ]);
        } else {
            $this->update([
                'locked_until' => Carbon::now()->addMinutes(30),
                'last_lockout_at' => Carbon::now(),
                'failed_login_attempts' => 0,
            ]);
        }
    }
}

public function resetLoginAttempts(): void
{
    $this->update([
        'failed_login_attempts' => 0,
        'locked_until' => null,
        'last_lockout_at' => null,
        'is_permanently_locked' => false,
    ]);
}
```

### Lockout rules

- 5 failed login attempts → locked for 30 minutes
- Admin can unlock via `POST /admin/users/{user}/unlock` (calls `resetLoginAttempts()`)
- If locked again within 7 days of previous lockout → permanent lock
- Permanently locked users must contact admin (shown link to `/account-locked` page)

---

## 7. Warnings for the Next Instance

1. **DO NOT use `git checkout HEAD`** on files that have extensive uncommitted changes. The working tree may be far ahead of the last commit. If you need to revert your own changes, use targeted `git diff` + manual `Edit` to undo only what you added, or `git stash` first.

2. **The users migration is a consolidated file** — it contains columns like `username`, `roles`, `points_balance`, `current_streak`, etc. that were added over 20 days of work. The committed version in git is bare-bones. Never revert this file to HEAD.

3. **Test passwords in registration/creation tests must be `NewPass1!`** if strength rules are active. Factory-created users (for login tests) still use the default `'password'` — don't change those.

4. **The `<x-password-strength>` component replaces `<x-input>` fields** — it cannot be nested inside `<x-input>`. It renders its own `<input>` elements with matching classes. When adding it to a form, remove the existing `<x-input name="password">` and `<x-input name="password_confirmation">` and replace with a single `<x-password-strength>` call.

5. **Brand and agency registration views are untracked files** — they won't respond to `git checkout HEAD`. If you need to revert changes to untracked files, you must manually edit them back.
