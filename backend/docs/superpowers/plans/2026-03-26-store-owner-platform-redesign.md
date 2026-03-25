# Store Owner Platform Redesign — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Fix store owner multi-outlet scoping, add store-owner API endpoints for mobile, build staff management, and add admin brand monitoring.

**Architecture:** A `StoreOwnerContext` service resolves whether a user is Brand HQ (`Brand.user_id`) or a branch manager (`outlet_user` pivot), returning scoped outlets and bin IDs. All store-owner controllers (web + API) use this service. Admin gets read-only brand monitoring views.

**Tech Stack:** Laravel 12, Pest 4, Tailwind v4, Alpine.js, Sanctum API auth

---

## File Structure

### New Files
| File | Responsibility |
|------|---------------|
| `app/Services/StoreOwnerContext.php` | Resolves user's brand, outlets, isHQ status, bin IDs |
| `app/Http/Controllers/Api/StoreOwner/DashboardController.php` | API: dashboard stats, analytics, brand info |
| `app/Http/Controllers/Api/StoreOwner/OutletController.php` | API: list/show outlets and bins |
| `app/Http/Controllers/Api/StoreOwner/RewardController.php` | API: CRUD rewards (HQ-only mutations) |
| `app/Http/Controllers/StoreOwner/StaffController.php` | Web: staff list, invite, remove |
| `app/Http/Controllers/Admin/BrandMonitoringController.php` | Admin: view brands and their data |
| `app/Http/Resources/StoreOwner/DashboardResource.php` | API resource: dashboard stats |
| `app/Http/Resources/StoreOwner/OutletResource.php` | API resource: outlet with bins |
| `app/Http/Resources/StoreOwner/BrandResource.php` | API resource: brand profile |
| `resources/views/store-owner/staff/index.blade.php` | Staff management page |
| `resources/views/admin/brands/index.blade.php` | Admin brand list |
| `resources/views/admin/brands/show.blade.php` | Admin brand detail with stats |
| `tests/Feature/StoreOwner/StoreOwnerContextTest.php` | Context service tests |
| `tests/Feature/StoreOwner/StoreOwnerScopingTest.php` | HQ vs branch scoping tests |
| `tests/Feature/StoreOwner/StaffManagementTest.php` | Staff invite/remove tests |
| `tests/Feature/Api/StoreOwnerApiTest.php` | All store-owner API endpoint tests |
| `tests/Feature/Admin/BrandMonitoringTest.php` | Admin brand monitoring tests |

### Modified Files
| File | Changes |
|------|---------|
| `app/Http/Controllers/StoreOwner/DashboardController.php` | Use StoreOwnerContext, support `?outlet=` filter |
| `app/Http/Controllers/StoreOwner/RewardController.php` | Use StoreOwnerContext, HQ gate on mutations |
| `resources/views/store-owner/dashboard.blade.php` | Outlet selector, multi-outlet aggregation |
| `resources/views/components/layouts/store-owner.blade.php` | Add Staff nav item, update brand card |
| `resources/views/components/admin/sidebar.blade.php` | Add Brands nav item |
| `routes/web.php` | Staff routes, admin brand routes |
| `routes/api.php` | Store-owner API route group |

---

## Task 1: StoreOwnerContext Service

**Files:**
- Create: `app/Services/StoreOwnerContext.php`
- Test: `tests/Feature/StoreOwner/StoreOwnerContextTest.php`

- [ ] **Step 1: Write failing tests for the context service**

```php
<?php

use App\Enums\ApplicationStatus;
use App\Models\Brand;
use App\Models\Outlet;
use App\Models\User;
use App\Services\StoreOwnerContext;

it('resolves HQ user context with all brand outlets', function () {
    $user = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $user->id]);
    $outlet1 = Outlet::factory()->create(['brand_id' => $brand->id]);
    $outlet2 = Outlet::factory()->create(['brand_id' => $brand->id]);

    $context = app(StoreOwnerContext::class)->resolve($user);

    expect($context->brand->id)->toBe($brand->id)
        ->and($context->isHQ)->toBeTrue()
        ->and($context->outlets)->toHaveCount(2)
        ->and($context->outlets->pluck('id')->toArray())
            ->toContain($outlet1->id, $outlet2->id);
});

it('resolves branch manager context with only assigned outlets', function () {
    $hqUser = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $hqUser->id]);
    $outlet1 = Outlet::factory()->create(['brand_id' => $brand->id]);
    $outlet2 = Outlet::factory()->create(['brand_id' => $brand->id]);

    $branchUser = User::factory()->storeOwner()->create();
    $outlet1->managers()->attach($branchUser->id, ['role' => 'manager']);

    $context = app(StoreOwnerContext::class)->resolve($branchUser);

    expect($context->brand->id)->toBe($brand->id)
        ->and($context->isHQ)->toBeFalse()
        ->and($context->outlets)->toHaveCount(1)
        ->and($context->outlets->first()->id)->toBe($outlet1->id);
});

it('collects bin IDs across all scoped outlets', function () {
    $user = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $user->id]);
    $outlet1 = Outlet::factory()->create(['brand_id' => $brand->id]);
    $outlet2 = Outlet::factory()->create(['brand_id' => $brand->id]);
    $bin1 = \App\Models\Bin::factory()->create();
    $bin2 = \App\Models\Bin::factory()->create();
    \App\Models\BinAssignment::create(['bin_id' => $bin1->id, 'outlet_id' => $outlet1->id, 'assigned_at' => now()]);
    \App\Models\BinAssignment::create(['bin_id' => $bin2->id, 'outlet_id' => $outlet2->id, 'assigned_at' => now()]);

    $context = app(StoreOwnerContext::class)->resolve($user);

    expect($context->binIds)->toContain($bin1->id, $bin2->id)
        ->and($context->binIds)->toHaveCount(2);
});

it('aborts 403 for user with no brand association', function () {
    $user = User::factory()->storeOwner()->create();

    app(StoreOwnerContext::class)->resolve($user);
})->throws(\Symfony\Component\HttpKernel\Exception\HttpException::class);

it('filters outlets when outlet ID is provided', function () {
    $user = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $user->id]);
    $outlet1 = Outlet::factory()->create(['brand_id' => $brand->id]);
    $outlet2 = Outlet::factory()->create(['brand_id' => $brand->id]);

    $context = app(StoreOwnerContext::class)->resolve($user, $outlet1->id);

    expect($context->outlets)->toHaveCount(1)
        ->and($context->outlets->first()->id)->toBe($outlet1->id)
        ->and($context->selectedOutlet->id)->toBe($outlet1->id);
});

it('rejects outlet filter for outlet not in scope', function () {
    $hqUser = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $hqUser->id]);
    $outlet1 = Outlet::factory()->create(['brand_id' => $brand->id]);

    $branchUser = User::factory()->storeOwner()->create();
    $outlet1->managers()->attach($branchUser->id, ['role' => 'manager']);

    $otherOutlet = Outlet::factory()->create(['brand_id' => $brand->id]);

    app(StoreOwnerContext::class)->resolve($branchUser, $otherOutlet->id);
})->throws(\Symfony\Component\HttpKernel\Exception\HttpException::class);
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --compact tests/Feature/StoreOwner/StoreOwnerContextTest.php`
Expected: FAIL — class `StoreOwnerContext` not found

