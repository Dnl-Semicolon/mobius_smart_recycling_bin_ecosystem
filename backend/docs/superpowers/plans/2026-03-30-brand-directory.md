# Brand Directory Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a brand catalog/directory with searchable registration flow, so brand representatives can claim their brand or request a new one.

**Architecture:** New `brand_applications` table separates "who wants to own a brand" from "brands that exist." ~18 F&B brands seeded as unclaimed catalog entries. Registration page redesigned with Alpine.js searchable dropdown. Admin gets a Brand Directory page + updated Applications page.

**Tech Stack:** Laravel 12, Blade, Alpine.js, Tailwind v4, Pest

**Spec:** `docs/superpowers/specs/2026-03-30-brand-directory-design.md`

---

## File Map

### New files:
| File | Purpose |
|------|---------|
| `database/migrations/XXXX_create_brand_applications_table.php` | Migration |
| `app/Models/BrandApplication.php` | Eloquent model |
| `app/Http/Requests/StoreBrandApplicationRequest.php` | Validation for both claim + new brand flows |
| `app/Http/Requests/ApproveBrandApplicationRequest.php` | Admin approve validation |
| `app/Http/Controllers/Admin/BrandDirectoryController.php` | Admin CRUD for brand catalog |
| `resources/views/registration/brand.blade.php` | Redesigned registration page (overwrite) |
| `resources/views/admin/brand-directory/index.blade.php` | Admin brand catalog page |
| `resources/views/admin/brand-directory/edit.blade.php` | Admin edit brand form |
| `resources/views/admin/applications/brands/index.blade.php` | Updated to use BrandApplication (overwrite) |
| `resources/views/admin/applications/brands/show.blade.php` | Updated show page (overwrite) |
| `tests/Feature/Registration/BrandRegistrationTest.php` | Rewritten for new flows (overwrite) |
| `tests/Feature/Admin/BrandApplicationTest.php` | Admin approval tests |

### Modified files:
| File | Changes |
|------|---------|
| `app/Services/ApplicationService.php` | Add `registerBrandClaim()`, `registerNewBrand()`, `approveBrandApplication()`, `rejectBrandApplication()` |
| `app/Http/Controllers/Registration/BrandRegistrationController.php` | Handle both flows, add brand search endpoint |
| `app/Http/Controllers/Admin/ApplicationController.php` | Switch to BrandApplication queries |
| `resources/views/components/admin/sidebar.blade.php` | Add Brand Directory link, update Applications badge |
| `routes/web.php` | Add brand directory routes, brand search route |

---

### Task 1: Create BrandApplication Migration + Model

**Files:**
- Create: `database/migrations/XXXX_create_brand_applications_table.php`
- Create: `app/Models/BrandApplication.php`

- [ ] **Step 1: Generate migration**

```bash
php artisan make:migration create_brand_applications_table --no-interaction
```

