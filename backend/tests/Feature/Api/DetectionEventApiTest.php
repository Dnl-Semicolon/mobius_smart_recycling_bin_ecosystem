<?php

use App\Enums\WasteType;
use App\Models\Bin;
use App\Models\BinAssignment;
use App\Models\DetectionEvent;
use App\Models\Outlet;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Index tests
test('index returns paginated detection events', function () {
    $bin = Bin::factory()->create();
    DetectionEvent::factory()->count(20)->create(['bin_id' => $bin->id]);

    $response = $this->getJson('/api/v1/detection-events');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'bin_id',
                    'waste_type',
                    'confidence',
                    'detected_at',
                ],
            ],
            'links',
            'meta',
            'message',
        ]);

    expect($response->json('data'))->toHaveCount(15);
});

test('index returns empty list when no events exist', function () {
    $response = $this->getJson('/api/v1/detection-events');

    $response->assertOk()
        ->assertJsonPath('data', [])
        ->assertJsonPath('message', 'Detection events retrieved successfully.');
});

test('index filters by bin_id', function () {
    $bin1 = Bin::factory()->create();
    $bin2 = Bin::factory()->create();

    DetectionEvent::factory()->count(5)->create(['bin_id' => $bin1->id]);
    DetectionEvent::factory()->count(3)->create(['bin_id' => $bin2->id]);

    $response = $this->getJson("/api/v1/detection-events?bin_id={$bin1->id}");

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(5);

    foreach ($response->json('data') as $event) {
        expect($event['bin_id'])->toBe($bin1->id);
    }
});

test('index filters by waste_type', function () {
    $bin = Bin::factory()->create();

    DetectionEvent::factory()->count(5)->create([
        'bin_id' => $bin->id,
        'waste_type' => WasteType::PaperCup,
    ]);
    DetectionEvent::factory()->count(3)->create([
        'bin_id' => $bin->id,
        'waste_type' => WasteType::PlasticCup,
    ]);

    $response = $this->getJson('/api/v1/detection-events?waste_type=paper_cup');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(5);

    foreach ($response->json('data') as $event) {
        expect($event['waste_type'])->toBe('paper_cup');
    }
});

test('index filters by minimum confidence', function () {
    $bin = Bin::factory()->create();

    DetectionEvent::factory()->count(5)->create([
        'bin_id' => $bin->id,
        'confidence' => 95,
    ]);
    DetectionEvent::factory()->count(3)->create([
        'bin_id' => $bin->id,
        'confidence' => 75,
    ]);

    $response = $this->getJson('/api/v1/detection-events?min_confidence=90');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(5);

    foreach ($response->json('data') as $event) {
        expect($event['confidence'])->toBeGreaterThanOrEqual(90);
    }
});

test('index filters by date range', function () {
    $bin = Bin::factory()->create();

    DetectionEvent::factory()->count(5)->create([
        'bin_id' => $bin->id,
        'detected_at' => now()->subDays(2),
    ]);
    DetectionEvent::factory()->count(3)->create([
        'bin_id' => $bin->id,
        'detected_at' => now()->subDays(10),
    ]);

    $from = now()->subDays(5)->toDateString();
    $to = now()->toDateString();

    $response = $this->getJson("/api/v1/detection-events?from={$from}&to={$to}");

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(5);
});

test('index can combine multiple filters', function () {
    $bin1 = Bin::factory()->create();
    $bin2 = Bin::factory()->create();

    // Events that should match
    DetectionEvent::factory()->count(3)->create([
        'bin_id' => $bin1->id,
        'waste_type' => WasteType::PaperCup,
        'confidence' => 95,
    ]);

    // Events that should not match (different bin)
    DetectionEvent::factory()->count(2)->create([
        'bin_id' => $bin2->id,
        'waste_type' => WasteType::PaperCup,
        'confidence' => 95,
    ]);

    // Events that should not match (different waste type)
    DetectionEvent::factory()->count(2)->create([
        'bin_id' => $bin1->id,
        'waste_type' => WasteType::PlasticCup,
        'confidence' => 95,
    ]);

    // Events that should not match (low confidence)
    DetectionEvent::factory()->count(2)->create([
        'bin_id' => $bin1->id,
        'waste_type' => WasteType::PaperCup,
        'confidence' => 70,
    ]);

    $response = $this->getJson("/api/v1/detection-events?bin_id={$bin1->id}&waste_type=paper_cup&min_confidence=90");

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(3);
});

test('index includes bin information', function () {
    $bin = Bin::factory()->create(['serial_number' => 'MBR-TEST-001']);
    DetectionEvent::factory()->create(['bin_id' => $bin->id]);

    $response = $this->getJson('/api/v1/detection-events');

    $response->assertOk()
        ->assertJsonPath('data.0.bin.serial_number', 'MBR-TEST-001');
});

// Show tests
test('show returns detection event with bin info', function () {
    $outlet = Outlet::factory()->create(['name' => 'Test Outlet']);
    $bin = Bin::factory()->create();

    BinAssignment::factory()->create([
        'bin_id' => $bin->id,
        'outlet_id' => $outlet->id,
        'unassigned_at' => null,
    ]);

    $event = DetectionEvent::factory()->create([
        'bin_id' => $bin->id,
        'waste_type' => WasteType::PaperCup,
        'confidence' => 95,
    ]);

    $response = $this->getJson("/api/v1/detection-events/{$event->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $event->id)
        ->assertJsonPath('data.waste_type', 'paper_cup')
        ->assertJsonPath('data.confidence', 95)
        ->assertJsonPath('message', 'Detection event retrieved successfully.')
        ->assertJsonStructure([
            'data' => [
                'id',
                'bin_id',
                'waste_type',
                'confidence',
                'detected_at',
                'bin' => [
                    'id',
                    'serial_number',
                    'current_assignment' => [
                        'outlet',
                    ],
                ],
            ],
        ]);
});

test('show returns 404 for missing event', function () {
    $response = $this->getJson('/api/v1/detection-events/999');

    $response->assertNotFound();
});

// Read-only verification tests
test('store endpoint does not exist', function () {
    $bin = Bin::factory()->create();

    $response = $this->postJson('/api/v1/detection-events', [
        'bin_id' => $bin->id,
        'waste_type' => 'paper_cup',
        'confidence' => 95,
    ]);

    $response->assertStatus(405); // Method Not Allowed
});

test('update endpoint does not exist', function () {
    $bin = Bin::factory()->create();
    $event = DetectionEvent::factory()->create(['bin_id' => $bin->id]);

    $response = $this->putJson("/api/v1/detection-events/{$event->id}", [
        'confidence' => 99,
    ]);

    $response->assertStatus(405); // Method Not Allowed
});

test('destroy endpoint does not exist', function () {
    $bin = Bin::factory()->create();
    $event = DetectionEvent::factory()->create(['bin_id' => $bin->id]);

    $response = $this->deleteJson("/api/v1/detection-events/{$event->id}");

    $response->assertStatus(405); // Method Not Allowed
});
