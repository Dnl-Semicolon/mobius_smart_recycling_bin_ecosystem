<?php

use App\Enums\WasteType;
use App\Models\Bin;
use App\Models\BinAssignment;
use App\Models\DetectionEvent;
use App\Models\Outlet;
use App\Models\RecyclingTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Stats endpoint
|--------------------------------------------------------------------------
*/

test('customer stats returns correct data for authenticated user', function () {
    $user = User::factory()->publicUser()->create(['points_balance' => 30, 'current_streak' => 2, 'longest_streak' => 5]);
    $bin = Bin::factory()->create();

    // Create 3 detection events linked to this user (without observer to control state)
    DetectionEvent::factory()->count(3)->create([
        'bin_id' => $bin->id,
        'user_id' => $user->id,
        'waste_type' => WasteType::PaperCup,
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/customer/stats');

    $response->assertOk()
        ->assertJson([
            'points_balance' => 30,
            'current_streak' => 2,
            'longest_streak' => 5,
            'total_detections' => 3,
            'co2_saved_grams' => 75,
        ]);
});

test('customer stats returns zeros for new user', function () {
    $user = User::factory()->publicUser()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/customer/stats')
        ->assertOk()
        ->assertJson([
            'points_balance' => 0,
            'current_streak' => 0,
            'longest_streak' => 0,
            'total_detections' => 0,
            'co2_saved_grams' => 0,
        ]);
});

/*
|--------------------------------------------------------------------------
| History endpoint
|--------------------------------------------------------------------------
*/

test('customer history returns paginated detection events', function () {
    $user = User::factory()->publicUser()->create();
    $bin = Bin::factory()->create();

    DetectionEvent::factory()->count(3)->create([
        'bin_id' => $bin->id,
        'user_id' => $user->id,
        'waste_type' => WasteType::PlasticCup,
    ]);

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/v1/customer/history');

    $response->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [['id', 'waste_type', 'waste_type_label', 'confidence', 'bin_serial', 'detected_at', 'points_earned']],
            'current_page',
            'last_page',
        ]);
});