- [ ] **Step 2: Write migration schema**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brand_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->string('brand_name');
            $table->text('description')->nullable();
            $table->string('website_url')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('contact_person');
            $table->string('contact_email');
            $table->string('contact_phone')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_applications');
    }
};
```

- [ ] **Step 3: Generate model**

```bash
php artisan make:class App/Models/BrandApplication --no-interaction
```

- [ ] **Step 4: Write BrandApplication model**

```php
<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrandApplication extends Model
{
    protected $fillable = [
        'brand_id',
        'user_id',
        'status',
        'brand_name',
        'description',
        'website_url',
        'logo_path',
        'contact_person',
        'contact_email',
        'contact_phone',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isClaimingExisting(): bool
    {
        return $this->brand_id !== null;
    }

    public function scopePending(Builder $query): void
    {
        $query->where('status', ApplicationStatus::Pending);
    }

    public function scopeApproved(Builder $query): void
    {
        $query->where('status', ApplicationStatus::Approved);
    }

    public function scopeRejected(Builder $query): void
    {
        $query->where('status', ApplicationStatus::Rejected);
    }
}
```

- [ ] **Step 5: Run migration**

```bash
php artisan migrate --no-interaction
```

Expected: Migration runs, `brand_applications` table created.

- [ ] **Step 6: Run pint + commit**

```bash
vendor/bin/pint --dirty
```

Commit: `feat: add brand_applications migration and model`

---

### Task 2: Seed Catalog Brands

**Files:**
- Create: `database/migrations/XXXX_seed_brand_directory_catalog.php`

- [ ] **Step 1: Generate migration**

```bash
php artisan make:migration seed_brand_directory_catalog --no-interaction
```

- [ ] **Step 2: Write seed migration**

This inserts brands that don't already exist (by slug). Existing brands (ZUS, Starbucks, MIXUE) are skipped.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $brands = [
            ['name' => 'Tealive', 'primary_color' => '#E91E63', 'description' => "Malaysia's largest lifestyle tea brand with 800+ outlets."],
            ['name' => 'Gong Cha', 'primary_color' => '#8B4513', 'description' => 'Premium tea brand from Taiwan specializing in artisan teas.'],
            ['name' => 'Tiger Sugar', 'primary_color' => '#F5A623', 'description' => 'Taiwanese brown sugar boba tea chain.'],
            ['name' => 'Daboba', 'primary_color' => '#FF6B6B', 'description' => 'Popular bubble tea brand across Southeast Asia.'],
            ['name' => 'CoolBlog', 'primary_color' => '#00BCD4', 'description' => 'Malaysian frozen yogurt and smoothie chain.'],
            ['name' => 'Boost Juice', 'primary_color' => '#FF5722', 'description' => 'Australian juice and smoothie bar franchise.'],
            ['name' => 'The Alley', 'primary_color' => '#2C2C2C', 'description' => 'Taiwanese handcrafted tea and bubble tea brand.'],
            ['name' => 'Chatime', 'primary_color' => '#6A1B9A', 'description' => 'Global bubble tea franchise originating from Taiwan.'],
            ['name' => 'Bask Bear Coffee', 'primary_color' => '#8D6E63', 'description' => 'Malaysian specialty coffee chain with affordable pricing.'],
            ['name' => 'Lucky Cup', 'primary_color' => '#FFD700', 'description' => 'Bubble tea and fruit tea chain in Malaysia.'],
            ['name' => 'Luckin Coffee', 'primary_color' => '#003DA5', 'description' => 'Chinese technology-driven coffee chain expanding across Asia.'],
            ['name' => 'Tutti Frutti', 'primary_color' => '#FF69B4', 'description' => 'Self-serve frozen yogurt chain with a variety of toppings.'],
            ['name' => 'Inside Scoop', 'primary_color' => '#F06292', 'description' => 'Malaysian artisan ice cream brand with unique local flavours.'],
            ['name' => 'Llaollao', 'primary_color' => '#7CB342', 'description' => 'Spanish frozen yogurt chain popular in Malaysia.'],
            ['name' => 'Secret Recipe', 'primary_color' => '#B71C1C', 'description' => 'Malaysian lifestyle cafe chain known for cakes and meals.'],
        ];

        foreach ($brands as $brand) {
            $slug = Str::slug($brand['name']);

            if (DB::table('brands')->where('slug', $slug)->exists()) {
                continue;
            }

            DB::table('brands')->insert([
                'name' => $brand['name'],
                'slug' => $slug,
                'primary_color' => $brand['primary_color'],
                'description' => $brand['description'],
                'points_multiplier' => 1.00,
                'rewards_budget' => 0,
                'active' => true,
                'status' => 'approved',
                'user_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $slugs = [
            'tealive', 'gong-cha', 'tiger-sugar', 'daboba', 'coolblog',
            'boost-juice', 'the-alley', 'chatime', 'bask-bear-coffee',
            'lucky-cup', 'luckin-coffee', 'tutti-frutti', 'inside-scoop',
            'llaollao', 'secret-recipe',
        ];

        DB::table('brands')->whereIn('slug', $slugs)->whereNull('user_id')->delete();
    }
};
```

- [ ] **Step 3: Run migration**

```bash
php artisan migrate --no-interaction
```

- [ ] **Step 4: Verify**

```bash
php artisan tinker --execute="echo App\Models\Brand::count() . ' brands total, ' . App\Models\Brand::whereNull('user_id')->count() . ' unclaimed catalog entries';"
```

Expected: `18 brands total, 18 unclaimed catalog entries`

- [ ] **Step 5: Commit**

Commit: `feat: seed 15 F&B brands into brand directory catalog`

---

### Task 3: ApplicationService — Brand Application Methods

**Files:**
- Modify: `app/Services/ApplicationService.php`
- Create: `tests/Feature/Admin/BrandApplicationTest.php`

- [ ] **Step 1: Write failing tests**

Create `tests/Feature/Admin/BrandApplicationTest.php`:

```php
<?php

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Brand;
use App\Models\BrandApplication;
use App\Models\User;
use App\Services\ApplicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('registers a brand claim for an existing catalog brand', function () {
    $brand = Brand::factory()->create(['user_id' => null, 'status' => 'approved']);
    $service = app(ApplicationService::class);

    $application = $service->registerBrandClaim([
        'brand_id' => $brand->id,
        'contact_person' => 'Alice Wong',
        'email' => 'alice@starbucks.my',
        'phone' => '+60123456789',
        'password' => 'SecureP@ss1',
    ]);

    expect($application)->toBeInstanceOf(BrandApplication::class)
        ->and($application->brand_id)->toBe($brand->id)
        ->and($application->brand_name)->toBe($brand->name)
        ->and($application->status)->toBe(ApplicationStatus::Pending);

    $user = User::where('email', 'alice@starbucks.my')->first();
    expect($user)->not->toBeNull()
        ->and($application->user_id)->toBe($user->id);

    // Brand should remain unclaimed
    expect($brand->fresh()->user_id)->toBeNull();
});

it('registers a new brand request', function () {
    $service = app(ApplicationService::class);

    $application = $service->registerNewBrand([
        'brand_name' => 'Awesome Tea Co',
        'description' => 'New bubble tea brand',
        'website_url' => 'https://awesometea.com',
        'contact_person' => 'Bob Lee',
        'email' => 'bob@awesometea.com',
        'phone' => '+60198765432',
        'password' => 'SecureP@ss1',
    ]);

    expect($application)->toBeInstanceOf(BrandApplication::class)
        ->and($application->brand_id)->toBeNull()
        ->and($application->brand_name)->toBe('Awesome Tea Co')
        ->and($application->status)->toBe(ApplicationStatus::Pending);
});

it('approves a brand claim and sets user_id on brand', function () {
    $brand = Brand::factory()->create(['user_id' => null, 'status' => 'approved']);
    $applicantUser = User::factory()->create(['roles' => ['public_user']]);
    $admin = User::factory()->create(['roles' => ['admin']]);

    $application = BrandApplication::create([
        'brand_id' => $brand->id,
        'user_id' => $applicantUser->id,
        'status' => 'pending',
        'brand_name' => $brand->name,
        'contact_person' => 'Alice',
        'contact_email' => $applicantUser->email,
    ]);

    $service = app(ApplicationService::class);
    $service->approveBrandApplication($application, $admin, [
        'points_multiplier' => 1.50,
        'rewards_budget' => 10000,
    ]);

    expect($brand->fresh()->user_id)->toBe($applicantUser->id)
        ->and($application->fresh()->status)->toBe(ApplicationStatus::Approved)
        ->and($applicantUser->fresh()->hasRole(UserRole::StoreOwner))->toBeTrue();
});

it('approves a new brand request and creates the brand', function () {
    $applicantUser = User::factory()->create(['roles' => ['public_user']]);
    $admin = User::factory()->create(['roles' => ['admin']]);

    $application = BrandApplication::create([
        'brand_id' => null,
        'user_id' => $applicantUser->id,
        'status' => 'pending',
        'brand_name' => 'New Brand Co',
        'description' => 'A brand new brand',
        'website_url' => 'https://newbrand.com',
        'contact_person' => 'Charlie',
        'contact_email' => $applicantUser->email,
    ]);

    $service = app(ApplicationService::class);
    $service->approveBrandApplication($application, $admin, [
        'points_multiplier' => 1.30,
        'rewards_budget' => 5000,
    ]);

    $newBrand = Brand::where('name', 'New Brand Co')->first();
    expect($newBrand)->not->toBeNull()
        ->and($newBrand->user_id)->toBe($applicantUser->id)
        ->and($newBrand->status)->toBe(ApplicationStatus::Approved)
        ->and($newBrand->active)->toBeTrue()
        ->and($application->fresh()->status)->toBe(ApplicationStatus::Approved)
        ->and($application->fresh()->brand_id)->toBe($newBrand->id);
});

it('rejects a brand application', function () {
    $applicantUser = User::factory()->create(['roles' => ['public_user']]);
    $admin = User::factory()->create(['roles' => ['admin']]);

    $application = BrandApplication::create([
        'brand_id' => null,
        'user_id' => $applicantUser->id,
        'status' => 'pending',
        'brand_name' => 'Sketchy Brand',
        'contact_person' => 'Dan',
        'contact_email' => $applicantUser->email,
    ]);

    $service = app(ApplicationService::class);
    $service->rejectBrandApplication($application, $admin, 'Insufficient documentation');

    expect($application->fresh()->status)->toBe(ApplicationStatus::Rejected)
        ->and($application->fresh()->rejection_reason)->toBe('Insufficient documentation');
});
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test --compact --filter=BrandApplicationTest
```

Expected: All fail — methods don't exist yet.

- [ ] **Step 3: Add methods to ApplicationService**

Add these methods to `app/Services/ApplicationService.php` (after the existing `registerBrand` method):

```php
public function registerBrandClaim(array $data): BrandApplication
{
    return DB::transaction(function () use ($data) {
        $brand = Brand::findOrFail($data['brand_id']);

        $user = User::create([
            'name' => $data['contact_person'],
            'email' => $data['email'],
            'password' => $data['password'],
            'roles' => ['public_user'],
        ]);

        $application = BrandApplication::create([
            'brand_id' => $brand->id,
            'user_id' => $user->id,
            'status' => ApplicationStatus::Pending,
            'brand_name' => $brand->name,
            'contact_person' => $data['contact_person'],
            'contact_email' => $data['email'],
            'contact_phone' => $data['phone'] ?? null,
        ]);

        $this->notifications->notifyWelcome($user);

        Log::info('Brand claim submitted', [
            'application_id' => $application->id,
            'brand_id' => $brand->id,
            'email' => $data['email'],
        ]);

        return $application;
    });
}

public function registerNewBrand(array $data): BrandApplication
{
    return DB::transaction(function () use ($data) {
        $user = User::create([
            'name' => $data['contact_person'],
            'email' => $data['email'],
            'password' => $data['password'],
            'roles' => ['public_user'],
        ]);

        $application = BrandApplication::create([
            'brand_id' => null,
            'user_id' => $user->id,
            'status' => ApplicationStatus::Pending,
            'brand_name' => $data['brand_name'],
            'description' => $data['description'] ?? null,
            'website_url' => $data['website_url'] ?? null,
            'logo_path' => $data['logo_path'] ?? null,
            'contact_person' => $data['contact_person'],
            'contact_email' => $data['email'],
            'contact_phone' => $data['phone'] ?? null,
        ]);

        $this->notifications->notifyWelcome($user);

        Log::info('New brand request submitted', [
            'application_id' => $application->id,
            'brand_name' => $data['brand_name'],
            'email' => $data['email'],
        ]);

        return $application;
    });
}

public function approveBrandApplication(BrandApplication $application, User $admin, array $config): BrandApplication
{
    return DB::transaction(function () use ($application, $admin, $config) {
        if ($application->isClaimingExisting()) {
            // Claim existing brand — set user_id + update config
            $application->brand->update([
                'user_id' => $application->user_id,
                'points_multiplier' => $config['points_multiplier'],
                'rewards_budget' => $config['rewards_budget'],
            ]);
        } else {
            // New brand — create it from application data
            $brand = Brand::create([
                'name' => $application->brand_name,
                'description' => $application->description,
                'website_url' => $application->website_url,
                'logo_path' => $application->logo_path,
                'status' => ApplicationStatus::Approved,
                'active' => true,
                'points_multiplier' => $config['points_multiplier'],
                'rewards_budget' => $config['rewards_budget'],
                'user_id' => $application->user_id,
            ]);

            $application->brand_id = $brand->id;
        }

        $application->update([
            'status' => ApplicationStatus::Approved,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $application->user->addRole(UserRole::StoreOwner);

        $this->notifications->notifySystem(
            $application->user,
            'Brand Application Approved',
            "Your application for \"{$application->brand_name}\" has been approved! You can now manage your brand."
        );

        Log::info('Brand application approved', [
            'application_id' => $application->id,
            'approved_by' => $admin->id,
        ]);

        return $application;
    });
}

public function rejectBrandApplication(BrandApplication $application, User $admin, string $reason): BrandApplication
{
    $application->update([
        'status' => ApplicationStatus::Rejected,
        'rejection_reason' => $reason,
        'reviewed_by' => $admin->id,
        'reviewed_at' => now(),
    ]);

    $this->notifications->notifySystem(
        $application->user,
        'Brand Application Update',
        "Your application for \"{$application->brand_name}\" was not approved. Reason: {$reason}"
    );

    Log::info('Brand application rejected', [
        'application_id' => $application->id,
        'rejected_by' => $admin->id,
    ]);

    return $application;
}
```

Add `use App\Models\BrandApplication;` to the imports.

- [ ] **Step 4: Run tests**

```bash
php artisan test --compact --filter=BrandApplicationTest
```

Expected: All 5 tests pass.

- [ ] **Step 5: Run pint + commit**

```bash
vendor/bin/pint --dirty
```

Commit: `feat: add brand application service methods for claim and new brand flows`

---

### Task 4: Brand Search Route + Registration Controller

**Files:**
- Create: `app/Http/Requests/StoreBrandApplicationRequest.php`
- Modify: `app/Http/Controllers/Registration/BrandRegistrationController.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Create form request**

```bash
php artisan make:request StoreBrandApplicationRequest --no-interaction
```

Write `app/Http/Requests/StoreBrandApplicationRequest.php`:

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreBrandApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $rules = [
            'flow' => ['required', 'in:claim,new'],
            'contact_person' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];

        if ($this->input('flow') === 'claim') {
            $rules['brand_id'] = ['required', 'exists:brands,id'];
        } else {
            $rules['brand_name'] = ['required', 'string', 'max:255'];
            $rules['description'] = ['nullable', 'string', 'max:1000'];
            $rules['website_url'] = ['nullable', 'url', 'max:255'];
            $rules['logo'] = ['nullable', 'image', 'max:2048'];
        }

        return $rules;
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'brand_id.required' => 'Please select a brand from the directory.',
            'brand_id.exists' => 'The selected brand is not in our directory.',
            'brand_name.required' => 'A brand name is required for new brand requests.',
            'email.unique' => 'This email is already registered.',
            'password.confirmed' => 'The passwords do not match.',
        ];
    }
}
```

- [ ] **Step 2: Rewrite BrandRegistrationController**

Replace `app/Http/Controllers/Registration/BrandRegistrationController.php`:

```php
<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBrandApplicationRequest;
use App\Models\Brand;
use App\Models\BrandApplication;
use App\Services\ApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandRegistrationController extends Controller
{
    public function create(): View
    {
        return view('registration.brand');
    }

    public function store(StoreBrandApplicationRequest $request, ApplicationService $service): RedirectResponse
    {
        $data = $request->validated();

        if ($data['flow'] === 'claim') {
            $service->registerBrandClaim($data);
        } else {
            if ($request->hasFile('logo')) {
                $data['logo_path'] = $request->file('logo')->store('brands', 'public');
            }
            $service->registerNewBrand($data);
        }

        return redirect()->route('registration.success')
            ->with('success', 'Your brand application has been submitted.');
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q', '');

        $brands = Brand::query()
            ->whereNull('user_id')
            ->where('status', 'approved')
            ->where('active', true)
            ->whereDoesntHave('applications', fn ($q) => $q->pending())
            ->when($query, fn ($q) => $q->where('name', 'like', "%{$query}%"))
            ->select('id', 'name', 'slug', 'logo_path', 'primary_color', 'website_url', 'description')
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json($brands);
    }
}
```

- [ ] **Step 3: Add `applications` relationship to Brand model**

Add to `app/Models/Brand.php` after the `reviewer()` method:

```php
public function applications(): HasMany
{
    return $this->hasMany(BrandApplication::class);
}
```

Add `use` for `HasMany` is already imported.

- [ ] **Step 4: Add brand search route**

In `routes/web.php`, inside the `register` prefix group (after the existing `registration.success` route at line 81):

```php
Route::get('brand/search', [BrandRegistrationController::class, 'search'])->name('brand.search');
```

- [ ] **Step 5: Write tests for registration flows**

Replace `tests/Feature/Registration/BrandRegistrationTest.php`:

```php
<?php

use App\Enums\ApplicationStatus;
use App\Models\Brand;
use App\Models\BrandApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows brand registration form with search', function () {
    $this->get(route('registration.brand.create'))
        ->assertOk()
        ->assertSee('Partner with Mobius');
});

it('returns brands from search endpoint', function () {
    Brand::factory()->create(['name' => 'Starbucks', 'user_id' => null, 'status' => 'approved']);
    Brand::factory()->create(['name' => 'Secret Recipe', 'user_id' => null, 'status' => 'approved']);

    $response = $this->getJson(route('registration.brand.search', ['q' => 'star']));

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['name' => 'Starbucks']);
});

it('excludes claimed brands from search', function () {
    Brand::factory()->create(['name' => 'Claimed Brand', 'user_id' => User::factory(), 'status' => 'approved']);
    Brand::factory()->create(['name' => 'Available Brand', 'user_id' => null, 'status' => 'approved']);

    $response = $this->getJson(route('registration.brand.search', ['q' => '']));

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['name' => 'Available Brand']);
});

it('excludes brands with pending applications from search', function () {
    $brand = Brand::factory()->create(['name' => 'Pending Brand', 'user_id' => null, 'status' => 'approved']);
    BrandApplication::create([
        'brand_id' => $brand->id,
        'user_id' => User::factory()->create()->id,
        'status' => 'pending',
        'brand_name' => $brand->name,
        'contact_person' => 'Test',
        'contact_email' => 'test@test.com',
    ]);

    $response = $this->getJson(route('registration.brand.search', ['q' => '']));

    $response->assertOk()
        ->assertJsonCount(0);
});

it('submits a brand claim for existing brand', function () {
    $brand = Brand::factory()->create(['user_id' => null, 'status' => 'approved']);

    $this->post(route('registration.brand.store'), [
        'flow' => 'claim',
        'brand_id' => $brand->id,
        'contact_person' => 'Alice Wong',
        'email' => 'alice@example.com',
        'phone' => '+60123456789',
        'password' => 'SecureP@ss1',
        'password_confirmation' => 'SecureP@ss1',
    ])->assertRedirect(route('registration.success'));

    $application = BrandApplication::where('brand_id', $brand->id)->first();
    expect($application)->not->toBeNull()
        ->and($application->status)->toBe(ApplicationStatus::Pending)
        ->and($application->brand_name)->toBe($brand->name);

    // Brand should remain unclaimed
    expect($brand->fresh()->user_id)->toBeNull();
});

it('submits a new brand request', function () {
    $this->post(route('registration.brand.store'), [
        'flow' => 'new',
        'brand_name' => 'Awesome Tea Co',
        'description' => 'New bubble tea brand',
        'website_url' => 'https://awesometea.com',
        'contact_person' => 'Bob Lee',
        'email' => 'bob@awesometea.com',
        'phone' => '+60198765432',
        'password' => 'SecureP@ss1',
        'password_confirmation' => 'SecureP@ss1',
    ])->assertRedirect(route('registration.success'));

    $application = BrandApplication::where('brand_name', 'Awesome Tea Co')->first();
    expect($application)->not->toBeNull()
        ->and($application->brand_id)->toBeNull()
        ->and($application->status)->toBe(ApplicationStatus::Pending);
});

it('validates required fields for claim flow', function () {
    $this->post(route('registration.brand.store'), ['flow' => 'claim'])
        ->assertSessionHasErrors(['brand_id', 'contact_person', 'email', 'password']);
});

it('validates required fields for new brand flow', function () {
    $this->post(route('registration.brand.store'), ['flow' => 'new'])
        ->assertSessionHasErrors(['brand_name', 'contact_person', 'email', 'password']);
});

it('rejects duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->post(route('registration.brand.store'), [
        'flow' => 'new',
        'brand_name' => 'Some Brand',
        'contact_person' => 'Jane',
        'email' => 'taken@example.com',
        'password' => 'SecureP@ss1',
        'password_confirmation' => 'SecureP@ss1',
    ])->assertSessionHasErrors(['email']);
});

it('shows success page', function () {
    $this->get(route('registration.success'))
        ->assertOk()
        ->assertSee('Application Received');
});
```

- [ ] **Step 6: Run tests**

```bash
php artisan test --compact --filter=BrandRegistrationTest
```

Expected: All tests pass.

- [ ] **Step 7: Run pint + commit**

```bash
vendor/bin/pint --dirty
```

Commit: `feat: brand registration controller with claim + new brand flows and search API`

---

### Task 5: Registration Page UI Redesign

**Files:**
- Overwrite: `resources/views/registration/brand.blade.php`

This task uses the `frontend-design` skill for the UI.

- [ ] **Step 1: Write the redesigned registration page**

Replace `resources/views/registration/brand.blade.php` with an Alpine.js-powered searchable brand picker. The page has two states:

1. **Initial**: Search bar + brand cards dropdown
2. **Brand selected** (claim flow): Selected brand card shown + contact/password fields
3. **"Can't find your brand?"** (new flow): Full form with brand details + contact/password

Key Alpine.js component: `brandSearch` manages state, fetches from `/register/brand/search?q=`, toggles between flows.

The exact UI should be built using the `frontend-design` skill invocation at implementation time. The implementing agent should:
- Use `<x-layouts.app>` wrapper (matches current page)
- Use existing `<x-card>`, `<x-input>`, `<x-button>` components
- Use Alpine.js `x-data`, `x-model`, `@click`, `x-show`, `x-for` directives
- Fetch brands from `{{ route('registration.brand.search') }}?q=` via `fetch()`
- Style the search dropdown with Tailwind: rounded cards, brand color dots, hover states
- Hidden `<input name="flow" value="claim|new">` to control which validation path
- Hidden `<input name="brand_id">` set when a brand is selected

- [ ] **Step 2: Verify page loads**

Visit `http://localhost:8000/register/brand` — should show searchable dropdown.

- [ ] **Step 3: Run registration tests**

```bash
php artisan test --compact --filter=BrandRegistrationTest
```

Expected: All pass.

- [ ] **Step 4: Commit**

Commit: `feat: redesign brand registration page with searchable brand directory`

---

### Task 6: Admin Applications Page — Switch to BrandApplication

**Files:**
- Modify: `app/Http/Controllers/Admin/ApplicationController.php`
- Create: `app/Http/Requests/ApproveBrandApplicationRequest.php`
- Overwrite: `resources/views/admin/applications/brands/index.blade.php`
- Overwrite: `resources/views/admin/applications/brands/show.blade.php`
- Modify: `routes/web.php` (update application routes)

- [ ] **Step 1: Create ApproveBrandApplicationRequest**

```bash
php artisan make:request ApproveBrandApplicationRequest --no-interaction
```

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveBrandApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'points_multiplier' => ['required', 'numeric', 'min:1.00', 'max:5.00'],
            'rewards_budget' => ['required', 'integer', 'min:0', 'max:999999'],
        ];
    }
}
```

- [ ] **Step 2: Update ApplicationController brand methods**

Replace the brand-related methods in `app/Http/Controllers/Admin/ApplicationController.php`:

```php
public function brandApplications(Request $request): View
{
    $status = $request->query('status', 'pending');

    $applications = BrandApplication::query()
        ->when($status !== 'all', fn ($q) => $q->where('status', $status))
        ->when($status === 'all', fn ($q) => $q->whereIn('status', ['pending', 'approved', 'rejected']))
        ->with(['brand', 'user'])
        ->latest()
        ->paginate(20);

    return view('admin.applications.brands.index', [
        'applications' => $applications,
        'currentStatus' => $status,
        'counts' => [
            'pending' => BrandApplication::pending()->count(),
            'approved' => BrandApplication::approved()->count(),
            'rejected' => BrandApplication::rejected()->count(),
        ],
    ]);
}

