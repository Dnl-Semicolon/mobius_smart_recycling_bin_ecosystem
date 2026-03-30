# Bug Fix CC — Admin Panel, Registration, Role Panels, Detection Pipeline

**Copy everything below the line and paste as your first message in a new CC session.**

---

Load skill: `/using-superpowers`

Read `backend/Z_MENTOR_BRIEFING_FOR_NEW_CC.md` before doing anything — critical safety rules.

## Safety Rules Recap

1. **NEVER run git commands.** I handle all git in a separate terminal. After each task, tell me what changed + suggest a commit message.
2. **NEVER run `migrate:fresh` or `db:seed`.** The MySQL database has 100k+ rows of demo data. If you wipe it, the project is dead.
3. **One bug at a time.** Fix it, I test and commit, then we move to the next.
4. **Run `vendor/bin/pint --dirty` after any PHP changes.**
5. **Run relevant tests** with `php artisan test --compact --filter=TestName` after fixes.

## Current State

- **Database**: MySQL (`mobius` database, 101 users, 113k detections, all working)
- **Server**: `composer run dev` running on `http://localhost:8000`
- **Auth**: Web login works, email verification enabled, `daniel@mobius.test` is the multi-role admin account
- **iOS**: SwiftUI app running on iPhone, logged in as `daniel@mobius.test`

## Your Mission

Fix bugs across the admin panel, verify all role dashboards work, and ensure the detection pipeline stores data correctly in MySQL. **One bug at a time, in this order.**

### Bug 1: Fix `http://localhost:8000/admin/brands` Exception (CRITICAL)

This page throws an exception every time it's accessed. It's in the admin sidebar so it's very visible.

**Investigation steps:**
1. Read `app/Http/Controllers/Admin/BrandMonitoringController.php`
2. The `index()` method loads brands with counts. The `show()` method uses `flatMap()` on outlets.bins — this might fail if a brand has no outlets or outlets have no bins.
3. Check if the issue is that brands have `status = 'pending'` (not 'approved') — the seed data might set brands as pending but the view might filter by approved only.
4. Run: `php artisan tinker --execute="echo App\Models\Brand::first()->toJson(JSON_PRETTY_PRINT);"` to see brand data
5. Try accessing the route programmatically: `php artisan tinker --execute="echo app(App\Http\Controllers\Admin\BrandMonitoringController::class)->index(new Illuminate\Http\Request)->render();"` or just read the error log at `storage/logs/laravel.log`

Fix whatever is causing the exception. The page should load and show the 3 seeded brands (ZUS Coffee, Starbucks, MIXUE).

### Bug 2: Verify All Registration Pages Load

Check these routes load without errors:
- `http://localhost:8000/register` — public user registration
- `http://localhost:8000/register/brand` — brand registration
- `http://localhost:8000/register/agency` — agency registration

Read each view file, check if they reference any components or fields that don't exist. Fix any issues.

### Bug 3: Verify All Role Dashboards Load

Each role has its own dashboard. Log in as appropriate users and verify:

- **Admin**: `http://localhost:8000/admin/` — `admin@mobius.test`
- **Store Owner**: `http://localhost:8000/store/` — find a store_owner user or use `daniel@mobius.test`
- **Collector**: `http://localhost:8000/collector/` — find a collector user or use `daniel@mobius.test`
- **Public User**: `http://localhost:8000/public/` — any public_user
- **Agency Admin**: `http://localhost:8000/agency/` — find an agency_admin user

For each dashboard, check:
1. Does the page load without errors?
2. Does the sidebar/navigation work?
3. Are role-specific features accessible?

Use `php artisan tinker` to find test users for each role:
```php
App\Models\User::whereJsonContains('roles', 'store_owner')->first(['id','email','name']);
```

Fix any 500 errors, missing views, or broken routes. **One fix at a time.**

### Bug 4: Verify Detection Events Reach MySQL with User Linking

This is the core pipeline. Verify:

1. **Check recent detections**:
   ```
   php artisan tinker --execute="echo App\Models\DetectionEvent::latest()->take(5)->get(['id','bin_id','user_id','waste_type','confidence','detected_at'])->toJson(JSON_PRETTY_PRINT);"
   ```

2. **Check if QR scan → user linking works**:
   - The iOS app scans a QR code (bin serial number)
   - This calls `POST /api/v1/customer/scan` with `{ bin_serial: "MBR-2026-079" }`
   - Laravel caches `bin_session:{bin_id} = user_id` for 60 seconds
   - The next detection on that bin should have `user_id` set

3. **Read `app/Http/Controllers/Api/DetectionEventController.php`** — the `store()` method at the top. Check:
   - Line ~65: Does it pull from `Cache::pull("bin_session:{$bin_id}")` correctly?
   - Is the cache driver configured for MySQL (or file)? Check `.env` for `CACHE_STORE` — if it's `database`, the cache table must exist.

4. **Check cache driver**:
   ```
   php artisan tinker --execute="echo config('cache.default');"
   ```
   If it's `database`, verify the cache table exists:
   ```
   php artisan tinker --execute="echo Schema::hasTable('cache') ? 'exists' : 'missing';"
   ```

5. **Test the flow manually**:
   - I'll scan QR with my iPhone
   - Then trigger a detection from the React app
   - Check if the detection has `user_id` set
   - Report the results back to you

Fix any issues found. The end goal: when I scan a QR code with the iOS app and then a detection happens on that bin, the detection should be linked to my user account and points should be awarded.

### Bug 5: Check Admin Sidebar Pages

Go through each admin sidebar link and verify the page loads:
- Dashboard
- Outlets (index)
- Bins (index)
- Detection Events (index)
- Pickup Requests (index)
- Users (index)
- Brands (index) — should be fixed by Bug 1
- Applications → Brands
- Applications → Agencies
- Reports (index)
- Notifications (index)

For each, just do a quick GET request check or read the controller to spot obvious issues. Fix any 500 errors.

## Architecture Reference

**Key test accounts** (password: `password`):
- `daniel@mobius.test` — admin, public_user, store_owner, collector (multi-role)
- `admin@mobius.test` — admin only

**Role checking**: `$user->hasRole(UserRole::Admin)` or `$user->primaryRole()`

**Detection pipeline**:
```
POST /api/v1/detect → DetectionEventController@store → DetectionEvent created
                                                            ↓
                                                   Observer → processDetection()
                                                            ↓
                                                   if (user_id) → awardPoints() → update points_balance
```

**User linking via QR**:
```
POST /api/v1/customer/scan { bin_serial } → Cache::put("bin_session:{bin_id}", user_id, 60s)
                                                    ↓
Next POST /api/v1/detect { bin_id } → Cache::pull("bin_session:{bin_id}") → sets user_id
```