test('customer history only shows own detections', function () {
    $user = User::factory()->publicUser()->create();
    $otherUser = User::factory()->publicUser()->create();
    $bin = Bin::factory()->create();

    DetectionEvent::factory()->count(2)->create(['bin_id' => $bin->id, 'user_id' => $user->id]);
    DetectionEvent::factory()->count(3)->create(['bin_id' => $bin->id, 'user_id' => $otherUser->id]);

    Sanctum::actingAs($user);

    $this->getJson('/api/v1/customer/history')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

/*
|--------------------------------------------------------------------------
| Leaderboard endpoint
|--------------------------------------------------------------------------
*/

test('leaderboard returns top users by points', function () {
    User::factory()->publicUser()->create(['name' => 'Top Recycler', 'points_balance' => 100, 'current_streak' => 5]);
    User::factory()->publicUser()->create(['name' => 'Second Place', 'points_balance' => 50, 'current_streak' => 3]);
    User::factory()->publicUser()->create(['name' => 'Zero Points', 'points_balance' => 0]);

    Sanctum::actingAs(User::factory()->publicUser()->create());

    $response = $this->getJson('/api/v1/customer/leaderboard');

    $response->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.name', 'Top Recycler')
        ->assertJsonPath('data.0.rank', 1)
        ->assertJsonPath('data.0.points', 100)
        ->assertJsonPath('data.1.name', 'Second Place')
        ->assertJsonPath('data.1.rank', 2);
});

/*
|--------------------------------------------------------------------------
| Points awarding via observer
|--------------------------------------------------------------------------
*/

test('points are awarded when detection event is created with user_id', function () {
    $user = User::factory()->publicUser()->create();
    $bin = Bin::factory()->create();

    DetectionEvent::create([
        'bin_id' => $bin->id,
        'user_id' => $user->id,
        'waste_type' => WasteType::PaperCup,
        'confidence' => 95,
        'detected_at' => now(),
    ]);

    $user->refresh();

    expect($user->points_balance)->toBe(15);
    expect(RecyclingTransaction::where('user_id', $user->id)->count())->toBe(1);

    $transaction = RecyclingTransaction::where('user_id', $user->id)->first();
    expect($transaction->points_earned)->toBe(15);
    expect($transaction->type)->toBe('earned');
    expect($transaction->description)->toBe('Recycled paper_cup');
});

test('no points awarded when detection has no user_id', function () {
    $bin = Bin::factory()->create();

    DetectionEvent::create([
        'bin_id' => $bin->id,
        'waste_type' => WasteType::PaperCup,
        'confidence' => 95,
        'detected_at' => now(),
    ]);

    expect(RecyclingTransaction::count())->toBe(0);
});

test('points vary by waste type', function (WasteType $wasteType, int $expectedPoints) {
    $user = User::factory()->publicUser()->create();
    $bin = Bin::factory()->create();

    DetectionEvent::create([
        'bin_id' => $bin->id,
        'user_id' => $user->id,
        'waste_type' => $wasteType,
        'confidence' => 90,
        'detected_at' => now(),
    ]);

    $user->refresh();
    expect($user->points_balance)->toBe($expectedPoints);
})->with([
    'paper cup' => [WasteType::PaperCup, 15],
    'plastic cup' => [WasteType::PlasticCup, 12],
    'lid' => [WasteType::Lid, 5],
    'straw' => [WasteType::Straw, 3],
    'napkin' => [WasteType::Napkin, 2],
    'liquid waste' => [WasteType::LiquidWaste, 8],
]);

/*
|--------------------------------------------------------------------------
| Streak logic
|--------------------------------------------------------------------------
*/

test('streak starts at 1 on first recycling', function () {
    $user = User::factory()->publicUser()->create();
    $bin = Bin::factory()->create();

    DetectionEvent::create([
        'bin_id' => $bin->id,
        'user_id' => $user->id,
        'waste_type' => WasteType::PaperCup,
        'confidence' => 90,
        'detected_at' => now(),
    ]);

    $user->refresh();
    expect($user->current_streak)->toBe(1);
    expect($user->longest_streak)->toBe(1);
    expect($user->last_recycled_at)->not->toBeNull();
});

test('streak increments when recycling on consecutive days', function () {
    $user = User::factory()->publicUser()->create([
        'current_streak' => 1,
        'longest_streak' => 1,
        'last_recycled_at' => now()->subDay(),
    ]);
    $bin = Bin::factory()->create();

    DetectionEvent::create([
        'bin_id' => $bin->id,
        'user_id' => $user->id,
        'waste_type' => WasteType::PaperCup,
        'confidence' => 90,
        'detected_at' => now(),
    ]);

    $user->refresh();
    expect($user->current_streak)->toBe(2);
    expect($user->longest_streak)->toBe(2);
});

test('streak does not double increment on same day', function () {
    $user = User::factory()->publicUser()->create([
        'current_streak' => 1,
        'longest_streak' => 1,
        'last_recycled_at' => now(),
    ]);
    $bin = Bin::factory()->create();

    DetectionEvent::create([
        'bin_id' => $bin->id,
        'user_id' => $user->id,
        'waste_type' => WasteType::PaperCup,
        'confidence' => 90,
        'detected_at' => now(),
    ]);

    $user->refresh();
    expect($user->current_streak)->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Authorization
|--------------------------------------------------------------------------
*/

test('unauthenticated user cannot access customer endpoints', function () {
    $this->getJson('/api/v1/customer/stats')->assertUnauthorized();
    $this->getJson('/api/v1/customer/history')->assertUnauthorized();
    $this->getJson('/api/v1/customer/leaderboard')->assertUnauthorized();
});

test('admin cannot access customer endpoints', function () {
    Sanctum::actingAs(User::factory()->admin()->create());

    $this->getJson('/api/v1/customer/stats')->assertForbidden();
    $this->getJson('/api/v1/customer/history')->assertForbidden();
    $this->getJson('/api/v1/customer/leaderboard')->assertForbidden();
});

test('store owner can access customer endpoints', function () {
    Sanctum::actingAs(User::factory()->storeOwner()->create());

    $this->getJson('/api/v1/customer/stats')->assertOk();
    $this->getJson('/api/v1/customer/history')->assertOk();
    $this->getJson('/api/v1/customer/leaderboard')->assertOk();
});

test('collector can access customer endpoints', function () {
    Sanctum::actingAs(User::factory()->collector()->create());

    $this->getJson('/api/v1/customer/stats')->assertOk();
    $this->getJson('/api/v1/customer/history')->assertOk();
    $this->getJson('/api/v1/customer/leaderboard')->assertOk();
});

/*
|--------------------------------------------------------------------------
| Scan endpoint (QR code → bin session)
|--------------------------------------------------------------------------
*/

test('scan creates a session and returns bin info', function () {
    $outlet = Outlet::factory()->active()->create(['name' => 'Starbucks Gurney']);
    $bin = Bin::factory()->active()->create(['serial_number' => 'MBR-2026-001']);
    BinAssignment::factory()->current()->create(['bin_id' => $bin->id, 'outlet_id' => $outlet->id]);

    $user = User::factory()->publicUser()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/customer/scan', [
        'bin_serial' => 'MBR-2026-001',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.bin_id', $bin->id)
        ->assertJsonPath('data.bin_serial', 'MBR-2026-001')
        ->assertJsonPath('data.outlet_name', 'Starbucks Gurney')
        ->assertJsonPath('data.session_expires_in', 60);

    expect(Cache::get("bin_session:{$bin->id}"))->toBe($user->id);
});

test('scan returns 404 for non-existent bin serial', function () {
    Sanctum::actingAs(User::factory()->publicUser()->create());

    $this->postJson('/api/v1/customer/scan', [
        'bin_serial' => 'FAKE-999',
    ])->assertNotFound();
});

test('scan requires bin_serial', function () {
    Sanctum::actingAs(User::factory()->publicUser()->create());

    $this->postJson('/api/v1/customer/scan', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['bin_serial']);
});

test('scan requires authentication', function () {
    $this->postJson('/api/v1/customer/scan', ['bin_serial' => 'MBR-2026-001'])
        ->assertUnauthorized();
});

/*
|--------------------------------------------------------------------------
| Session-based detection (scan → detect → auto-attribute)
|--------------------------------------------------------------------------
*/

test('detection auto-attributes user from bin session', function () {
    $bin = Bin::factory()->active()->empty()->create();
    $user = User::factory()->publicUser()->create(['points_balance' => 0]);

    // Simulate: user scanned the bin QR
    Cache::put("bin_session:{$bin->id}", $user->id, now()->addSeconds(60));

    // Bin detects waste (no user_id in request)
    $response = $this->postJson('/api/v1/detect', [
        'bin_id' => $bin->id,
        'waste_type' => 'paper_cup',
        'confidence' => 90,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.user_id', $user->id);

    // Points should be awarded (paper_cup = 15 base, no brand multiplier)
    expect($user->fresh()->points_balance)->toBe(15);

    // Session should be consumed (one-time use)
    expect(Cache::get("bin_session:{$bin->id}"))->toBeNull();
});

test('detection without session creates anonymous event', function () {
    $bin = Bin::factory()->active()->empty()->create();

    $response = $this->postJson('/api/v1/detect', [
        'bin_id' => $bin->id,
        'waste_type' => 'straw',
        'confidence' => 80,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.user_id', null);

    // Fill level should still bump (no user needed for that)
    expect($bin->fresh()->fill_level)->toBe(1);
    expect(RecyclingTransaction::count())->toBe(0);
});
