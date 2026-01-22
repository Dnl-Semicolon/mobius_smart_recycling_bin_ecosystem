<?php

use App\Enums\ContractStatus;
use App\Models\Bin;
use App\Models\BinAssignment;
use App\Models\Outlet;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('index returns paginated outlets', function () {
    Outlet::factory()->count(20)->create();

    $response = $this->getJson('/api/v1/outlets');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'address',
                    'contract_status',
                ],
            ],
            'links',
            'meta',
            'message',
        ]);

    expect($response->json('data'))->toHaveCount(15);
});

test('index returns empty list when no outlets exist', function () {
    $response = $this->getJson('/api/v1/outlets');

    $response->assertOk()
        ->assertJsonPath('data', [])
        ->assertJsonPath('message', 'Outlets retrieved successfully.');
});

test('index includes current bins count', function () {
    $outlet = Outlet::factory()->create();
    $bins = Bin::factory()->count(3)->create();

    foreach ($bins as $bin) {
        BinAssignment::factory()->create([
            'bin_id' => $bin->id,
            'outlet_id' => $outlet->id,
            'unassigned_at' => null,
        ]);
    }

    $response = $this->getJson('/api/v1/outlets');

    $response->assertOk();
    expect($response->json('data.0.current_bins_count'))->toBe(3);
});

test('show returns outlet with bins', function () {
    $outlet = Outlet::factory()->create();
    $bin = Bin::factory()->create();

    BinAssignment::factory()->create([
        'bin_id' => $bin->id,
        'outlet_id' => $outlet->id,
        'unassigned_at' => null,
    ]);

    $response = $this->getJson("/api/v1/outlets/{$outlet->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $outlet->id)
        ->assertJsonPath('data.name', $outlet->name)
        ->assertJsonPath('message', 'Outlet retrieved successfully.')
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'address',
                'bins' => [
                    '*' => [
                        'id',
                        'serial_number',
                        'fill_level',
                        'status',
                    ],
                ],
            ],
        ]);
});

test('show returns 404 for missing outlet', function () {
    $response = $this->getJson('/api/v1/outlets/999');

    $response->assertNotFound();
});

test('store creates outlet with valid data', function () {
    $data = [
        'name' => 'New Coffee Shop',
        'address' => '123 Main Street, KL',
        'latitude' => 3.1390,
        'longitude' => 101.6869,
        'contact_name' => 'John Doe',
        'contact_phone' => '012-345 6789',
        'contact_email' => 'john@example.com',
        'operating_hours' => '09:00-21:00',
        'contract_status' => 'active',
        'notes' => 'Test outlet',
    ];

    $response = $this->postJson('/api/v1/outlets', $data);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'New Coffee Shop')
        ->assertJsonPath('data.address', '123 Main Street, KL')
        ->assertJsonPath('data.contract_status', 'active')
        ->assertJsonPath('message', 'Outlet created successfully.');

    $this->assertDatabaseHas('outlets', [
        'name' => 'New Coffee Shop',
        'address' => '123 Main Street, KL',
    ]);
});

test('store creates outlet with minimal required data', function () {
    $data = [
        'name' => 'Minimal Outlet',
        'address' => 'Some Address',
    ];

    $response = $this->postJson('/api/v1/outlets', $data);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Minimal Outlet');
});

test('store fails without required name', function () {
    $data = [
        'address' => '123 Main Street',
    ];

    $response = $this->postJson('/api/v1/outlets', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

test('store fails without required address', function () {
    $data = [
        'name' => 'Test Outlet',
    ];

    $response = $this->postJson('/api/v1/outlets', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['address']);
});

test('store fails with invalid latitude', function () {
    $data = [
        'name' => 'Test',
        'address' => 'Test',
        'latitude' => 100, // Invalid: must be -90 to 90
    ];

    $response = $this->postJson('/api/v1/outlets', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['latitude']);
});

test('store fails with invalid longitude', function () {
    $data = [
        'name' => 'Test',
        'address' => 'Test',
        'longitude' => 200, // Invalid: must be -180 to 180
    ];

    $response = $this->postJson('/api/v1/outlets', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['longitude']);
});

test('store fails with invalid email', function () {
    $data = [
        'name' => 'Test',
        'address' => 'Test',
        'contact_email' => 'not-an-email',
    ];

    $response = $this->postJson('/api/v1/outlets', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['contact_email']);
});

test('store fails with invalid contract status', function () {
    $data = [
        'name' => 'Test',
        'address' => 'Test',
        'contract_status' => 'invalid_status',
    ];

    $response = $this->postJson('/api/v1/outlets', $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['contract_status']);
});

test('update modifies outlet', function () {
    $outlet = Outlet::factory()->create([
        'name' => 'Old Name',
        'contract_status' => ContractStatus::Pending,
    ]);

    $response = $this->putJson("/api/v1/outlets/{$outlet->id}", [
        'name' => 'New Name',
        'contract_status' => 'active',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'New Name')
        ->assertJsonPath('data.contract_status', 'active')
        ->assertJsonPath('message', 'Outlet updated successfully.');

    $this->assertDatabaseHas('outlets', [
        'id' => $outlet->id,
        'name' => 'New Name',
        'contract_status' => 'active',
    ]);
});

test('update allows partial updates', function () {
    $outlet = Outlet::factory()->create([
        'name' => 'Original Name',
        'address' => 'Original Address',
    ]);

    $response = $this->patchJson("/api/v1/outlets/{$outlet->id}", [
        'name' => 'Updated Name',
    ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Updated Name')
        ->assertJsonPath('data.address', 'Original Address');
});

test('update returns 404 for missing outlet', function () {
    $response = $this->putJson('/api/v1/outlets/999', [
        'name' => 'Test',
    ]);

    $response->assertNotFound();
});

test('destroy soft deletes outlet', function () {
    $outlet = Outlet::factory()->create();

    $response = $this->deleteJson("/api/v1/outlets/{$outlet->id}");

    $response->assertOk()
        ->assertJsonPath('message', 'Outlet deleted successfully.');

    $this->assertSoftDeleted('outlets', ['id' => $outlet->id]);
});

test('destroy returns 404 for missing outlet', function () {
    $response = $this->deleteJson('/api/v1/outlets/999');

    $response->assertNotFound();
});

test('deleted outlet is not returned in index', function () {
    $outlet = Outlet::factory()->create();
    $outlet->delete();

    $response = $this->getJson('/api/v1/outlets');

    $response->assertOk()
        ->assertJsonPath('data', []);
});
