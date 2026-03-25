# Store Owner Platform Architecture Redesign — Design Spec

## Summary

Resolve the store owner identity ambiguity, fix multi-outlet dashboard scoping, add store-owner API endpoints for mobile, and build lightweight staff/branch-manager invitation. Skip corporate marketing site and outlet self-management.

---

## Decision: Store Owner Identity Model — Hybrid (HQ + Branch)

**Brand registrant** (`Brand.user_id`) = **Brand HQ**. Sees ALL outlets, ALL bins, ALL analytics for the entire brand. Creates and manages rewards. Manages budget.

**Branch managers** (via `outlet_user` pivot) = **Outlet Managers**. See only their assigned outlet(s). Can view rewards but cannot create/edit/delete. Both share `store_owner` role in the `users.roles` JSON array.

**Scoping logic:** A `StoreOwnerContext` service resolves the user's brand context:
- Finds the user's Brand (either via `Brand.user_id` match OR via `outlet_user` → `outlet.brand_id`)
- Determines if user is HQ: `$user->id === $brand->user_id`
- Returns outlet scope: all brand outlets for HQ, only assigned outlets for branch managers

No new roles needed in `UserRole` enum. No schema migrations for the identity model — `Brand.user_id` and `outlet_user` pivot already exist.

---

## Architecture: StoreOwnerContext Service

Central service that all store-owner controllers (web and API) use. Eliminates the current pattern of `$user->outlets()->first()` scattered across controllers.

**Responsibilities:**
- `resolve(User $user): StoreOwnerContextData` — returns a value object with: `brand`, `outlets` (Collection), `isHQ` (bool), `binIds` (array of all bin IDs across scoped outlets)
- Aborts 403 if user has no brand association (neither HQ nor branch manager)
- Used by both web controllers and API controllers

