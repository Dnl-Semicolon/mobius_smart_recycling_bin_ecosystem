<?php

use App\Enums\BinStatus;
use App\Models\Bin;
use App\Models\BinAssignment;
use App\Models\DetectionEvent;
use App\Models\Outlet;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Index tests
test('index returns paginated bins', function () {
    Bin::factory()->count(20)->create();

    $response = $this->getJson('/api/v1/bins');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'serial_number',
                    'fill_level',
                    'status',
                    'is_ready_for_pickup',
                ],
            ],
            'links',
            'meta',
            'message',
        ]);

    expect($response->json('data'))->toHaveCount(15);
});

test('index returns empty list when no bins exist', function () {
    $response = $this->getJson('/api/v1/bins');

    $response->assertOk()
        ->assertJsonPath('data', [])
        ->assertJsonPath('message', 'Bins retrieved successfully.');
});

test('index includes current assignment with outlet', function () {
    $outlet = Outlet::factory()->create(['name' => 'Test Outlet']);
    $bin = Bin::factory()->create();

    BinAssignment::factory()->create([
        'bin_id' => $bin->id,
        'outlet_id' => $outlet->id,
        'unassigned_at' => null,
    ]);

    $response = $this->getJson('/api/v1/bins');

    $response->assertOk()
        ->assertJsonPath('data.0.current_assignment.outlet.name', 'Test Outlet');
});

