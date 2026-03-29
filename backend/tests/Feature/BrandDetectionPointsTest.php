<?php

use App\Enums\WasteType;
use App\Models\Bin;
use App\Models\BinAssignment;
use App\Models\Brand;
use App\Models\DetectionEvent;
use App\Models\Outlet;
use App\Models\User;
use App\Services\DetectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Helper: create a branded bin (Bin → BinAssignment → Outlet → Brand)
|--------------------------------------------------------------------------
*/

function createBrandedBin(Brand $brand): Bin
{
    $outlet = Outlet::factory()->active()->create(['brand_id' => $brand->id]);
    $bin = Bin::factory()->create();
    BinAssignment::factory()->current()->create([
        'bin_id' => $bin->id,
        'outlet_id' => $outlet->id,
    ]);

    return $bin->fresh();
}

/*
|--------------------------------------------------------------------------
| Option B Hard Deterrent — calculatePoints unit tests
|--------------------------------------------------------------------------
*/

test('non-cup items get bin brand multiplier regardless of detected brand', function () {
    $brand = Brand::factory()->sponsored(1.50)->create();
    $bin = createBrandedBin($brand);

    // Lid at branded bin — should get 1.5x multiplier
    $points = DetectionService::calculatePoints(WasteType::Lid, $bin);
    expect($points)->toBe((int) round(5 * 1.5)); // 8

    // Straw at branded bin with a random detected brand — still 1.5x
    $otherBrand = Brand::factory()->create();
    $points = DetectionService::calculatePoints(WasteType::Straw, $bin, $otherBrand);
    expect($points)->toBe((int) round(3 * 1.5)); // 5
});

test('cup matching bin brand gets full brand multiplier', function () {
    $brand = Brand::factory()->sponsored(1.50)->create();
    $bin = createBrandedBin($brand);

    $points = DetectionService::calculatePoints(WasteType::PaperCup, $bin, $brand);
    expect($points)->toBe((int) round(15 * 1.5)); // 23
});

test('cup with no detected brand at branded bin gets base points', function () {
    $brand = Brand::factory()->sponsored(1.50)->create();
    $bin = createBrandedBin($brand);

    $points = DetectionService::calculatePoints(WasteType::PaperCup, $bin, null);
    expect($points)->toBe(15); // base only
});

test('competitor cup at branded bin gets hard deterrent (0.3x multiplier)', function () {
    $binBrand = Brand::factory()->sponsored(1.50)->create();
    $bin = createBrandedBin($binBrand);

    $competitor = Brand::factory()->sponsored(1.50)->create();

    $points = DetectionService::calculatePoints(WasteType::PaperCup, $bin, $competitor);
    // 15 × 1.5 × 0.3 = 6.75 → 7
    expect($points)->toBe((int) round(15 * 1.5 * 0.3));
});

test('any cup at unbranded bin gets base points', function () {
    $bin = Bin::factory()->create(); // No outlet assignment = no brand
    $someBrand = Brand::factory()->create();

    $points = DetectionService::calculatePoints(WasteType::PlasticCup, $bin, $someBrand);
    expect($points)->toBe(12); // base only

    $points = DetectionService::calculatePoints(WasteType::PlasticCup, $bin, null);
    expect($points)->toBe(12); // still base only
});

test('calculatePoints with no bin and no brand returns base points', function () {
    foreach (DetectionService::POINTS_PER_TYPE as $type => $expected) {
        $wasteType = WasteType::from($type);
        expect(DetectionService::calculatePoints($wasteType))->toBe($expected);
    }
});

/*
|--------------------------------------------------------------------------
| Integration: points actually awarded correctly via observer
|--------------------------------------------------------------------------
*/

test('brand match awards full multiplier points via processDetection', function () {
    $brand = Brand::factory()->sponsored(1.50)->create();
    $bin = createBrandedBin($brand);
    $user = User::factory()->create(['points_balance' => 0]);

    DetectionEvent::create([
        'bin_id' => $bin->id,
        'user_id' => $user->id,
        'waste_type' => WasteType::PaperCup,
        'confidence' => 95,
        'detected_brand_id' => $brand->id,
        'detected_at' => now(),
    ]);

    $user->refresh();
    expect($user->points_balance)->toBe((int) round(15 * 1.5)); // 23
});

