<?php

use App\Models\CollectorAgency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires agency_admin role to access agency dashboard', function () {
    $user = User::factory()->publicUser()->create();

    $this->actingAs($user)
        ->get(route('agency.dashboard'))
        ->assertForbidden();
});

it('shows agency dashboard for agency admin', function () {
    $user = User::factory()->agencyAdmin()->create();
    $agency = CollectorAgency::factory()->approved()->create([
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('agency.dashboard'))
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee('Total Collectors');
});

it('shows collectors page', function () {
    $user = User::factory()->agencyAdmin()->create();
    $agency = CollectorAgency::factory()->approved()->create([
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('agency.collectors.index'))
        ->assertOk()
        ->assertSee('Collectors');
});

it('can invite a collector by email', function () {
    $user = User::factory()->agencyAdmin()->create();
    $agency = CollectorAgency::factory()->approved()->create([
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->post(route('agency.collectors.invite'), [
            'email' => 'newcollector@example.com',
        ])
        ->assertRedirect();

    expect(User::where('email', 'newcollector@example.com')->exists())->toBeTrue();
    expect($agency->collectors()->where('users.email', 'newcollector@example.com')->exists())->toBeTrue();
});

it('prevents inviting existing agency member', function () {
    $user = User::factory()->agencyAdmin()->create();
    $agency = CollectorAgency::factory()->approved()->create([
        'user_id' => $user->id,
    ]);
    $collector = User::factory()->collector()->create();
    $agency->collectors()->attach($collector->id, ['status' => 'active']);

    $this->actingAs($user)
        ->post(route('agency.collectors.invite'), [
            'email' => $collector->email,
        ])
        ->assertRedirect()
        ->assertSessionHas('error');
});

it('shows routes page', function () {
    $user = User::factory()->agencyAdmin()->create();
    $agency = CollectorAgency::factory()->approved()->create([
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('agency.routes.index'))
        ->assertOk()
        ->assertSee('Route History');
});

it('shows analytics page', function () {
    $user = User::factory()->agencyAdmin()->create();
    $agency = CollectorAgency::factory()->approved()->create([
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('agency.analytics'))
        ->assertOk()
        ->assertSee('Analytics');
});