public function showBrandApplication(BrandApplication $brandApplication): View
{
    $brandApplication->load(['brand', 'user', 'reviewer']);

    return view('admin.applications.brands.show', ['application' => $brandApplication]);
}

public function approveBrand(ApproveBrandApplicationRequest $request, BrandApplication $brandApplication): RedirectResponse
{
    $this->service->approveBrandApplication($brandApplication, $request->user(), [
        'points_multiplier' => $request->validated('points_multiplier'),
        'rewards_budget' => $request->validated('rewards_budget'),
    ]);

    return redirect()->route('admin.applications.brands.index')
        ->with('success', "Application for \"{$brandApplication->brand_name}\" has been approved.");
}

public function rejectBrand(RejectApplicationRequest $request, BrandApplication $brandApplication): RedirectResponse
{
    $this->service->rejectBrandApplication($brandApplication, $request->user(), $request->validated('rejection_reason'));

    return redirect()->route('admin.applications.brands.index')
        ->with('success', "Application for \"{$brandApplication->brand_name}\" has been rejected.");
}
```

Add imports: `use App\Models\BrandApplication;` and `use App\Http\Requests\ApproveBrandApplicationRequest;`.

- [ ] **Step 3: Update admin routes**

In `routes/web.php`, replace the brand application routes (lines 175-178):

```php
Route::get('brands', [ApplicationController::class, 'brandApplications'])->name('brands.index');
Route::get('brands/{brand_application}', [ApplicationController::class, 'showBrandApplication'])->name('brands.show');
Route::post('brands/{brand_application}/approve', [ApplicationController::class, 'approveBrand'])->name('brands.approve');
Route::post('brands/{brand_application}/reject', [ApplicationController::class, 'rejectBrand'])->name('brands.reject');
```

- [ ] **Step 4: Update index view**

Rewrite `resources/views/admin/applications/brands/index.blade.php` to render `$applications` (BrandApplication collection) instead of `$brands`. Key changes:
- Each card shows `$app->brand_name`, whether it's claiming existing (`$app->brand` exists) or new request
- Badge: "Claiming [Brand]" vs "New Brand"
- Contact info from application
- Same status tabs pattern

- [ ] **Step 5: Update show view**

Rewrite `resources/views/admin/applications/brands/show.blade.php` to show BrandApplication details:
- If claiming existing brand: show brand card from catalog (logo, website, color)
- If new brand: show submitted brand info
- Contact info from application
- Approve/reject forms with `brand_application` route parameter

- [ ] **Step 6: Run tests**

```bash
php artisan test --compact --filter=BrandApplicationTest
php artisan test --compact --filter=BrandRegistrationTest
```

- [ ] **Step 7: Run pint + commit**

```bash
vendor/bin/pint --dirty
```

Commit: `feat: update admin applications page to use BrandApplication model`

---

### Task 7: Admin Brand Directory Page

**Files:**
- Create: `app/Http/Controllers/Admin/BrandDirectoryController.php`
- Create: `resources/views/admin/brand-directory/index.blade.php`
- Create: `resources/views/admin/brand-directory/edit.blade.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Generate controller**

