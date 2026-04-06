<?php

use App\Models\RegistrationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeLead(array $attributes = []): RegistrationRequest
{
    return RegistrationRequest::create([
        'company_name' => 'Example Co',
        'contact_name' => 'Jane Doe',
        'contact_email' => 'Lead@Example.COM',
        'contact_phone' => '012-345 6789',
        'type' => 'beverage_company',
        'status' => 'pending',
        ...$attributes,
    ]);
}

test('contacts normalize dry run reports changes without persisting them', function () {
    $user = User::factory()->create([
        'email' => 'USER@EXAMPLE.COM',
        'phone' => '012-345 6789',
    ]);

    $lead = makeLead();

    $this->artisan('contacts:normalize --dry-run')
        ->expectsOutputToContain('Dry run complete. No data was changed.')
        ->assertExitCode(0);

    expect($user->refresh()->email)->toBe('USER@EXAMPLE.COM');
    expect($user->refresh()->phone)->toBe('012-345 6789');
    expect($lead->refresh()->contact_email)->toBe('Lead@Example.COM');
    expect($lead->refresh()->contact_phone)->toBe('012-345 6789');
});

test('contacts normalize applies canonical values when data is safe', function () {
    $user = User::factory()->create([
        'email' => 'USER@EXAMPLE.COM',
        'phone' => '012-345 6789',
    ]);

    $lead = makeLead();

    $this->artisan('contacts:normalize')
        ->expectsOutputToContain('Normalization applied successfully.')
        ->assertExitCode(0);

    expect($user->refresh()->email)->toBe('user@example.com');
    expect($user->refresh()->phone)->toBe('+60123456789');
    expect($lead->refresh()->contact_email)->toBe('lead@example.com');
    expect($lead->refresh()->contact_phone)->toBe('+60123456789');
});

test('contacts normalize aborts when user phone collisions would be introduced', function () {
    $first = User::factory()->create([
        'phone' => '0123456789',
        'email' => 'one@example.com',
    ]);

    $second = User::factory()->create([
        'phone' => '+60123456789',
        'email' => 'two@example.com',
    ]);

    $this->artisan('contacts:normalize')
        ->expectsOutputToContain('User phone collision on +60123456789')
        ->expectsOutputToContain('Normalization aborted.')
        ->assertExitCode(1);

    expect($first->refresh()->phone)->toBe('0123456789');
    expect($second->refresh()->phone)->toBe('+60123456789');
});
