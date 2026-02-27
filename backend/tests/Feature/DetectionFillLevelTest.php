<?php

use App\Enums\WasteType;
use App\Models\Bin;
use App\Models\DetectionEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('creating a paper_cup detection bumps bin fill level by 5', function () {
    $bin = Bin::factory()->create(['fill_level' => 10]);

    DetectionEvent::factory()->create([
        'bin_id' => $bin->id,
        'waste_type' => WasteType::PaperCup,
    ]);

    expect($bin->fresh()->fill_level)->toBe(15);
});

test('creating a plastic_cup detection bumps bin fill level by 5', function () {
    $bin = Bin::factory()->create(['fill_level' => 20]);

    DetectionEvent::factory()->create([
        'bin_id' => $bin->id,
        'waste_type' => WasteType::PlasticCup,
    ]);

    expect($bin->fresh()->fill_level)->toBe(25);
});

test('creating a lid detection bumps bin fill level by 2', function () {
    $bin = Bin::factory()->create(['fill_level' => 50]);

    DetectionEvent::factory()->create([
        'bin_id' => $bin->id,
        'waste_type' => WasteType::Lid,
    ]);

    expect($bin->fresh()->fill_level)->toBe(52);
});

test('creating a straw detection bumps bin fill level by 1', function () {
    $bin = Bin::factory()->create(['fill_level' => 50]);

    DetectionEvent::factory()->create([
        'bin_id' => $bin->id,
        'waste_type' => WasteType::Straw,
    ]);

    expect($bin->fresh()->fill_level)->toBe(51);
});

test('creating a liquid_waste detection bumps bin fill level by 3', function () {
    $bin = Bin::factory()->create(['fill_level' => 40]);

    DetectionEvent::factory()->create([
        'bin_id' => $bin->id,
        'waste_type' => WasteType::LiquidWaste,
    ]);

    expect($bin->fresh()->fill_level)->toBe(43);
});

test('fill level caps at 100', function () {
    $bin = Bin::factory()->create(['fill_level' => 98]);

    DetectionEvent::factory()->create([
        'bin_id' => $bin->id,
        'waste_type' => WasteType::PaperCup,
    ]);

    expect($bin->fresh()->fill_level)->toBe(100);
});

test('detection with null waste_type does not change fill level', function () {
    $bin = Bin::factory()->create(['fill_level' => 30]);

    DetectionEvent::factory()->create([
        'bin_id' => $bin->id,
        'waste_type' => null,
    ]);

    expect($bin->fresh()->fill_level)->toBe(30);
});

test('multiple detections accumulate fill level', function () {
    $bin = Bin::factory()->create(['fill_level' => 0]);

    // 3 paper cups = +15
    DetectionEvent::factory()->count(3)->create([
        'bin_id' => $bin->id,
        'waste_type' => WasteType::PaperCup,
    ]);

    expect($bin->fresh()->fill_level)->toBe(15);
});