**StoreOwnerContextData** (simple value object / readonly class):
- `Brand $brand`
- `Collection $outlets` (Outlet collection, scoped)
- `bool $isHQ`
- `array $binIds` (flattened from all scoped outlets' bins)

Note: Per-outlet drill-down filtering (`?outlet={id}`) is handled by the controller, not the service. The controller validates the requested outlet is within the user's scope, then filters queries accordingly.

---

## Phase 1: Fix Dashboard Scoping + Brand Context Service

### StoreOwnerContext Service
- New file: `app/Services/StoreOwnerContext.php`
- `resolve(User $user): StoreOwnerContextData`
  - Check if user is HQ: `Brand::where('user_id', $user->id)->first()`
  - If not HQ, find brand through outlets: `$user->outlets()->with('brand')->get()` -> extract brand
  - If no brand found either way, abort 403
  - Return context data object with proper outlet scoping

### Refactor DashboardController
- Replace `$user->outlets()->first()` with `StoreOwnerContext::resolve($user)`
- Stats queries use `$context->binIds` instead of single outlet's bin IDs
- View receives `$context->outlets` (collection)
- Support `?outlet={id}` query param: controller validates outlet is in `$context->outlets`, filters `binIds` to that outlet's bins only. Default (no param) = aggregated across all scoped outlets.

### Refactor RewardController
- Replace `$this->getBrand()` with `StoreOwnerContext::resolve($user)`
- Add HQ check for mutation routes (create/update/delete): `abort_unless($context->isHQ, 403)`
- Read routes (index) available to both HQ and branch

### Dashboard View Changes
- Add outlet selector at top of dashboard (dropdown or tabs)
- "All Outlets" option (default for HQ, shows aggregated stats)
- Individual outlet options filter stats to that outlet
- Brand welcome header shows brand name + outlet count instead of single outlet name
- When specific outlet selected, show outlet name + address
- Bins section shows bins grouped by outlet (with outlet name labels)

### Store Owner Layout Update
- Sidebar brand card: show brand name + "N outlets" instead of single outlet name
- No structural layout changes needed

### Tests
- Test HQ user sees aggregated stats across multiple outlets
- Test branch manager sees only assigned outlet stats
- Test outlet filter query param works correctly
- Test branch manager cannot access create/edit/delete reward routes (403)
- Test HQ user can still CRUD rewards
- Test user with no brand association gets 403
- Update existing `StoreOwnerDashboardTest` for new scoping

---

## Phase 2: Store Owner API Endpoints

### Route Group
Prefix: `/api/v1/store-owner`
Middleware: `auth:sanctum`, `role:store_owner`

### Endpoints

**Dashboard & Brand:**
- `GET /dashboard` — aggregated stats: detection counts (today/week/month), unique recyclers, redemption count, active rewards count, brand loyalty breakdown (match/competitor/unidentified cups), waste breakdown. Accepts `?outlet={id}` for filtering.
- `GET /brand` — brand profile: name, slug, logo_path, primary_color, points_multiplier, rewards_budget, status.

**Outlets & Bins:**
- `GET /outlets` — list outlets in scope (all for HQ, assigned for branch). Each outlet includes: name, address, lat/lng, bin count, today's detection count.
- `GET /outlets/{outlet}` — single outlet detail with bins, recent detections summary, waste breakdown. Validates outlet is in user's scope.
- `GET /bins` — all bins across scoped outlets. Each bin includes: serial_number, fill_level, status, last_seen_at, outlet name.

**Analytics:**
- `GET /analytics` — time-series data: daily detections (last 14 days), waste type breakdown (last 30 days), hourly distribution (last 30 days). Accepts `?outlet={id}`.

**Rewards (HQ-only mutation, read for all):**
- `GET /rewards` — brand's rewards with redemption counts, stock, active status, expiry.
- `POST /rewards` — create reward. HQ only (403 for branch).
- `PUT /rewards/{reward}` — update reward. HQ only.
- `DELETE /rewards/{reward}` — delete reward. HQ only.

### API Resources
- `StoreOwnerDashboardResource` — formats dashboard stats response
- `StoreOwnerOutletResource` — outlet with bin summary
- `StoreOwnerBinResource` — bin with fill level and status
- `StoreOwnerAnalyticsResource` — time-series data structures

### Controllers
- `Api\StoreOwner\DashboardController` — dashboard + analytics + brand
- `Api\StoreOwner\OutletController` — outlets + bins listing
- `Api\StoreOwner\RewardController` — reward CRUD

All controllers inject `StoreOwnerContext` service for scoping.

### Tests
- Test each endpoint returns correct data structure
- Test HQ vs branch scoping on every endpoint
- Test reward mutation endpoints return 403 for branch managers
- Test outlet filter works
- Test unauthenticated / wrong role returns 401/403

---

## Phase 3: Staff Management (Branch Manager Invitation)

### Store Owner Sidebar
Add "Staff" nav item between "Rewards" and "Analytics" in the store-owner layout sidebar.

### Web Routes (inside store group)
- `GET /store/staff` — staff list page
- `POST /store/staff/invite` — invite a branch manager
- `DELETE /store/staff/{user}` — remove a branch manager

All three routes require HQ status (branch managers cannot manage other staff).

### Staff Controller: `StoreOwner\StaffController`
- `index(Request)` — list all users in `outlet_user` pivot for this brand's outlets, grouped by outlet. Shows: name, email, outlet assignment, joined date.
- `invite(Request)` — validates: email (required, email), outlet_id (required, exists in brand's outlets). If user exists by email: attach to outlet_user pivot with role='manager', add `store_owner` role if not present. If user doesn't exist: create user with email + temporary hashed password (bcrypt random string) + `store_owner` role, attach to pivot, log welcome notification intent. Matches existing pattern in `ApplicationService::registerBrand()` — real password reset handled via login flow when email notifications are implemented.
- `remove(User)` — detach user from all of this brand's outlet_user entries. If user has no remaining outlet assignments, remove `store_owner` role.

### Staff Views
- `store-owner/staff/index.blade.php` — grouped list by outlet. Each outlet section shows its managers. Invite form at top (email + outlet dropdown). Remove button per manager (with confirmation).

### API Endpoints (extend Phase 2)
- `GET /api/v1/store-owner/staff` — list staff
- `POST /api/v1/store-owner/staff/invite` — invite
- `DELETE /api/v1/store-owner/staff/{user}` — remove

### Tests
- Test HQ can view staff list
- Test HQ can invite new user (creates user + pivot entry)
- Test HQ can invite existing user (attaches to pivot)
- Test HQ can remove branch manager (detaches from pivot)
- Test branch manager cannot access staff routes (403)
- Test invited user can log in and see only assigned outlet
- Test removing last outlet assignment removes store_owner role

---

## Phase 4: Dashboard Polish + Analytics Upgrade (Nice-to-Have)

Per-outlet drill-down is already built in Phase 1 (via `?outlet={id}` query param). This phase adds visual polish only.

### Comparative Analytics
- Side-by-side outlet performance (detection counts, waste breakdown)
- Only visible when brand has 2+ outlets
- Bar chart comparing outlets on key metrics

### Trend Indicators
- Stats cards show directional arrows: "12% vs last week"
- Computed by comparing current period to previous period of same length

---

## Explicitly Out of Scope

| Feature | Why Deferred |
|---------|-------------|
| Corporate marketing landing page | Cosmetic work with no architectural value. Current `/home` is functional. |
| Outlet self-management | Requires approval pipeline. Admin CRUD is sufficient. Outlets are seeded for demos. |
| Mobile store owner UI implementation | API will be ready (Phase 2), but mobile views are a separate work item. |
| Branch manager reward creation | Read-only for branches is the correct business rule. |
| Role hierarchy beyond HQ/branch | Regional manager, area director, etc. are over-engineering for FYP scope. |
| Notification emails for invitations | Log intent only, matching existing pattern in ApplicationService. |

---

## Files Summary

### New Files (~20)
- 1 service: `StoreOwnerContext` + value object
- 3 API controllers: `Api\StoreOwner\{Dashboard,Outlet,Reward}Controller`
- 4 API resources: dashboard, outlet, bin, analytics
- 1 web controller: `StoreOwner\StaffController`
- 1 view: staff index
- ~5 test files: context service, dashboard scoping, API endpoints, staff management, reward authorization

### Modified Files (~8)
- `StoreOwner\DashboardController` — use StoreOwnerContext
- `StoreOwner\RewardController` — use StoreOwnerContext + HQ gate
- `store-owner/dashboard.blade.php` — outlet selector, multi-outlet stats
- `components/layouts/store-owner.blade.php` — add Staff nav item, update brand card
- `routes/web.php` — add staff routes
- `routes/api.php` — add store-owner API group
- `tests/Feature/StoreOwner/StoreOwnerDashboardTest.php` — update for new scoping

### No Schema Migrations
The existing `Brand.user_id` and `outlet_user` pivot table are sufficient. No database changes needed.
