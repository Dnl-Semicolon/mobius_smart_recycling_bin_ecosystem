<?php

use App\Models\Bin;
use App\Models\Outlet;
use App\Models\PickupRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('unauthenticated user cannot access protected api routes', function () {
    $bin = Bin::factory()->create();
    $outlet = Outlet::factory()->create();
    $pickup = PickupRequest::factory()->create();

    $this->getJson('/api/v1/dashboard')->assertUnauthorized();
    $this->getJson("/api/v1/bins/{$bin->id}")->assertUnauthorized();
    $this->postJson('/api/v1/bins', ['serial_number' => 'MBR-AUTH-001'])->assertUnauthorized();
    $this->postJson("/api/v1/bins/{$bin->id}/assign", ['outlet_id' => $outlet->id])->assertUnauthorized();
    $this->getJson('/api/v1/outlets')->assertUnauthorized();
    $this->postJson('/api/v1/persons', [])->assertUnauthorized();
    $this->getJson('/api/v1/detection-events')->assertUnauthorized();
    $this->getJson('/api/v1/collector/pickups')->assertUnauthorized();
    $this->postJson("/api/v1/collector/pickups/{$pickup->id}/claim")->assertUnauthorized();
});

test('detect endpoint remains public', function () {
    $this->postJson('/api/v1/detect', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['bin_id', 'image']);
});

test('collector can access shared read routes but not admin mutation routes', function () {
    $collector = User::factory()->collector()->create();
    $bin = Bin::factory()->create();
    $outlet = Outlet::factory()->create();

    Sanctum::actingAs($collector);

    $this->getJson('/api/v1/dashboard')->assertOk();
    $this->getJson('/api/v1/bins')->assertOk();
    $this->getJson('/api/v1/outlets')->assertOk();

    $this->postJson('/api/v1/bins', ['serial_number' => 'MBR-FORBID-001'])->assertForbidden();
    $this->postJson("/api/v1/bins/{$bin->id}/assign", ['outlet_id' => $outlet->id])->assertForbidden();
    $this->postJson('/api/v1/outlets', ['name' => 'New Outlet', 'address' => 'Address'])->assertForbidden();
    $this->postJson('/api/v1/persons', [])->assertForbidden();
});

test('admin can access admin mutation routes but not collector pickup routes', function () {
    $admin = User::factory()->admin()->create();
    $pickup = PickupRequest::factory()->create();

    Sanctum::actingAs($admin);

    $this->postJson('/api/v1/bins', ['serial_number' => 'MBR-ADMIN-001'])->assertCreated();
    $this->postJson('/api/v1/outlets', ['name' => 'Admin Outlet', 'address' => 'Address'])->assertCreated();
    $this->postJson('/api/v1/persons', [
        'name' => 'Admin User',
        'birthday' => '1990-01-01',
        'phone' => '012-345 6789',
        'line_1' => 'Line 1',
        'city' => 'Penang',
        'state' => 'Penang',
        'postal_code' => '10000',
    ])->assertCreated();

    $this->getJson('/api/v1/collector/pickups')->assertForbidden();
    $this->postJson("/api/v1/collector/pickups/{$pickup->id}/claim")->assertForbidden();
});

test('public user cannot access admin collector shared routes', function () {
    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/v1/dashboard')->assertForbidden();
    $this->getJson('/api/v1/bins')->assertForbidden();
    $this->getJson('/api/v1/outlets')->assertForbidden();
    $this->getJson('/api/v1/detection-events')->assertForbidden();
    $this->getJson('/api/v1/collector/pickups')->assertForbidden();
    $this->postJson('/api/v1/persons', [])->assertForbidden();
});