- [ ] **Step 3: Implement the StoreOwnerContext service**

```php
<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Support\Collection;

class StoreOwnerContextData
{
    /**
     * @param  array<int>  $binIds
     */
    public function __construct(
        public readonly Brand $brand,
        public readonly Collection $outlets,
        public readonly bool $isHQ,
        public readonly array $binIds,
        public readonly ?Outlet $selectedOutlet = null,
    ) {}
}

class StoreOwnerContext
{
    public function resolve(User $user, ?int $outletId = null): StoreOwnerContextData
    {
        // Check if user is brand HQ
        $brand = Brand::where('user_id', $user->id)->first();
        $isHQ = $brand !== null;

        if (! $brand) {
            // Find brand through outlet assignments
            $assignedOutlet = $user->outlets()->with('brand')->first();
            $brand = $assignedOutlet?->brand;
        }

        abort_unless($brand, 403, 'No brand associated with your account.');

        // Scope outlets: HQ sees all, branch sees assigned only
        $outlets = $isHQ
            ? $brand->outlets()->with('bins')->get()
            : $user->outlets()->where('brand_id', $brand->id)->with('bins')->get();

        // Handle outlet filter
        $selectedOutlet = null;
        if ($outletId !== null) {
            $selectedOutlet = $outlets->firstWhere('id', $outletId);
            abort_unless($selectedOutlet, 403, 'Outlet not in your scope.');
            $outlets = collect([$selectedOutlet]);
        }

        // Collect bin IDs across all scoped outlets
        $binIds = $outlets->flatMap(fn (Outlet $outlet) => $outlet->bins->pluck('id'))->all();

        return new StoreOwnerContextData(
            brand: $brand,
            outlets: $outlets,
            isHQ: $isHQ,
            binIds: $binIds,
            selectedOutlet: $selectedOutlet,
        );
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test --compact tests/Feature/StoreOwner/StoreOwnerContextTest.php`
Expected: All 6 tests PASS

- [ ] **Step 5: Run pint and commit**

```bash
vendor/bin/pint --dirty
git add app/Services/StoreOwnerContext.php tests/Feature/StoreOwner/StoreOwnerContextTest.php
git commit -m "feat: add StoreOwnerContext service for HQ/branch scoping"
```

---

## Task 2: Refactor Web Controllers to Use StoreOwnerContext

**Files:**
- Modify: `app/Http/Controllers/StoreOwner/DashboardController.php`
- Modify: `app/Http/Controllers/StoreOwner/RewardController.php`
- Test: `tests/Feature/StoreOwner/StoreOwnerScopingTest.php`
- Modify: `tests/Feature/StoreOwner/StoreOwnerDashboardTest.php`

- [ ] **Step 1: Write failing tests for multi-outlet scoping and HQ gate**

```php
<?php

use App\Models\Bin;
use App\Models\BinAssignment;
use App\Models\Brand;
use App\Models\DetectionEvent;
use App\Models\Outlet;
use App\Models\Reward;
use App\Models\User;

it('HQ user sees aggregated stats across all outlets', function () {
    $user = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $user->id]);
    $outlet1 = Outlet::factory()->create(['brand_id' => $brand->id]);
    $outlet2 = Outlet::factory()->create(['brand_id' => $brand->id]);
    $bin1 = Bin::factory()->create();
    $bin2 = Bin::factory()->create();
    BinAssignment::create(['bin_id' => $bin1->id, 'outlet_id' => $outlet1->id, 'assigned_at' => now()]);
    BinAssignment::create(['bin_id' => $bin2->id, 'outlet_id' => $outlet2->id, 'assigned_at' => now()]);

    // Attach HQ user to first outlet (so existing code would only see outlet1)
    $outlet1->managers()->attach($user->id, ['role' => 'manager']);

    DetectionEvent::factory()->create(['bin_id' => $bin1->id, 'detected_at' => now()]);
    DetectionEvent::factory()->create(['bin_id' => $bin2->id, 'detected_at' => now()]);

    $response = $this->actingAs($user)->get(route('store.dashboard'));

    $response->assertOk();
    // HQ should see detections from BOTH outlets
    $response->assertViewHas('todayDetections', 2);
});

it('branch manager sees only assigned outlet stats', function () {
    $hqUser = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $hqUser->id]);
    $outlet1 = Outlet::factory()->create(['brand_id' => $brand->id]);
    $outlet2 = Outlet::factory()->create(['brand_id' => $brand->id]);
    $bin1 = Bin::factory()->create();
    $bin2 = Bin::factory()->create();
    BinAssignment::create(['bin_id' => $bin1->id, 'outlet_id' => $outlet1->id, 'assigned_at' => now()]);
    BinAssignment::create(['bin_id' => $bin2->id, 'outlet_id' => $outlet2->id, 'assigned_at' => now()]);

    $branchUser = User::factory()->storeOwner()->create();
    $outlet1->managers()->attach($branchUser->id, ['role' => 'manager']);

    DetectionEvent::factory()->create(['bin_id' => $bin1->id, 'detected_at' => now()]);
    DetectionEvent::factory()->create(['bin_id' => $bin2->id, 'detected_at' => now()]);

    $response = $this->actingAs($branchUser)->get(route('store.dashboard'));

    $response->assertOk();
    // Branch should see only outlet1's detection
    $response->assertViewHas('todayDetections', 1);
});

it('outlet filter narrows dashboard stats', function () {
    $user = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $user->id]);
    $outlet1 = Outlet::factory()->create(['brand_id' => $brand->id]);
    $outlet2 = Outlet::factory()->create(['brand_id' => $brand->id]);
    $bin1 = Bin::factory()->create();
    $bin2 = Bin::factory()->create();
    BinAssignment::create(['bin_id' => $bin1->id, 'outlet_id' => $outlet1->id, 'assigned_at' => now()]);
    BinAssignment::create(['bin_id' => $bin2->id, 'outlet_id' => $outlet2->id, 'assigned_at' => now()]);

    DetectionEvent::factory()->create(['bin_id' => $bin1->id, 'detected_at' => now()]);
    DetectionEvent::factory()->create(['bin_id' => $bin2->id, 'detected_at' => now()]);

    $response = $this->actingAs($user)->get(route('store.dashboard', ['outlet' => $outlet1->id]));

    $response->assertOk();
    $response->assertViewHas('todayDetections', 1);
});

it('branch manager cannot create rewards', function () {
    $hqUser = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $hqUser->id]);
    $outlet = Outlet::factory()->create(['brand_id' => $brand->id]);

    $branchUser = User::factory()->storeOwner()->create();
    $outlet->managers()->attach($branchUser->id, ['role' => 'manager']);

    $response = $this->actingAs($branchUser)->post(route('store.rewards.store'), [
        'name' => 'Test Reward',
        'points_cost' => 100,
    ]);

    $response->assertForbidden();
});

it('branch manager can view rewards index', function () {
    $hqUser = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $hqUser->id]);
    $outlet = Outlet::factory()->create(['brand_id' => $brand->id]);

    $branchUser = User::factory()->storeOwner()->create();
    $outlet->managers()->attach($branchUser->id, ['role' => 'manager']);

    $response = $this->actingAs($branchUser)->get(route('store.rewards.index'));

    $response->assertOk();
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --compact tests/Feature/StoreOwner/StoreOwnerScopingTest.php`
Expected: FAIL — HQ user gets 403 (current code requires outlet_user pivot), branch scoping wrong

