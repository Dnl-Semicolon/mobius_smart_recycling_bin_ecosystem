<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

use App\Enums\RedemptionStatus;
use App\Enums\WasteType;
use App\Models\Bin;
use App\Models\BinAssignment;
use App\Models\Brand;
use App\Models\DetectionEvent;
use App\Models\Outlet;
use App\Models\Redemption;
use App\Models\Reward;
use App\Models\User;
use App\Services\DetectionService;

/*
|--------------------------------------------------------------------------
| Rewards listing
|--------------------------------------------------------------------------
*/

test('customer can list available rewards', function () {
    $brand = Brand::factory()->sponsored()->create();
    Reward::factory()->forBrand($brand)->create(['name' => 'Free Coffee', 'points_cost' => 100, 'active' => true]);
    Reward::factory()->forBrand($brand)->create(['name' => 'Inactive', 'active' => false]);
    Reward::factory()->create(['name' => 'Platform Reward', 'points_cost' => 200]);

    $user = User::factory()->publicUser()->create();

    $response = $this->actingAs($user)->getJson('/api/v1/customer/rewards');

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.name', 'Free Coffee')
        ->assertJsonPath('data.0.brand.name', $brand->name);
});

test('expired and out of stock rewards are excluded', function () {
    Reward::factory()->expired()->create(['name' => 'Expired']);
    Reward::factory()->outOfStock()->create(['name' => 'Sold Out']);
    Reward::factory()->create(['name' => 'Available', 'active' => true]);

    $user = User::factory()->publicUser()->create();

    $response = $this->actingAs($user)->getJson('/api/v1/customer/rewards');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Available');
});

/*
|--------------------------------------------------------------------------
| Redemption flow
|--------------------------------------------------------------------------
*/

test('customer can redeem a reward', function () {
    $user = User::factory()->publicUser()->create(['points_balance' => 500]);
    $reward = Reward::factory()->create(['points_cost' => 200, 'stock' => 10]);

    $response = $this->actingAs($user)->postJson("/api/v1/customer/rewards/{$reward->id}/redeem");

    $response->assertCreated()
        ->assertJsonPath('data.points_spent', 200)
        ->assertJsonPath('data.new_balance', 300);

    expect($user->fresh()->points_balance)->toBe(300);
    expect($reward->fresh()->stock)->toBe(9);
    expect(Redemption::where('user_id', $user->id)->count())->toBe(1);

    $redemption = Redemption::where('user_id', $user->id)->first();
    expect($redemption->status)->toBe(RedemptionStatus::Pending);
    expect($redemption->voucher_code)->toStartWith('MBX-');
});

test('redemption fails with insufficient points', function () {
    $user = User::factory()->publicUser()->create(['points_balance' => 50]);
    $reward = Reward::factory()->create(['points_cost' => 200]);

    $response = $this->actingAs($user)->postJson("/api/v1/customer/rewards/{$reward->id}/redeem");

    $response->assertUnprocessable()
        ->assertJsonPath('message', 'Insufficient points. You need 200 points but have 50.');

    expect($user->fresh()->points_balance)->toBe(50);
});

test('redemption fails for out of stock reward', function () {
    $user = User::factory()->publicUser()->create(['points_balance' => 500]);
    $reward = Reward::factory()->outOfStock()->create(['points_cost' => 100]);

    $response = $this->actingAs($user)->postJson("/api/v1/customer/rewards/{$reward->id}/redeem");

    $response->assertUnprocessable();
    expect($user->fresh()->points_balance)->toBe(500);
});

test('redemption fails for expired reward', function () {
    $user = User::factory()->publicUser()->create(['points_balance' => 500]);
    $reward = Reward::factory()->expired()->create(['points_cost' => 100]);

    $response = $this->actingAs($user)->postJson("/api/v1/customer/rewards/{$reward->id}/redeem");

    $response->assertUnprocessable();
});

test('brand budget is deducted on redemption', function () {
    $brand = Brand::factory()->sponsored(1.5, 1000)->create();
    $user = User::factory()->publicUser()->create(['points_balance' => 500]);
    $reward = Reward::factory()->forBrand($brand)->create(['points_cost' => 200]);

    $this->actingAs($user)->postJson("/api/v1/customer/rewards/{$reward->id}/redeem")
        ->assertCreated();

    expect($brand->fresh()->rewards_budget)->toBe(800);
});

test('customer can view redemption history', function () {
    $user = User::factory()->publicUser()->create();
    $reward = Reward::factory()->create();
    Redemption::factory()->create(['user_id' => $user->id, 'reward_id' => $reward->id]);

    $response = $this->actingAs($user)->getJson('/api/v1/customer/redemptions');

    $response->assertSuccessful()
        ->assertJsonPath('data.0.reward.name', $reward->name);
});

/*
|--------------------------------------------------------------------------
| Brand multiplier on points
|--------------------------------------------------------------------------
*/

test('brand multiplier increases points earned', function () {
    $brand = Brand::factory()->sponsored(1.50, 10000)->create();
    $outlet = Outlet::factory()->create(['brand_id' => $brand->id]);
    $bin = Bin::factory()->create();
    BinAssignment::create(['bin_id' => $bin->id, 'outlet_id' => $outlet->id, 'assigned_at' => now()]);

    $user = User::factory()->publicUser()->create(['points_balance' => 0]);

    // Cup must match bin brand to get multiplier (Option B hard deterrent)
    DetectionEvent::create([
        'bin_id' => $bin->id,
        'user_id' => $user->id,
        'waste_type' => WasteType::PaperCup,
        'confidence' => 95,
        'detected_brand_id' => $brand->id,
        'detected_at' => now(),
    ]);

    $user->refresh();

    // 15 base * 1.5 multiplier = 22.5 → rounds to 23
    expect($user->points_balance)->toBe(23);
});

test('no multiplier at unbranded bin gives base points', function () {
    $bin = Bin::factory()->create();
    $user = User::factory()->publicUser()->create(['points_balance' => 0]);

    DetectionEvent::create([
        'bin_id' => $bin->id,
        'user_id' => $user->id,
        'waste_type' => WasteType::PaperCup,
        'confidence' => 95,
        'detected_at' => now(),
    ]);

    $user->refresh();
    expect($user->points_balance)->toBe(15);
});

test('calculatePoints returns correct values for all waste types', function () {
    foreach (DetectionService::POINTS_PER_TYPE as $type => $expected) {
        $wasteType = WasteType::from($type);
        expect(DetectionService::calculatePoints($wasteType))->toBe($expected);
    }
});

/*
|--------------------------------------------------------------------------
| Auth checks
|--------------------------------------------------------------------------
*/

test('rewards listing requires authentication', function () {
    $this->getJson('/api/v1/customer/rewards')->assertUnauthorized();
});

test('redeem requires authentication', function () {
    $reward = Reward::factory()->create();
    $this->postJson("/api/v1/customer/rewards/{$reward->id}/redeem")->assertUnauthorized();
});
