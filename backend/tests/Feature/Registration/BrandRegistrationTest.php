<?php

use App\Enums\ApplicationStatus;
use App\Models\Brand;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows brand registration form', function () {
    $this->get(route('registration.brand.create'))
        ->assertOk()
        ->assertSee('Brand Registration')
        ->assertSee('Company Information')
        ->assertSee('Submit Application');
});

it('registers a brand application', function () {
    $this->post(route('registration.brand.store'), [
        'name' => 'Test Brand Co',
        'contact_person' => 'John Doe',
        'email' => 'john@testbrand.com',
        'phone' => '+60123456789',
        'description' => 'A test brand',
        'website_url' => 'https://testbrand.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect(route('registration.success'));

    $brand = Brand::where('name', 'Test Brand Co')->first();
    expect($brand)->not->toBeNull()
        ->and($brand->status)->toBe(ApplicationStatus::Pending)
        ->and($brand->active)->toBeFalse()
        ->and($brand->contact_email)->toBe('john@testbrand.com');

    $user = User::where('email', 'john@testbrand.com')->first();
    expect($user)->not->toBeNull()
        ->and($brand->user_id)->toBe($user->id);
});

it('validates required fields', function () {
    $this->post(route('registration.brand.store'), [])
        ->assertSessionHasErrors(['name', 'contact_person', 'email', 'password']);
});

it('rejects duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->post(route('registration.brand.store'), [
        'name' => 'Duplicate Brand',
        'contact_person' => 'Jane',
        'email' => 'taken@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasErrors(['email']);
});

it('shows success page', function () {
    $this->get(route('registration.success'))
        ->assertOk()
        ->assertSee('Application Received');
});