- [ ] **Step 3: Refactor DashboardController**

Replace the full content of `app/Http/Controllers/StoreOwner/DashboardController.php`:

```php
<?php

namespace App\Http\Controllers\StoreOwner;

use App\Enums\WasteType;
use App\Http\Controllers\Controller;
use App\Models\DetectionEvent;
use App\Models\Redemption;
use App\Services\StoreOwnerContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private StoreOwnerContext $context) {}

    public function index(Request $request): View
    {
        $ctx = $this->context->resolve($request->user(), $request->integer('outlet') ?: null);
        $brand = $ctx->brand;
        $binIds = $ctx->binIds;

        $todayDetections = DetectionEvent::whereIn('bin_id', $binIds)
            ->whereDate('detected_at', today())
            ->count();

        $weekDetections = DetectionEvent::whereIn('bin_id', $binIds)
            ->where('detected_at', '>=', now()->startOfWeek())
            ->count();

        $monthDetections = DetectionEvent::whereIn('bin_id', $binIds)
            ->where('detected_at', '>=', now()->startOfMonth())
            ->count();

        $wasteBreakdown = DetectionEvent::whereIn('bin_id', $binIds)
            ->where('detected_at', '>=', now()->startOfMonth())
            ->whereNotNull('waste_type')
            ->selectRaw('waste_type, count(*) as total')
            ->groupBy('waste_type')
            ->pluck('total', 'waste_type')
            ->toArray();

        $uniqueRecyclers = DetectionEvent::whereIn('bin_id', $binIds)
            ->where('detected_at', '>=', now()->startOfMonth())
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');

        // Brand loyalty stats (cups only)
        $brandMatchCups = 0;
        $competitorCups = 0;
        $unidentifiedCups = 0;
        $cupTypes = [WasteType::PaperCup->value, WasteType::PlasticCup->value];
        $cupsQuery = DetectionEvent::whereIn('bin_id', $binIds)
            ->where('detected_at', '>=', now()->startOfMonth())
            ->whereIn('waste_type', $cupTypes);

        $brandMatchCups = (clone $cupsQuery)->where('detected_brand_id', $brand->id)->count();
        $competitorCups = (clone $cupsQuery)->whereNotNull('detected_brand_id')->where('detected_brand_id', '!=', $brand->id)->count();
        $unidentifiedCups = (clone $cupsQuery)->whereNull('detected_brand_id')->count();

        // Reward redemptions
        $rewardIds = $brand->rewards()->pluck('id');
        $redemptionCount = Redemption::whereIn('reward_id', $rewardIds)
            ->where('redeemed_at', '>=', now()->startOfMonth())
            ->count();
        $activeRewards = $brand->rewards()->where('active', true)->count();

        $outlets = $ctx->outlets;
        $selectedOutlet = $ctx->selectedOutlet;
        $isHQ = $ctx->isHQ;

        return view('store-owner.dashboard', compact(
            'brand',
            'outlets',
            'selectedOutlet',
            'isHQ',
            'todayDetections',
            'weekDetections',
            'monthDetections',
            'wasteBreakdown',
            'uniqueRecyclers',
            'brandMatchCups',
            'competitorCups',
            'unidentifiedCups',
            'redemptionCount',
            'activeRewards',
        ));
    }

    public function analytics(Request $request): View
    {
        $ctx = $this->context->resolve($request->user(), $request->integer('outlet') ?: null);
        $binIds = $ctx->binIds;

        $dailyDetections = DetectionEvent::whereIn('bin_id', $binIds)
            ->where('detected_at', '>=', now()->subDays(14))
            ->selectRaw('DATE(detected_at) as date, count(*) as total')
            ->groupByRaw('DATE(detected_at)')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        $wasteBreakdown = DetectionEvent::whereIn('bin_id', $binIds)
            ->where('detected_at', '>=', now()->subDays(30))
            ->whereNotNull('waste_type')
            ->selectRaw('waste_type, count(*) as total')
            ->groupBy('waste_type')
            ->pluck('total', 'waste_type')
            ->toArray();

        $hourlyDistribution = DetectionEvent::whereIn('bin_id', $binIds)
            ->where('detected_at', '>=', now()->subDays(30))
            ->selectRaw("strftime('%H', detected_at) as hour, count(*) as total")
            ->groupByRaw("strftime('%H', detected_at)")
            ->orderBy('hour')
            ->pluck('total', 'hour')
            ->toArray();

        $outlets = $ctx->outlets;
        $selectedOutlet = $ctx->selectedOutlet;

        return view('store-owner.analytics', compact(
            'outlets',
            'selectedOutlet',
            'dailyDetections',
            'wasteBreakdown',
            'hourlyDistribution',
        ));
    }
}
```

