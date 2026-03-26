<?php

use App\Models\Bin;
use App\Models\BinAssignment;
use App\Models\Brand;
use App\Models\DetectionEvent;
use App\Models\Outlet;
use App\Models\Reward;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

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

it('GET dashboard returns aggregated stats for HQ', function () {
    Sanctum::actingAs($this->hqUser);
    DetectionEvent::factory()->create(['bin_id' => $this->bin1->id, 'detected_at' => now()]);
    DetectionEvent::factory()->create(['bin_id' => $this->bin2->id, 'detected_at' => now()]);

    $response = $this->getJson('/api/v1/store-owner/dashboard');

    $response->assertOk()
        ->assertJsonPath('data.today_detections', 2)
        ->assertJsonStructure(['data' => ['today_detections', 'week_detections', 'month_detections', 'unique_recyclers', 'redemption_count', 'active_rewards', 'brand_loyalty', 'waste_breakdown']]);
});

it('GET dashboard returns scoped stats for branch', function () {
    Sanctum::actingAs($this->branchUser);
    DetectionEvent::factory()->create(['bin_id' => $this->bin1->id, 'detected_at' => now()]);
    DetectionEvent::factory()->create(['bin_id' => $this->bin2->id, 'detected_at' => now()]);

    $response = $this->getJson('/api/v1/store-owner/dashboard');

    $response->assertOk()
        ->assertJsonPath('data.today_detections', 1);
});

it('GET brand returns brand profile', function () {
    Sanctum::actingAs($this->hqUser);

    $response = $this->getJson('/api/v1/store-owner/brand');

    $response->assertOk()
        ->assertJsonPath('data.name', $this->brand->name)
        ->assertJsonStructure(['data' => ['id', 'name', 'slug', 'primary_color', 'points_multiplier', 'rewards_budget']]);
});

it('GET analytics returns chart data', function () {
    Sanctum::actingAs($this->hqUser);
    DetectionEvent::factory()->create(['bin_id' => $this->bin1->id, 'detected_at' => now()]);

    $response = $this->getJson('/api/v1/store-owner/analytics');

    $response->assertOk()
        ->assertJsonStructure(['data' => ['daily_detections', 'waste_breakdown', 'hourly_distribution']]);
});

it('GET outlets returns all outlets for HQ', function () {
    Sanctum::actingAs($this->hqUser);

    $response = $this->getJson('/api/v1/store-owner/outlets');

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});

it('GET outlets returns assigned outlets for branch', function () {
    Sanctum::actingAs($this->branchUser);

    $response = $this->getJson('/api/v1/store-owner/outlets');

    $response->assertOk()
        ->assertJsonCount(1, 'data');
});

it('GET outlets/{id} returns outlet detail for in-scope outlet', function () {
    Sanctum::actingAs($this->hqUser);

    $response = $this->getJson("/api/v1/store-owner/outlets/{$this->outlet1->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $this->outlet1->id)
        ->assertJsonStructure(['data' => ['id', 'name', 'address', 'bins']]);
});

it('GET outlets/{id} rejects out-of-scope outlet for branch', function () {
    Sanctum::actingAs($this->branchUser);

    $response = $this->getJson("/api/v1/store-owner/outlets/{$this->outlet2->id}");

    $response->assertForbidden();
});

it('GET bins returns all scoped bins', function () {
    Sanctum::actingAs($this->hqUser);

    $response = $this->getJson('/api/v1/store-owner/bins');

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});

it('GET rewards returns brand rewards', function () {
    Sanctum::actingAs($this->hqUser);
    Reward::factory()->create(['brand_id' => $this->brand->id]);

    $response = $this->getJson('/api/v1/store-owner/rewards');

    $response->assertOk()
        ->assertJsonCount(1, 'data');
});

it('POST rewards creates reward for HQ', function () {
    Sanctum::actingAs($this->hqUser);

    $response = $this->postJson('/api/v1/store-owner/rewards', [
        'name' => 'Free Coffee',
        'points_cost' => 100,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Free Coffee');
    expect($this->brand->rewards()->count())->toBe(1);
});

it('POST rewards returns 403 for branch manager', function () {
    Sanctum::actingAs($this->branchUser);

    $response = $this->postJson('/api/v1/store-owner/rewards', [
        'name' => 'Free Coffee',
        'points_cost' => 100,
    ]);

    $response->assertForbidden();
});

it('PUT rewards updates reward for HQ', function () {
    Sanctum::actingAs($this->hqUser);
    $reward = Reward::factory()->create(['brand_id' => $this->brand->id, 'name' => 'Old Name']);

    $response = $this->putJson("/api/v1/store-owner/rewards/{$reward->id}", [
        'name' => 'New Name',
        'points_cost' => 200,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'New Name');
});

it('DELETE rewards deletes reward for HQ', function () {
    Sanctum::actingAs($this->hqUser);
    $reward = Reward::factory()->create(['brand_id' => $this->brand->id]);

    $response = $this->deleteJson("/api/v1/store-owner/rewards/{$reward->id}");

    $response->assertOk();
    expect($this->brand->rewards()->count())->toBe(0);
});

it('returns 401 for unauthenticated requests', function () {
    $this->getJson('/api/v1/store-owner/dashboard')->assertUnauthorized();
});

it('returns 403 for non-store-owner role', function () {
    Sanctum::actingAs(User::factory()->create(['roles' => ['public_user']]));

    $this->getJson('/api/v1/store-owner/dashboard')->assertForbidden();
});