```bash
php artisan make:controller Admin/BrandDirectoryController --no-interaction
```

- [ ] **Step 2: Write controller**

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandDirectoryController extends Controller
{
    public function index(): View
    {
        $brands = Brand::query()
            ->withCount('outlets')
            ->with('adminUser')
            ->orderBy('name')
            ->paginate(30);

        return view('admin.brand-directory.index', compact('brands'));
    }

    public function edit(Brand $brand): View
    {
        return view('admin.brand-directory.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'primary_color' => ['nullable', 'string', 'max:7'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('brands', 'public');
        }
        unset($data['logo']);

        $brand->update($data);

        return redirect()->route('admin.brand-directory.index')
            ->with('success', "Brand \"{$brand->name}\" updated.");
    }

    public function create(): View
    {
        return view('admin.brand-directory.edit', ['brand' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'primary_color' => ['nullable', 'string', 'max:7'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('brands', 'public');
        }
        unset($data['logo']);

        $data['status'] = 'approved';
        $data['active'] = true;

        Brand::create($data);

        return redirect()->route('admin.brand-directory.index')
            ->with('success', "Brand \"{$data['name']}\" added to directory.");
    }
}
```

- [ ] **Step 3: Add routes**

In `routes/web.php`, inside the admin group (after the brand monitoring routes at line 171):

```php
// Brand directory (catalog management)
Route::get('brand-directory', [BrandDirectoryController::class, 'index'])->name('brand-directory.index');
Route::get('brand-directory/create', [BrandDirectoryController::class, 'create'])->name('brand-directory.create');
Route::post('brand-directory', [BrandDirectoryController::class, 'store'])->name('brand-directory.store');
Route::get('brand-directory/{brand}/edit', [BrandDirectoryController::class, 'edit'])->name('brand-directory.edit');
Route::put('brand-directory/{brand}', [BrandDirectoryController::class, 'update'])->name('brand-directory.update');
```

Add `use App\Http\Controllers\Admin\BrandDirectoryController;` to the imports in `routes/web.php`.

- [ ] **Step 4: Write index view**

`resources/views/admin/brand-directory/index.blade.php` — table showing all brands:
- Columns: Color dot + Name, Website, Status (Available/Claimed by [user]), Outlets count, Edit link
- "Add Brand" button in header
- Uses existing `<x-layouts.admin>`, `<x-card>` patterns

- [ ] **Step 5: Write edit/create view**

`resources/views/admin/brand-directory/edit.blade.php` — form with:
- Name, Description, Website URL, Primary Color (color picker), Logo upload
- Save/Cancel buttons
- Uses `<x-input>` components

- [ ] **Step 6: Run pint + commit**

```bash
vendor/bin/pint --dirty
```

Commit: `feat: add admin brand directory page for catalog management`

---

### Task 8: Update Sidebar + Badge

**Files:**
- Modify: `resources/views/components/admin/sidebar.blade.php`

- [ ] **Step 1: Add Brand Directory link and update Applications badge**

In the sidebar nav section, add Brand Directory after the Brands item and update the Applications badge to use BrandApplication:

After the Brands sidebar-item (line 55):
```blade
<x-admin.sidebar-item route="admin.brand-directory.index" label="Brand Directory">
    <x-slot:icon><x-heroicon-o-rectangle-stack class="w-5 h-5" /></x-slot:icon>
</x-admin.sidebar-item>
```

Update the Applications badge (around line 64) to count BrandApplication pending instead of Brand pending:
```blade
@if (($pendingAppCount = \App\Models\BrandApplication::pending()->count() + \App\Models\CollectorAgency::pending()->count()) > 0)
    <x-slot:badge>{{ $pendingAppCount }}</x-slot:badge>
@endif
```

- [ ] **Step 2: Verify sidebar loads**

Visit any admin page. Brand Directory should appear in sidebar.

- [ ] **Step 3: Commit**

Commit: `feat: add Brand Directory to admin sidebar, update application badge count`

---

### Task 9: Final Verification

- [ ] **Step 1: Run all brand-related tests**

```bash
php artisan test --compact --filter=Brand
```

Expected: All pass.

- [ ] **Step 2: Manual smoke test**

1. Visit `/register/brand` — search dropdown works, brands appear
2. Select a brand — card shows, contact fields appear
3. Click "Can't find?" — full form appears
4. Submit claim — redirects to success
5. Submit new brand — redirects to success
6. Admin `/admin/applications/brands` — shows new BrandApplications
7. Admin `/admin/brand-directory` — shows all 18 brands with status
8. Admin approve a claim — brand gets user_id
9. Admin approve new brand — brand created in catalog

- [ ] **Step 3: Run pint on everything**

```bash
vendor/bin/pint --dirty
```

- [ ] **Step 4: Final commit**

Commit: `feat: complete brand directory and registration redesign`