- [ ] **Step 4: Refactor RewardController**

Replace the full content of `app/Http/Controllers/StoreOwner/RewardController.php`:

```php
<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Services\StoreOwnerContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RewardController extends Controller
{
    public function __construct(private StoreOwnerContext $context) {}

    public function index(Request $request): View
    {
        $ctx = $this->context->resolve($request->user());
        $brand = $ctx->brand;

        $rewards = $brand->rewards()
            ->withCount('redemptions')
            ->orderBy('sort_order')
            ->get();

        return view('store-owner.rewards.index', compact('brand', 'rewards'));
    }

    public function create(Request $request): View
    {
        $ctx = $this->context->resolve($request->user());
        abort_unless($ctx->isHQ, 403, 'Only brand administrators can create rewards.');
        $brand = $ctx->brand;

        return view('store-owner.rewards.create', compact('brand'));
    }

    public function store(Request $request): RedirectResponse
    {
        $ctx = $this->context->resolve($request->user());
        abort_unless($ctx->isHQ, 403, 'Only brand administrators can create rewards.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'points_cost' => ['required', 'integer', 'min:1'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        $ctx->brand->rewards()->create([
            ...$validated,
            'active' => $request->boolean('active', true),
        ]);

        return redirect()->route('store.rewards.index')
            ->with('success', 'Reward created successfully.');
    }

    public function edit(Request $request, Reward $reward): View
    {
        $ctx = $this->context->resolve($request->user());
        abort_unless($ctx->isHQ, 403, 'Only brand administrators can edit rewards.');
        abort_unless($reward->brand_id === $ctx->brand->id, 403);

        $brand = $ctx->brand;

        return view('store-owner.rewards.edit', compact('brand', 'reward'));
    }

    public function update(Request $request, Reward $reward): RedirectResponse
    {
        $ctx = $this->context->resolve($request->user());
        abort_unless($ctx->isHQ, 403, 'Only brand administrators can edit rewards.');
        abort_unless($reward->brand_id === $ctx->brand->id, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'points_cost' => ['required', 'integer', 'min:1'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $reward->update([
            ...$validated,
            'active' => $request->boolean('active', true),
        ]);

        return redirect()->route('store.rewards.index')
            ->with('success', 'Reward updated successfully.');
    }

    public function destroy(Request $request, Reward $reward): RedirectResponse
    {
        $ctx = $this->context->resolve($request->user());
        abort_unless($ctx->isHQ, 403, 'Only brand administrators can delete rewards.');
        abort_unless($reward->brand_id === $ctx->brand->id, 403);

        $reward->delete();

        return redirect()->route('store.rewards.index')
            ->with('success', 'Reward deleted.');
    }
}
```

- [ ] **Step 5: Run scoping tests + existing tests**

Run: `php artisan test --compact tests/Feature/StoreOwner/`
Expected: All pass (new scoping tests + existing dashboard tests)

- [ ] **Step 6: Run pint and commit**

```bash
vendor/bin/pint --dirty
git add app/Http/Controllers/StoreOwner/DashboardController.php app/Http/Controllers/StoreOwner/RewardController.php tests/Feature/StoreOwner/StoreOwnerScopingTest.php
git commit -m "refactor: use StoreOwnerContext for HQ/branch scoping in web controllers"
```

---

## Task 3: Update Dashboard View for Multi-Outlet

**Files:**
- Modify: `resources/views/store-owner/dashboard.blade.php`
- Modify: `resources/views/components/layouts/store-owner.blade.php`

- [ ] **Step 1: Update dashboard view**

Replace the full content of `resources/views/store-owner/dashboard.blade.php`. Key changes:
- Brand welcome header shows brand name + outlet count (not single outlet)
- Outlet selector dropdown at top (links to `?outlet={id}`, "All Outlets" clears param)
- Bins section groups by outlet name
- Uses `$outlets` collection, `$selectedOutlet`, `$isHQ` from controller

The view should keep the same visual structure (stats grid, loyalty card, waste breakdown, bins list) but replace all `$outlet` references with `$selectedOutlet ?? $outlets->first()` for display, and use the already-aggregated stats from the controller.

- [ ] **Step 2: Update layout sidebar brand card**

In `resources/views/components/layouts/store-owner.blade.php`, update the brand card section to show outlet count:
- Change `{{ $currentOutlet?->name }}` to `{{ $authUser->id === $currentBrand?->user_id ? $currentBrand->outlets()->count() . ' outlets' : $currentOutlet?->name }}`
- Add "Staff" nav item: `['route' => 'store.staff.index', 'label' => 'Staff', 'icon' => 'heroicon-o-user-group']` — only show if HQ (`$authUser->id === $currentBrand?->user_id`)

- [ ] **Step 3: Verify dashboard renders correctly**

Run: `php artisan test --compact tests/Feature/StoreOwner/`
Expected: All pass

- [ ] **Step 4: Run pint and commit**

```bash
vendor/bin/pint --dirty
git add resources/views/store-owner/dashboard.blade.php resources/views/components/layouts/store-owner.blade.php
git commit -m "feat: update store owner dashboard for multi-outlet and HQ/branch views"
```

---

## Task 4: Staff Management

**Files:**
- Create: `app/Http/Controllers/StoreOwner/StaffController.php`
- Create: `resources/views/store-owner/staff/index.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/StoreOwner/StaffManagementTest.php`

- [ ] **Step 1: Write failing tests**

