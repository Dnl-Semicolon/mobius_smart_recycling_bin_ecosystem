<?php

use App\Enums\UserRole;
use App\Models\Organization;
use App\Models\RegistrationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin user creation normalizes email and phone', function () {
    $admin = User::factory()->create([
        'roles' => [UserRole::Admin->value],
    ]);

    $organization = Organization::create([
        'name' => 'Acme Org',
        'type' => 'beverage_company',
        'is_active' => true,
    ]);

    $response = $this
        ->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'New User',
            'email' => 'New.User@Example.COM ',
            'phone' => '012-345 6789',
            'role' => UserRole::PublicUser->value,
            'organization_id' => $organization->id,
        ]);

    $response->assertRedirect(route('admin.users.index'));

    $user = User::where('email', 'new.user@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->phone)->toBe('+60123456789');
    expect($user->email_verified_at)->not->toBeNull();
    expect($user->getRolesArray())->toBe([UserRole::PublicUser->value]);
});

test('admin user creation rejects duplicate canonical phone numbers', function () {
    $admin = User::factory()->create([
        'roles' => [UserRole::Admin->value],
    ]);

    User::factory()->create([
        'phone' => '+60123456789',
    ]);

    $response = $this
        ->actingAs($admin)
        ->from(route('admin.users.create'))
        ->post(route('admin.users.store'), [
            'name' => 'Duplicate User',
            'email' => 'duplicate@example.com',
            'phone' => '012-345 6789',
            'role' => UserRole::PublicUser->value,
            'organization_id' => '',
        ]);

    $response
        ->assertRedirect(route('admin.users.create'))
        ->assertSessionHasErrors('phone');
});

test('admin user creation rejects contact details that already exist as a lead', function () {
    $admin = User::factory()->create([
        'roles' => [UserRole::Admin->value],
    ]);

    RegistrationRequest::create([
        'company_name' => 'Existing Lead Co',
        'contact_name' => 'Lead Owner',
        'contact_email' => 'lead@example.com',
        'contact_phone' => '+60123456789',
        'type' => 'beverage_company',
        'status' => 'pending',
    ]);

    $response = $this
        ->actingAs($admin)
        ->from(route('admin.users.create'))
        ->post(route('admin.users.store'), [
            'name' => 'Duplicate Lead User',
            'email' => 'Lead@Example.COM ',
            'phone' => '012-345 6789',
            'role' => UserRole::PublicUser->value,
            'organization_id' => '',
        ]);

    $response
        ->assertRedirect(route('admin.users.create'))
        ->assertSessionHasErrors([
            'email' => 'This exists as a lead.',
            'phone' => 'This exists as a lead.',
        ]);
});