test('competitor cup awards deterrent points via processDetection', function () {
    $binBrand = Brand::factory()->sponsored(1.50)->create();
    $bin = createBrandedBin($binBrand);
    $competitor = Brand::factory()->create();
    $user = User::factory()->create(['points_balance' => 0]);

    DetectionEvent::create([
        'bin_id' => $bin->id,
        'user_id' => $user->id,
        'waste_type' => WasteType::PaperCup,
        'confidence' => 90,
        'detected_brand_id' => $competitor->id,
        'detected_at' => now(),
    ]);

    $user->refresh();
    expect($user->points_balance)->toBe((int) round(15 * 1.5 * 0.3)); // 7
});

test('transaction description reflects brand bonus or competitor penalty', function () {
    $brand = Brand::factory()->sponsored(1.50)->create();
    $bin = createBrandedBin($brand);
    $competitor = Brand::factory()->create();
    $user = User::factory()->create(['points_balance' => 0]);

    // Brand match — bonus
    DetectionEvent::create([
        'bin_id' => $bin->id,
        'user_id' => $user->id,
        'waste_type' => WasteType::PaperCup,
        'confidence' => 95,
        'detected_brand_id' => $brand->id,
        'detected_at' => now(),
    ]);

    $bonusTx = $user->recyclingTransactions()->latest('id')->first();
    expect($bonusTx->description)->toContain('brand bonus');

    // Competitor cup — penalty
    DetectionEvent::create([
        'bin_id' => $bin->id,
        'user_id' => $user->id,
        'waste_type' => WasteType::PlasticCup,
        'confidence' => 85,
        'detected_brand_id' => $competitor->id,
        'detected_at' => now(),
    ]);

    $penaltyTx = $user->recyclingTransactions()->latest('id')->first();
    expect($penaltyTx->description)->toContain('competitor penalty');
});

/*
|--------------------------------------------------------------------------
| API: detected_brand_id accepted and stored
|--------------------------------------------------------------------------
*/

test('store endpoint accepts detected_brand_id', function () {
    $brand = Brand::factory()->create();
    $bin = Bin::factory()->create();

    $response = $this->postJson('/api/v1/detect', [
        'bin_id' => $bin->id,
        'waste_type' => 'paper_cup',
        'confidence' => 92,
        'detected_brand_id' => $brand->id,
    ]);

    $response->assertCreated();

    $event = DetectionEvent::latest('id')->first();
    expect($event->detected_brand_id)->toBe($brand->id);
});

test('store endpoint works without detected_brand_id', function () {
    $bin = Bin::factory()->create();

    $response = $this->postJson('/api/v1/detect', [
        'bin_id' => $bin->id,
        'waste_type' => 'lid',
        'confidence' => 88,
    ]);

    $response->assertCreated();

    $event = DetectionEvent::latest('id')->first();
    expect($event->detected_brand_id)->toBeNull();
});

test('store endpoint resolves detected_brand string to brand id', function () {
    $brand = Brand::factory()->create(['name' => 'Starbucks', 'slug' => 'starbucks']);
    $bin = Bin::factory()->create();

    $response = $this->postJson('/api/v1/detect', [
        'bin_id' => $bin->id,
        'waste_type' => 'paper_cup',
        'confidence' => 90,
        'detected_brand' => 'starbucks',
    ]);

    $response->assertCreated();

    $event = DetectionEvent::latest('id')->first();
    expect($event->detected_brand_id)->toBe($brand->id);
});

test('store endpoint ignores unrecognised detected_brand string', function () {
    $bin = Bin::factory()->create();

    $response = $this->postJson('/api/v1/detect', [
        'bin_id' => $bin->id,
        'waste_type' => 'paper_cup',
        'confidence' => 90,
        'detected_brand' => 'unknown-brand-xyz',
    ]);

    $response->assertCreated();

    $event = DetectionEvent::latest('id')->first();
    expect($event->detected_brand_id)->toBeNull();
});

test('store endpoint rejects invalid detected_brand_id', function () {
    $bin = Bin::factory()->create();

    $response = $this->postJson('/api/v1/detect', [
        'bin_id' => $bin->id,
        'waste_type' => 'paper_cup',
        'confidence' => 90,
        'detected_brand_id' => 99999,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('detected_brand_id');
});

/*
|--------------------------------------------------------------------------
| Model: detectedBrand relationship
|--------------------------------------------------------------------------
*/

test('detection event has detectedBrand relationship', function () {
    $brand = Brand::factory()->create();
    $event = DetectionEvent::factory()
        ->withDetectedBrand($brand)
        ->create();

    expect($event->detectedBrand)->not->toBeNull();
    expect($event->detectedBrand->id)->toBe($brand->id);
});

test('detection event detectedBrand is nullable', function () {
    $event = DetectionEvent::factory()->create();

    expect($event->detected_brand_id)->toBeNull();
    expect($event->detectedBrand)->toBeNull();
});