```php
<?php

use App\Enums\UserRole;
use App\Models\Brand;
use App\Models\Outlet;
use App\Models\User;

it('HQ user can view staff list', function () {
    $user = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $user->id]);
    $outlet = Outlet::factory()->create(['brand_id' => $brand->id]);

    $manager = User::factory()->storeOwner()->create();
    $outlet->managers()->attach($manager->id, ['role' => 'manager']);

    $response = $this->actingAs($user)->get(route('store.staff.index'));

    $response->assertOk()
        ->assertSee($manager->name)
        ->assertSee($outlet->name);
});

it('branch manager cannot access staff page', function () {
    $hqUser = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $hqUser->id]);
    $outlet = Outlet::factory()->create(['brand_id' => $brand->id]);

    $branchUser = User::factory()->storeOwner()->create();
    $outlet->managers()->attach($branchUser->id, ['role' => 'manager']);

    $response = $this->actingAs($branchUser)->get(route('store.staff.index'));

    $response->assertForbidden();
});

it('HQ can invite existing user as branch manager', function () {
    $user = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $user->id]);
    $outlet = Outlet::factory()->create(['brand_id' => $brand->id]);

    $existingUser = User::factory()->create(['roles' => ['public_user']]);

    $response = $this->actingAs($user)->post(route('store.staff.invite'), [
        'email' => $existingUser->email,
        'outlet_id' => $outlet->id,
    ]);

    $response->assertRedirect(route('store.staff.index'));
    expect($outlet->managers()->where('user_id', $existingUser->id)->exists())->toBeTrue();
    $existingUser->refresh();
    expect($existingUser->hasRole(UserRole::StoreOwner))->toBeTrue();
});

it('HQ can invite new user as branch manager', function () {
    $user = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $user->id]);
    $outlet = Outlet::factory()->create(['brand_id' => $brand->id]);

    $response = $this->actingAs($user)->post(route('store.staff.invite'), [
        'email' => 'newmanager@example.com',
        'outlet_id' => $outlet->id,
    ]);

    $response->assertRedirect(route('store.staff.index'));
    $newUser = User::where('email', 'newmanager@example.com')->first();
    expect($newUser)->not->toBeNull();
    expect($newUser->hasRole(UserRole::StoreOwner))->toBeTrue();
    expect($outlet->managers()->where('user_id', $newUser->id)->exists())->toBeTrue();
});

it('HQ can remove branch manager', function () {
    $user = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $user->id]);
    $outlet = Outlet::factory()->create(['brand_id' => $brand->id]);

    $manager = User::factory()->storeOwner()->create();
    $outlet->managers()->attach($manager->id, ['role' => 'manager']);

    $response = $this->actingAs($user)->delete(route('store.staff.remove', $manager));

    $response->assertRedirect(route('store.staff.index'));
    expect($outlet->managers()->where('user_id', $manager->id)->exists())->toBeFalse();
    $manager->refresh();
    expect($manager->hasRole(UserRole::StoreOwner))->toBeFalse();
});

it('cannot invite to outlet outside brand scope', function () {
    $user = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $user->id]);
    Outlet::factory()->create(['brand_id' => $brand->id]);

    $otherBrand = Brand::factory()->approved()->create();
    $otherOutlet = Outlet::factory()->create(['brand_id' => $otherBrand->id]);

    $response = $this->actingAs($user)->post(route('store.staff.invite'), [
        'email' => 'test@example.com',
        'outlet_id' => $otherOutlet->id,
    ]);

    $response->assertStatus(422);
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --compact tests/Feature/StoreOwner/StaffManagementTest.php`
Expected: FAIL — route not defined

- [ ] **Step 3: Add routes to web.php**

Inside the `store` group in `routes/web.php`, add:

```php
// Staff management (HQ only)
Route::get('staff', [\App\Http\Controllers\StoreOwner\StaffController::class, 'index'])->name('staff.index');
Route::post('staff/invite', [\App\Http\Controllers\StoreOwner\StaffController::class, 'invite'])->name('staff.invite');
Route::delete('staff/{user}', [\App\Http\Controllers\StoreOwner\StaffController::class, 'remove'])->name('staff.remove');
```

- [ ] **Step 4: Implement StaffController**

```php
<?php

namespace App\Http\Controllers\StoreOwner;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StoreOwnerContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function __construct(private StoreOwnerContext $context) {}

    public function index(Request $request): View
    {
        $ctx = $this->context->resolve($request->user());
        abort_unless($ctx->isHQ, 403, 'Only brand administrators can manage staff.');

        $outletIds = $ctx->outlets->pluck('id');

        // Get all managers grouped by outlet
        $staffByOutlet = $ctx->outlets->map(function ($outlet) {
            return [
                'outlet' => $outlet,
                'managers' => $outlet->managers()->get(),
            ];
        });

        $brand = $ctx->brand;
        $outlets = $ctx->outlets;

        return view('store-owner.staff.index', compact('brand', 'outlets', 'staffByOutlet'));
    }

    public function invite(Request $request): RedirectResponse
    {
        $ctx = $this->context->resolve($request->user());
        abort_unless($ctx->isHQ, 403, 'Only brand administrators can invite staff.');

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'outlet_id' => ['required', 'integer', 'exists:outlets,id'],
        ]);

        // Verify outlet belongs to this brand
        $outlet = $ctx->outlets->firstWhere('id', $validated['outlet_id']);
        if (! $outlet) {
            return back()->withErrors(['outlet_id' => 'This outlet does not belong to your brand.'])->withInput();
        }

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            $user = User::create([
                'name' => Str::before($validated['email'], '@'),
                'email' => $validated['email'],
                'password' => Hash::make(Str::random(32)),
                'roles' => ['public_user'],
            ]);

            Log::info('Branch manager account created — welcome email would be sent', [
                'user_id' => $user->id,
                'email' => $validated['email'],
                'brand_id' => $ctx->brand->id,
            ]);
        }

        // Attach to outlet if not already attached
        if (! $outlet->managers()->where('user_id', $user->id)->exists()) {
            $outlet->managers()->attach($user->id, ['role' => 'manager']);
        }

        // Ensure user has store_owner role
        $user->addRole(UserRole::StoreOwner);

        return redirect()->route('store.staff.index')
            ->with('success', "Invited {$user->email} as manager of {$outlet->name}.");
    }

    public function remove(Request $request, User $user): RedirectResponse
    {
        $ctx = $this->context->resolve($request->user());
        abort_unless($ctx->isHQ, 403, 'Only brand administrators can remove staff.');

        $outletIds = $ctx->outlets->pluck('id');

        // Detach from all of this brand's outlets
        $user->outlets()->detach($outletIds);

        // If user has no remaining outlet assignments, remove store_owner role
        if ($user->outlets()->count() === 0 && $user->id !== $ctx->brand->user_id) {
            $user->removeRole(UserRole::StoreOwner);
        }

        return redirect()->route('store.staff.index')
            ->with('success', "Removed {$user->name} from staff.");
    }
}
```

