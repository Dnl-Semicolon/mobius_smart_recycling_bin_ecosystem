<?php

use App\Enums\ApplicationStatus;
use App\Models\CollectorAgency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows agency registration form', function () {
    $this->get(route('registration.agency.create'))
        ->assertOk()
        ->assertSee('Collector Agency Registration')
        ->assertSee('Company Information')
        ->assertSee('Fleet Size');
});

it('registers an agency application', function () {
    $this->post(route('registration.agency.store'), [
        'name' => 'CleanCycle Sdn Bhd',
        'contact_person' => 'Ali Ahmad',
        'email' => 'ali@cleancycle.my',
        'phone' => '+60123456789',
        'description' => 'Waste collection services',
        'fleet_size' => 10,
        'coverage_area' => 'Penang Island',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect(route('registration.success'));

    $agency = CollectorAgency::where('name', 'CleanCycle Sdn Bhd')->first();
    expect($agency)->not->toBeNull()
        ->and($agency->status)->toBe(ApplicationStatus::Pending)
        ->and($agency->fleet_size)->toBe(10);

    $user = User::where('email', 'ali@cleancycle.my')->first();
    expect($user)->not->toBeNull()
        ->and($agency->user_id)->toBe($user->id);
});

it('validates required fields', function () {
    $this->post(route('registration.agency.store'), [])
        ->assertSessionHasErrors(['name', 'contact_person', 'email', 'password']);
});

it('rejects duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->post(route('registration.agency.store'), [
        'name' => 'Duplicate Agency',
        'contact_person' => 'Test',
        'email' => 'taken@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasErrors(['email']);
});
