<?php

use App\Enums\WasteType;
use App\Models\Bin;
use App\Models\BinAssignment;
use App\Models\DetectionEvent;
use App\Models\Outlet;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('dashboard returns all statistics', function () {
    $response = $this->getJson('/api/v1/dashboard');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'outlets' => [
                    'total',
                    'active',
                    'inactive',
                    'pending',
                ],
                'bins' => [
                    'total',
                    'active',
                    'inactive',
                    'maintenance',
                    'ready_for_pickup',
                    'assigned',
                    'unassigned',
                ],
                'detections' => [
                    'total',
                    'today',
                    'this_week',
                    'by_waste_type',
                ],
            ],
            'message',
        ])
        ->assertJsonPath('message', 'Dashboard statistics retrieved successfully.');
});

test('dashboard returns correct outlet counts', function () {
    Outlet::factory()->count(3)->active()->create();
    Outlet::factory()->count(2)->inactive()->create();
    Outlet::factory()->count(1)->pending()->create();

    $response = $this->getJson('/api/v1/dashboard');

    $response->assertOk()
        ->assertJsonPath('data.outlets.total', 6)
        ->assertJsonPath('data.outlets.active', 3)
        ->assertJsonPath('data.outlets.inactive', 2)
        ->assertJsonPath('data.outlets.pending', 1);
});

test('dashboard returns correct bin counts', function () {
    Bin::factory()->count(5)->active()->create();
    Bin::factory()->count(2)->inactive()->create();
    Bin::factory()->count(1)->maintenance()->create();

    $response = $this->getJson('/api/v1/dashboard');

    $response->assertOk()
        ->assertJsonPath('data.bins.total', 8)
        ->assertJsonPath('data.bins.active', 5)
        ->assertJsonPath('data.bins.inactive', 2)
        ->assertJsonPath('data.bins.maintenance', 1);
});

test('dashboard returns correct bins ready for pickup count', function () {
    // Bins ready for pickup (active + fill >= 80)
    Bin::factory()->count(3)->active()->full()->create();

    // Active bins not ready (fill < 80)
    Bin::factory()->count(2)->active()->withFillLevel(50)->create();

    // Inactive bins with high fill (should not count)
    Bin::factory()->count(1)->inactive()->full()->create();

    $response = $this->getJson('/api/v1/dashboard');

    $response->assertOk()
        ->assertJsonPath('data.bins.ready_for_pickup', 3);
});

test('dashboard returns correct assigned and unassigned bin counts', function () {
    $outlet = Outlet::factory()->create();

    // Assigned bins
    $assignedBins = Bin::factory()->count(4)->create();
    foreach ($assignedBins as $bin) {
        BinAssignment::factory()->create([
            'bin_id' => $bin->id,
            'outlet_id' => $outlet->id,
            'unassigned_at' => null,
        ]);
    }

    // Unassigned bins
    Bin::factory()->count(3)->create();

    $response = $this->getJson('/api/v1/dashboard');

    $response->assertOk()
        ->assertJsonPath('data.bins.total', 7)
        ->assertJsonPath('data.bins.assigned', 4)
        ->assertJsonPath('data.bins.unassigned', 3);
});

test('dashboard returns correct detection event counts', function () {
    $bin = Bin::factory()->create();

    // Events today
    DetectionEvent::factory()->count(5)->create([
        'bin_id' => $bin->id,
        'detected_at' => now(),
    ]);

    // Events earlier this week
    DetectionEvent::factory()->count(3)->create([
        'bin_id' => $bin->id,
        'detected_at' => now()->startOfWeek()->addDay(),
    ]);

    // Events last week
    DetectionEvent::factory()->count(2)->create([
        'bin_id' => $bin->id,
        'detected_at' => now()->subWeek(),
    ]);

    $response = $this->getJson('/api/v1/dashboard');

    $response->assertOk()
        ->assertJsonPath('data.detections.total', 10)
        ->assertJsonPath('data.detections.today', 5)
        ->assertJsonPath('data.detections.this_week', 8);
});

test('dashboard returns detection counts by waste type', function () {
    $bin = Bin::factory()->create();

    DetectionEvent::factory()->count(5)->create([
        'bin_id' => $bin->id,
        'waste_type' => WasteType::PaperCup,
    ]);

    DetectionEvent::factory()->count(3)->create([
        'bin_id' => $bin->id,
        'waste_type' => WasteType::PlasticCup,
    ]);

    DetectionEvent::factory()->count(2)->create([
        'bin_id' => $bin->id,
        'waste_type' => WasteType::Lid,
    ]);

    $response = $this->getJson('/api/v1/dashboard');

    $response->assertOk();

    $wasteTypes = $response->json('data.detections.by_waste_type');
    expect($wasteTypes)->toHaveKey('paper_cup')
        ->and($wasteTypes['paper_cup'])->toBe(5)
        ->and($wasteTypes['plastic_cup'])->toBe(3)
        ->and($wasteTypes['lid'])->toBe(2);
});

test('dashboard returns zeros when no data exists', function () {
    $response = $this->getJson('/api/v1/dashboard');

    $response->assertOk()
        ->assertJsonPath('data.outlets.total', 0)
        ->assertJsonPath('data.bins.total', 0)
        ->assertJsonPath('data.bins.ready_for_pickup', 0)
        ->assertJsonPath('data.detections.total', 0)
        ->assertJsonPath('data.detections.today', 0);
});