- [ ] **Step 5: Create staff view**

Create `resources/views/store-owner/staff/index.blade.php` using the store-owner layout. Shows:
- Page header "Staff Management" with brand name
- Invite form card: email input + outlet dropdown + "Invite" button
- For each outlet: section header with outlet name, table/list of managers (name, email, joined date, remove button with confirmation)
- Empty state per outlet if no managers

- [ ] **Step 6: Run tests and verify they pass**

Run: `php artisan test --compact tests/Feature/StoreOwner/StaffManagementTest.php`
Expected: All 6 tests PASS

- [ ] **Step 7: Run pint and commit**

```bash
vendor/bin/pint --dirty
git add app/Http/Controllers/StoreOwner/StaffController.php resources/views/store-owner/staff/index.blade.php routes/web.php tests/Feature/StoreOwner/StaffManagementTest.php
git commit -m "feat: add staff management for store owner HQ (invite/remove branch managers)"
```

---

## Task 5: Store Owner API Endpoints

**Files:**
- Create: `app/Http/Controllers/Api/StoreOwner/DashboardController.php`
- Create: `app/Http/Controllers/Api/StoreOwner/OutletController.php`
- Create: `app/Http/Controllers/Api/StoreOwner/RewardController.php`
- Create: `app/Http/Resources/StoreOwner/DashboardResource.php`
- Create: `app/Http/Resources/StoreOwner/OutletResource.php`
- Create: `app/Http/Resources/StoreOwner/BrandResource.php`
- Modify: `routes/api.php`
- Test: `tests/Feature/Api/StoreOwnerApiTest.php`

- [ ] **Step 1: Write failing tests for API endpoints**

```php
<?php

use App\Models\Bin;
use App\Models\BinAssignment;
use App\Models\Brand;
use App\Models\DetectionEvent;
use App\Models\Outlet;
use App\Models\Reward;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->hqUser = User::factory()->storeOwner()->create();
    $this->brand = Brand::factory()->approved()->create(['user_id' => $this->hqUser->id]);
    $this->outlet1 = Outlet::factory()->create(['brand_id' => $this->brand->id]);
    $this->outlet2 = Outlet::factory()->create(['brand_id' => $this->brand->id]);
    $this->bin1 = Bin::factory()->create();
    $this->bin2 = Bin::factory()->create();
    BinAssignment::create(['bin_id' => $this->bin1->id, 'outlet_id' => $this->outlet1->id, 'assigned_at' => now()]);
    BinAssignment::create(['bin_id' => $this->bin2->id, 'outlet_id' => $this->outlet2->id, 'assigned_at' => now()]);

    $this->branchUser = User::factory()->storeOwner()->create();
    $this->outlet1->managers()->attach($this->branchUser->id, ['role' => 'manager']);
});

it('GET /store-owner/dashboard returns aggregated stats for HQ', function () {
    Sanctum::actingAs($this->hqUser);
    DetectionEvent::factory()->create(['bin_id' => $this->bin1->id, 'detected_at' => now()]);
    DetectionEvent::factory()->create(['bin_id' => $this->bin2->id, 'detected_at' => now()]);

    $response = $this->getJson('/api/v1/store-owner/dashboard');

    $response->assertOk()
        ->assertJsonPath('data.today_detections', 2)
        ->assertJsonStructure(['data' => ['today_detections', 'week_detections', 'month_detections', 'unique_recyclers', 'redemption_count', 'active_rewards', 'brand_loyalty']]);
});

it('GET /store-owner/dashboard returns scoped stats for branch', function () {
    Sanctum::actingAs($this->branchUser);
    DetectionEvent::factory()->create(['bin_id' => $this->bin1->id, 'detected_at' => now()]);
    DetectionEvent::factory()->create(['bin_id' => $this->bin2->id, 'detected_at' => now()]);

    $response = $this->getJson('/api/v1/store-owner/dashboard');

    $response->assertOk()
        ->assertJsonPath('data.today_detections', 1);
});

it('GET /store-owner/brand returns brand profile', function () {
    Sanctum::actingAs($this->hqUser);

    $response = $this->getJson('/api/v1/store-owner/brand');

    $response->assertOk()
        ->assertJsonPath('data.name', $this->brand->name)
        ->assertJsonStructure(['data' => ['id', 'name', 'slug', 'primary_color', 'points_multiplier', 'rewards_budget']]);
});

it('GET /store-owner/outlets returns all outlets for HQ', function () {
    Sanctum::actingAs($this->hqUser);

    $response = $this->getJson('/api/v1/store-owner/outlets');

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});

it('GET /store-owner/outlets returns assigned outlets for branch', function () {
    Sanctum::actingAs($this->branchUser);

    $response = $this->getJson('/api/v1/store-owner/outlets');

    $response->assertOk()
        ->assertJsonCount(1, 'data');
});

it('GET /store-owner/outlets/{id} returns outlet detail', function () {
    Sanctum::actingAs($this->hqUser);

    $response = $this->getJson("/api/v1/store-owner/outlets/{$this->outlet1->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $this->outlet1->id)
        ->assertJsonStructure(['data' => ['id', 'name', 'address', 'bins']]);
});

it('GET /store-owner/outlets/{id} rejects out-of-scope outlet for branch', function () {
    Sanctum::actingAs($this->branchUser);

    $response = $this->getJson("/api/v1/store-owner/outlets/{$this->outlet2->id}");

    $response->assertForbidden();
});

it('GET /store-owner/bins returns all scoped bins', function () {
    Sanctum::actingAs($this->hqUser);

    $response = $this->getJson('/api/v1/store-owner/bins');

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});

it('GET /store-owner/rewards returns brand rewards', function () {
    Sanctum::actingAs($this->hqUser);
    Reward::factory()->create(['brand_id' => $this->brand->id]);

    $response = $this->getJson('/api/v1/store-owner/rewards');

    $response->assertOk()
        ->assertJsonCount(1, 'data');
});

it('POST /store-owner/rewards creates reward for HQ', function () {
    Sanctum::actingAs($this->hqUser);

    $response = $this->postJson('/api/v1/store-owner/rewards', [
        'name' => 'Free Coffee',
        'points_cost' => 100,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Free Coffee');
    expect($this->brand->rewards()->count())->toBe(1);
});

it('POST /store-owner/rewards returns 403 for branch manager', function () {
    Sanctum::actingAs($this->branchUser);

    $response = $this->postJson('/api/v1/store-owner/rewards', [
        'name' => 'Free Coffee',
        'points_cost' => 100,
    ]);

    $response->assertForbidden();
});

it('returns 401 for unauthenticated requests', function () {
    $this->getJson('/api/v1/store-owner/dashboard')->assertUnauthorized();
});

it('returns 403 for non-store-owner role', function () {
    Sanctum::actingAs(User::factory()->create(['roles' => ['public_user']]));

    $this->getJson('/api/v1/store-owner/dashboard')->assertForbidden();
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --compact tests/Feature/Api/StoreOwnerApiTest.php`
Expected: FAIL — routes not defined