// Show tests
test('show returns bin with assignment history and recent detections', function () {
    $bin = Bin::factory()->create();
    $outlet = Outlet::factory()->create();

    BinAssignment::factory()->create([
        'bin_id' => $bin->id,
        'outlet_id' => $outlet->id,
        'unassigned_at' => null,
    ]);

    DetectionEvent::factory()->count(5)->create(['bin_id' => $bin->id]);

    $response = $this->getJson("/api/v1/bins/{$bin->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $bin->id)
        ->assertJsonPath('message', 'Bin retrieved successfully.')
        ->assertJsonStructure([
            'data' => [
                'id',
                'serial_number',
                'fill_level',
                'status',
                'current_assignment',
                'assignments',
                'recent_detections',
            ],
        ]);

    expect($response->json('data.recent_detections'))->toHaveCount(5);
});

test('show returns 404 for missing bin', function () {
    $response = $this->getJson('/api/v1/bins/999');

    $response->assertNotFound();
});

// Store tests
test('store creates bin with valid data', function () {
    $data = [
        'serial_number' => 'MBR-TEST-001',
        'fill_level' => 50,
        'status' => 'active',
    ];

    $response = $this->postJson('/api/v1/bins', $data);

    $response->assertCreated()
        ->assertJsonPath('data.serial_number', 'MBR-TEST-001')
        ->assertJsonPath('data.fill_level', 50)
        ->assertJsonPath('data.status', 'active')
        ->assertJsonPath('message', 'Bin created successfully.');

    $this->assertDatabaseHas('bins', [
        'serial_number' => 'MBR-TEST-001',
    ]);
});

test('store creates bin with minimal required data', function () {
    $data = [
        'serial_number' => 'MBR-MINIMAL-001',
    ];

    $response = $this->postJson('/api/v1/bins', $data);

    $response->assertCreated()
        ->assertJsonPath('data.serial_number', 'MBR-MINIMAL-001')
        ->assertJsonPath('data.fill_level', 0)
        ->assertJsonPath('data.status', 'active');
});

test('store fails without required serial number', function () {
    $data = [
        'fill_level' => 50,
    ];

    $response = $this->postJson('/api/v1/bins', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['serial_number']);
});

test('store fails with duplicate serial number', function () {
    Bin::factory()->create(['serial_number' => 'MBR-DUPE-001']);

    $data = [
        'serial_number' => 'MBR-DUPE-001',
    ];

    $response = $this->postJson('/api/v1/bins', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['serial_number']);
});

test('store fails with invalid fill level', function () {
    $data = [
        'serial_number' => 'MBR-TEST-001',
        'fill_level' => 150, // Invalid: must be 0-100
    ];

    $response = $this->postJson('/api/v1/bins', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['fill_level']);
});

test('store fails with invalid status', function () {
    $data = [
        'serial_number' => 'MBR-TEST-001',
        'status' => 'invalid_status',
    ];

    $response = $this->postJson('/api/v1/bins', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);
});

// Update tests
test('update modifies bin', function () {
    $bin = Bin::factory()->create([
        'fill_level' => 30,
        'status' => BinStatus::Active,
    ]);

    $response = $this->putJson("/api/v1/bins/{$bin->id}", [
        'fill_level' => 75,
        'status' => 'maintenance',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.fill_level', 75)
        ->assertJsonPath('data.status', 'maintenance')
        ->assertJsonPath('message', 'Bin updated successfully.');

    $this->assertDatabaseHas('bins', [
        'id' => $bin->id,
        'fill_level' => 75,
        'status' => 'maintenance',
    ]);
});

test('update does not allow changing serial number', function () {
    $bin = Bin::factory()->create(['serial_number' => 'MBR-ORIGINAL-001']);

    $response = $this->putJson("/api/v1/bins/{$bin->id}", [
        'serial_number' => 'MBR-CHANGED-001',
        'fill_level' => 50,
    ]);

    $response->assertOk();

    // Serial number should remain unchanged
    $this->assertDatabaseHas('bins', [
        'id' => $bin->id,
        'serial_number' => 'MBR-ORIGINAL-001',
    ]);
});

test('update returns 404 for missing bin', function () {
    $response = $this->putJson('/api/v1/bins/999', [
        'fill_level' => 50,
    ]);

    $response->assertNotFound();
});

// Destroy tests
test('destroy soft deletes bin', function () {
    $bin = Bin::factory()->create();

    $response = $this->deleteJson("/api/v1/bins/{$bin->id}");

    $response->assertOk()
        ->assertJsonPath('message', 'Bin deleted successfully.');

    $this->assertSoftDeleted('bins', ['id' => $bin->id]);
});

test('destroy returns 404 for missing bin', function () {
    $response = $this->deleteJson('/api/v1/bins/999');

    $response->assertNotFound();
});

test('deleted bin is not returned in index', function () {
    $bin = Bin::factory()->create();
    $bin->delete();

    $response = $this->getJson('/api/v1/bins');

    $response->assertOk()
        ->assertJsonPath('data', []);
});

// Assign tests
test('assign creates new assignment', function () {
    $bin = Bin::factory()->create();
    $outlet = Outlet::factory()->create();

    $response = $this->postJson("/api/v1/bins/{$bin->id}/assign", [
        'outlet_id' => $outlet->id,
    ]);

    $response->assertOk()
        ->assertJsonPath('message', 'Bin assigned to outlet successfully.')
        ->assertJsonPath('data.current_assignment.outlet_id', $outlet->id);

    $this->assertDatabaseHas('bin_assignments', [
        'bin_id' => $bin->id,
        'outlet_id' => $outlet->id,
        'unassigned_at' => null,
    ]);
});

test('assign ends previous assignment before creating new one', function () {
    $bin = Bin::factory()->create();
    $oldOutlet = Outlet::factory()->create();
    $newOutlet = Outlet::factory()->create();

    // Create existing assignment
    $oldAssignment = BinAssignment::factory()->create([
        'bin_id' => $bin->id,
        'outlet_id' => $oldOutlet->id,
        'unassigned_at' => null,
    ]);

    $response = $this->postJson("/api/v1/bins/{$bin->id}/assign", [
        'outlet_id' => $newOutlet->id,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.current_assignment.outlet_id', $newOutlet->id);

    // Old assignment should be ended
    $oldAssignment->refresh();
    expect($oldAssignment->unassigned_at)->not->toBeNull();

    // New assignment should exist
    $this->assertDatabaseHas('bin_assignments', [
        'bin_id' => $bin->id,
        'outlet_id' => $newOutlet->id,
        'unassigned_at' => null,
    ]);
});

test('assign fails without outlet_id', function () {
    $bin = Bin::factory()->create();

    $response = $this->postJson("/api/v1/bins/{$bin->id}/assign", []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['outlet_id']);
});

test('assign fails with non-existent outlet', function () {
    $bin = Bin::factory()->create();

    $response = $this->postJson("/api/v1/bins/{$bin->id}/assign", [
        'outlet_id' => 999,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['outlet_id']);
});

// Unassign tests
test('unassign ends current assignment', function () {
    $bin = Bin::factory()->create();
    $outlet = Outlet::factory()->create();

    $assignment = BinAssignment::factory()->create([
        'bin_id' => $bin->id,
        'outlet_id' => $outlet->id,
        'unassigned_at' => null,
    ]);

    $response = $this->postJson("/api/v1/bins/{$bin->id}/unassign");

    $response->assertOk()
        ->assertJsonPath('message', 'Bin unassigned from outlet successfully.');

    $assignment->refresh();
    expect($assignment->unassigned_at)->not->toBeNull();
});

test('unassign fails when bin has no current assignment', function () {
    $bin = Bin::factory()->create();

    $response = $this->postJson("/api/v1/bins/{$bin->id}/unassign");

    $response->assertUnprocessable()
        ->assertJsonPath('message', 'Bin is not currently assigned to any outlet.');
});

// Ready for pickup tests
test('bin with fill level 80 or above is ready for pickup', function () {
    $bin = Bin::factory()->create(['fill_level' => 85]);

    $response = $this->getJson("/api/v1/bins/{$bin->id}");

    $response->assertOk()
        ->assertJsonPath('data.is_ready_for_pickup', true);
});

test('bin with fill level below 80 is not ready for pickup', function () {
    $bin = Bin::factory()->create(['fill_level' => 79]);

    $response = $this->getJson("/api/v1/bins/{$bin->id}");

    $response->assertOk()
        ->assertJsonPath('data.is_ready_for_pickup', false);
});
