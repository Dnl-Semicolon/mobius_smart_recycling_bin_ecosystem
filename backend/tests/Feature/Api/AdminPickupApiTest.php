<?php

use App\Enums\PickupStatus;
use App\Models\PickupRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('admin can list pickup requests', function () {
    PickupRequest::factory()->count(3)->create();

    Sanctum::actingAs(User::factory()->admin()->create());

    $this->getJson(route('api.admin.pickup-requests.index'))
        ->assertOk()
        ->assertJsonPath('message', 'Pickup requests retrieved successfully.')
        ->assertJsonCount(3, 'data');
});

test('admin can filter pickup requests by status', function () {
    PickupRequest::factory()->create();
    PickupRequest::factory()->claimed()->create();

    Sanctum::actingAs(User::factory()->admin()->create());

    $this->getJson(route('api.admin.pickup-requests.index', ['status' => 'pending']))
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

test('admin can view a single pickup request', function () {
    $pickup = PickupRequest::factory()->create();

    Sanctum::actingAs(User::factory()->admin()->create());

    $this->getJson(route('api.admin.pickup-requests.show', $pickup))
        ->assertOk()
        ->assertJsonPath('data.id', $pickup->id)
        ->assertJsonPath('message', 'Pickup request retrieved successfully.');
});

test('admin can cancel a pending pickup', function () {
    $pickup = PickupRequest::factory()->create();

    Sanctum::actingAs(User::factory()->admin()->create());

    $this->postJson(route('api.admin.pickup-requests.cancel', $pickup))
        ->assertOk()
        ->assertJsonPath('data.status', 'cancelled')
        ->assertJsonPath('message', 'Pickup request cancelled successfully.');

    expect($pickup->fresh()->status)->toBe(PickupStatus::Cancelled);
});

test('admin can cancel a claimed pickup', function () {
    $pickup = PickupRequest::factory()->claimed()->create();

    Sanctum::actingAs(User::factory()->admin()->create());

    $this->postJson(route('api.admin.pickup-requests.cancel', $pickup))
        ->assertOk()
        ->assertJsonPath('data.status', 'cancelled');
});

test('admin cannot cancel a completed pickup', function () {
    $pickup = PickupRequest::factory()->completed()->create();

    Sanctum::actingAs(User::factory()->admin()->create());

    $this->postJson(route('api.admin.pickup-requests.cancel', $pickup))
        ->assertStatus(422)
        ->assertJsonPath('message', 'This pickup cannot be cancelled.');
});

test('admin can unclaim a claimed pickup', function () {
    $pickup = PickupRequest::factory()->claimed()->create();

    Sanctum::actingAs(User::factory()->admin()->create());

    $this->postJson(route('api.admin.pickup-requests.unclaim', $pickup))
        ->assertOk()
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('message', 'Pickup request returned to pending queue.');

    $fresh = $pickup->fresh();
    expect($fresh->status)->toBe(PickupStatus::Pending)
        ->and($fresh->claimed_by)->toBeNull();
});

test('admin cannot unclaim a pending pickup', function () {
    $pickup = PickupRequest::factory()->create();

    Sanctum::actingAs(User::factory()->admin()->create());

    $this->postJson(route('api.admin.pickup-requests.unclaim', $pickup))
        ->assertStatus(422)
        ->assertJsonPath('message', 'This pickup cannot be returned to the queue.');
});

test('collector cannot access admin pickup API', function () {
    Sanctum::actingAs(User::factory()->collector()->create());

    $this->getJson(route('api.admin.pickup-requests.index'))
        ->assertForbidden();
});