- [ ] **Step 3: Add store-owner API routes to api.php**

Inside the `auth:sanctum` middleware group in `routes/api.php`, add:

```php
// Store owner APIs
Route::middleware('role:store_owner')->prefix('store-owner')->name('store-owner.')->group(function (): void {
    Route::get('dashboard', [\App\Http\Controllers\Api\StoreOwner\DashboardController::class, 'index'])->name('dashboard');
    Route::get('brand', [\App\Http\Controllers\Api\StoreOwner\DashboardController::class, 'brand'])->name('brand');
    Route::get('analytics', [\App\Http\Controllers\Api\StoreOwner\DashboardController::class, 'analytics'])->name('analytics');
    Route::get('outlets', [\App\Http\Controllers\Api\StoreOwner\OutletController::class, 'index'])->name('outlets.index');
    Route::get('outlets/{outlet}', [\App\Http\Controllers\Api\StoreOwner\OutletController::class, 'show'])->name('outlets.show');
    Route::get('bins', [\App\Http\Controllers\Api\StoreOwner\OutletController::class, 'bins'])->name('bins.index');
    Route::get('rewards', [\App\Http\Controllers\Api\StoreOwner\RewardController::class, 'index'])->name('rewards.index');
    Route::post('rewards', [\App\Http\Controllers\Api\StoreOwner\RewardController::class, 'store'])->name('rewards.store');
    Route::put('rewards/{reward}', [\App\Http\Controllers\Api\StoreOwner\RewardController::class, 'update'])->name('rewards.update');
    Route::delete('rewards/{reward}', [\App\Http\Controllers\Api\StoreOwner\RewardController::class, 'destroy'])->name('rewards.destroy');
    Route::get('staff', [\App\Http\Controllers\Api\StoreOwner\DashboardController::class, 'staff'])->name('staff.index');
    Route::post('staff/invite', [\App\Http\Controllers\Api\StoreOwner\DashboardController::class, 'inviteStaff'])->name('staff.invite');
    Route::delete('staff/{user}', [\App\Http\Controllers\Api\StoreOwner\DashboardController::class, 'removeStaff'])->name('staff.remove');
});
```

- [ ] **Step 4: Create API resources**

Create `app/Http/Resources/StoreOwner/DashboardResource.php`, `OutletResource.php`, `BrandResource.php`. Each wraps the data from StoreOwnerContext into a standard JSON API response with snake_case keys.

DashboardResource wraps: today_detections, week_detections, month_detections, unique_recyclers, redemption_count, active_rewards, brand_loyalty (object with match/competitor/unidentified), waste_breakdown.

OutletResource wraps: id, name, address, latitude, longitude, contact_name, contact_phone, bins (nested array of bin summaries: id, serial_number, fill_level, status, last_seen_at), today_detections count.

BrandResource wraps: id, name, slug, logo_path, primary_color, points_multiplier, rewards_budget, status.

- [ ] **Step 5: Implement API controllers**

Create `app/Http/Controllers/Api/StoreOwner/DashboardController.php` — mirrors the web DashboardController logic but returns JSON via API resources. Methods: `index()` (dashboard stats), `brand()` (brand profile), `analytics()` (time series), `staff()` (staff list), `inviteStaff()`, `removeStaff()`. All inject `StoreOwnerContext`.

Create `app/Http/Controllers/Api/StoreOwner/OutletController.php` — `index()` returns scoped outlets, `show(Outlet $outlet)` validates scope then returns detail, `bins()` returns all bins across scope.

Create `app/Http/Controllers/Api/StoreOwner/RewardController.php` — mirrors web RewardController with JSON responses. HQ gate on mutations.

- [ ] **Step 6: Run API tests**

Run: `php artisan test --compact tests/Feature/Api/StoreOwnerApiTest.php`
Expected: All 13 tests PASS

- [ ] **Step 7: Run pint and commit**

```bash
vendor/bin/pint --dirty
git add app/Http/Controllers/Api/StoreOwner/ app/Http/Resources/StoreOwner/ routes/api.php tests/Feature/Api/StoreOwnerApiTest.php
git commit -m "feat: add store-owner API endpoints with HQ/branch scoping"
```

---

## Task 6: Admin Brand Monitoring

**Files:**
- Create: `app/Http/Controllers/Admin/BrandMonitoringController.php`
- Create: `resources/views/admin/brands/index.blade.php`
- Create: `resources/views/admin/brands/show.blade.php`
- Modify: `resources/views/components/admin/sidebar.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Admin/BrandMonitoringTest.php`

- [ ] **Step 1: Write failing tests**

