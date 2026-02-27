<?php

use App\Models\Bin;
use App\Models\PickupRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can view pickup requests index', function () {
    $admin = User::factory()->admin()->create();
    PickupRequest::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('admin.pickup-requests.index'))
        ->assertSuccessful()
        ->assertSee('Pickup Requests');
});

test('admin can filter pickup requests by status', function () {
    $admin = User::factory()->admin()->create();
    PickupRequest::factory()->create();
    PickupRequest::factory()->claimed()->create();

    $this->actingAs($admin)
        ->get(route('admin.pickup-requests.index', ['status' => 'pending']))
        ->assertSuccessful();
});

test('admin can view a single pickup request', function () {
    $admin = User::factory()->admin()->create();
    $bin = Bin::factory()->create(['serial_number' => 'MBR-TEST-999']);
    $pickup = PickupRequest::factory()->create(['bin_id' => $bin->id]);

    $this->actingAs($admin)
        ->get(route('admin.pickup-requests.show', $pickup))
        ->assertSuccessful()
        ->assertSee('MBR-TEST-999')
        ->assertSee('Timeline');
});

test('show page displays claimed info when pickup is claimed', function () {
    $admin = User::factory()->admin()->create();
    $collector = User::factory()->collector()->create(['name' => 'Jane Collector']);
    $pickup = PickupRequest::factory()->claimed($collector)->create();

    $this->actingAs($admin)
        ->get(route('admin.pickup-requests.show', $pickup))
        ->assertSuccessful()
        ->assertSee('Jane Collector');
});

test('collector cannot access admin pickup requests', function () {
    $collector = User::factory()->collector()->create();

    $this->actingAs($collector)
        ->get(route('admin.pickup-requests.index'))
        ->assertForbidden();
});

test('guest is redirected to login', function () {
    $this->get(route('admin.pickup-requests.index'))
        ->assertRedirect(route('login'));
});

test('admin can cancel a pending pickup', function () {
    $admin = User::factory()->admin()->create();
    $pickup = PickupRequest::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.pickup-requests.cancel', $pickup))
        ->assertRedirect(route('admin.pickup-requests.show', $pickup))
        ->assertSessionHas('success');

    expect($pickup->fresh()->status)->toBe(\App\Enums\PickupStatus::Cancelled);
});

test('admin can cancel a claimed pickup', function () {
    $admin = User::factory()->admin()->create();
    $pickup = PickupRequest::factory()->claimed()->create();

    $this->actingAs($admin)
        ->post(route('admin.pickup-requests.cancel', $pickup))
        ->assertRedirect(route('admin.pickup-requests.show', $pickup))
        ->assertSessionHas('success');

    expect($pickup->fresh()->status)->toBe(\App\Enums\PickupStatus::Cancelled);
});

test('admin can unclaim a claimed pickup (return to queue)', function () {
    $admin = User::factory()->admin()->create();
    $collector = User::factory()->collector()->create();
    $pickup = PickupRequest::factory()->claimed($collector)->create();

    $this->actingAs($admin)
        ->post(route('admin.pickup-requests.unclaim', $pickup))
        ->assertRedirect(route('admin.pickup-requests.show', $pickup))
        ->assertSessionHas('success');

    $fresh = $pickup->fresh();
    expect($fresh->status)->toBe(\App\Enums\PickupStatus::Pending)
        ->and($fresh->claimed_by)->toBeNull()
        ->and($fresh->claimed_at)->toBeNull();
});

test('admin cannot cancel a completed pickup', function () {
    $admin = User::factory()->admin()->create();
    $pickup = PickupRequest::factory()->completed()->create();

    $this->actingAs($admin)
        ->post(route('admin.pickup-requests.cancel', $pickup))
        ->assertRedirect(route('admin.pickup-requests.show', $pickup))
        ->assertSessionHas('error');

    expect($pickup->fresh()->status)->toBe(\App\Enums\PickupStatus::Completed);
});

test('admin cannot unclaim a pending pickup', function () {
    $admin = User::factory()->admin()->create();
    $pickup = PickupRequest::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.pickup-requests.unclaim', $pickup))
        ->assertRedirect(route('admin.pickup-requests.show', $pickup))
        ->assertSessionHas('error');

    expect($pickup->fresh()->status)->toBe(\App\Enums\PickupStatus::Pending);
});

test('show page displays action buttons for pending pickup', function () {
    $admin = User::factory()->admin()->create();
    $pickup = PickupRequest::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.pickup-requests.show', $pickup))
        ->assertSuccessful()
        ->assertSee('Cancel Pickup');
});

test('show page displays both action buttons for claimed pickup', function () {
    $admin = User::factory()->admin()->create();
    $pickup = PickupRequest::factory()->claimed()->create();

    $this->actingAs($admin)
        ->get(route('admin.pickup-requests.show', $pickup))
        ->assertSuccessful()
        ->assertSee('Cancel Pickup')
        ->assertSee('Return to Queue');
});

test('show page does not display action buttons for completed pickup', function () {
    $admin = User::factory()->admin()->create();
    $pickup = PickupRequest::factory()->completed()->create();

    $this->actingAs($admin)
        ->get(route('admin.pickup-requests.show', $pickup))
        ->assertSuccessful()
        ->assertDontSee('Cancel Pickup')
        ->assertDontSee('Return to Queue');
});