```php
<?php

use App\Models\Bin;
use App\Models\BinAssignment;
use App\Models\Brand;
use App\Models\DetectionEvent;
use App\Models\Outlet;
use App\Models\User;

it('admin can view brands list', function () {
    $admin = User::factory()->admin()->create();
    $brand = Brand::factory()->approved()->create();
    Outlet::factory()->count(2)->create(['brand_id' => $brand->id]);

    $response = $this->actingAs($admin)->get(route('admin.brands.index'));

    $response->assertOk()
        ->assertSee($brand->name);
});

it('admin can view brand detail with stats', function () {
    $admin = User::factory()->admin()->create();
    $hqUser = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $hqUser->id]);
    $outlet = Outlet::factory()->create(['brand_id' => $brand->id]);
    $bin = Bin::factory()->create();
    BinAssignment::create(['bin_id' => $bin->id, 'outlet_id' => $outlet->id, 'assigned_at' => now()]);
    DetectionEvent::factory()->create(['bin_id' => $bin->id, 'detected_at' => now()]);

    $response = $this->actingAs($admin)->get(route('admin.brands.show', $brand));

    $response->assertOk()
        ->assertSee($brand->name)
        ->assertSee($outlet->name)
        ->assertSee($hqUser->name);
});

it('admin can see brand staff (outlet managers)', function () {
    $admin = User::factory()->admin()->create();
    $brand = Brand::factory()->approved()->create();
    $outlet = Outlet::factory()->create(['brand_id' => $brand->id]);
    $manager = User::factory()->storeOwner()->create();
    $outlet->managers()->attach($manager->id, ['role' => 'manager']);

    $response = $this->actingAs($admin)->get(route('admin.brands.show', $brand));

    $response->assertOk()
        ->assertSee($manager->name);
});

it('non-admin cannot access brand monitoring', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('admin.brands.index'))->assertForbidden();
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --compact tests/Feature/Admin/BrandMonitoringTest.php`
Expected: FAIL — route not defined

- [ ] **Step 3: Add admin routes**

Inside the admin route group in `routes/web.php`, add:

```php
// Brand monitoring
Route::get('brands', [\App\Http\Controllers\Admin\BrandMonitoringController::class, 'index'])->name('brands.index');
Route::get('brands/{brand}', [\App\Http\Controllers\Admin\BrandMonitoringController::class, 'show'])->name('brands.show');
```

- [ ] **Step 4: Implement BrandMonitoringController**

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\DetectionEvent;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandMonitoringController extends Controller
{
    public function index(Request $request): View
    {
        $brands = Brand::query()
            ->withCount('outlets', 'rewards', 'redemptions')
            ->when($request->input('status'), fn ($q, $status) => $q->where('status', $status))
            ->orderBy('name')
            ->paginate(20);

        return view('admin.brands.index', compact('brands'));
    }

    public function show(Brand $brand): View
    {
        $brand->load(['outlets.bins', 'outlets.managers', 'rewards', 'adminUser']);

        $binIds = $brand->outlets->flatMap(fn ($o) => $o->bins->pluck('id'))->all();

        $stats = [
            'total_outlets' => $brand->outlets->count(),
            'total_bins' => count($binIds),
            'total_staff' => $brand->outlets->sum(fn ($o) => $o->managers->count()),
            'today_detections' => $binIds ? DetectionEvent::whereIn('bin_id', $binIds)->whereDate('detected_at', today())->count() : 0,
            'month_detections' => $binIds ? DetectionEvent::whereIn('bin_id', $binIds)->where('detected_at', '>=', now()->startOfMonth())->count() : 0,
            'active_rewards' => $brand->rewards->where('active', true)->count(),
        ];

        return view('admin.brands.show', compact('brand', 'stats'));
    }
}
```

- [ ] **Step 5: Create admin brand views**

`admin/brands/index.blade.php`: Table with columns: Brand Name, Status badge, Outlets count, Rewards count, Redemptions count, HQ Admin name/email, Actions (View). Status filter tabs at top (All / Approved / Pending / Rejected). Uses admin layout.

`admin/brands/show.blade.php`: Brand header card (name, logo, status, multiplier, budget, HQ admin info). Stats grid (outlets, bins, staff, detections today, detections month, active rewards). Outlets section: list each outlet with its managers and bins. Rewards section: list active rewards. Uses admin layout.

- [ ] **Step 6: Add Brands nav item to admin sidebar**

In `resources/views/components/admin/sidebar.blade.php`, add a "Brands" nav item after "Users" using the existing `<x-admin.sidebar-item>` component pattern. Route: `admin.brands.index`, icon: `heroicon-o-building-storefront`, no badge needed.

- [ ] **Step 7: Run tests**

Run: `php artisan test --compact tests/Feature/Admin/BrandMonitoringTest.php`
Expected: All 4 tests PASS

- [ ] **Step 8: Run pint and commit**

```bash
vendor/bin/pint --dirty
git add app/Http/Controllers/Admin/BrandMonitoringController.php resources/views/admin/brands/ resources/views/components/admin/sidebar.blade.php routes/web.php tests/Feature/Admin/BrandMonitoringTest.php
git commit -m "feat: add admin brand monitoring (list, detail with stats/staff/outlets)"
```

---

## Task 7: Update Existing Tests + Full Suite Verification

**Files:**
- Modify: `tests/Feature/StoreOwner/StoreOwnerDashboardTest.php`

- [ ] **Step 1: Fix existing store owner tests for new scoping**

The existing tests create a user, outlet, brand, and attach via `$outlet->managers()->attach()`. These tests will need the Brand's `user_id` set to the test user to maintain HQ behavior (or the test expectations updated for branch behavior). Review each existing test and ensure:
- Tests that create rewards set `Brand.user_id` to the acting user (HQ)
- Dashboard tests have proper outlet/brand/bin setup
- No tests rely on the old `$outlet` single-variable pattern (views now pass `$outlets` collection)

- [ ] **Step 2: Run the full store owner test suite**

Run: `php artisan test --compact tests/Feature/StoreOwner/`
Expected: All tests pass

- [ ] **Step 3: Run the full test suite**

Run: `php artisan test --compact`
Expected: All 530+ tests pass (no regressions)

- [ ] **Step 4: Run pint on all dirty files**

```bash
vendor/bin/pint --dirty
```

- [ ] **Step 5: Final commit if any test fixes needed**

```bash
git add -A
git commit -m "fix: update existing store owner tests for new HQ/branch scoping"
```
