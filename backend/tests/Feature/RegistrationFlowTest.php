<?php

use App\Models\Brand;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// --- Brand Registration ---

test('brand registration page loads', function () {
    $this->get(route('registration.brand.create'))
        ->assertOk()
        ->assertSee('Partner with Mobius');
});

test('brand search returns active brands as json', function () {
    $org = Organization::factory()->create();
    Brand::factory()->for($org)->create(['name' => 'Starbucks', 'is_active' => true]);
    Brand::factory()->for($org)->create(['name' => 'Inactive Brand', 'is_active' => false]);

    $this->getJson(route('registration.brand.search', ['q' => 'Star']))
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['name' => 'Starbucks']);
});

test('brand search excludes inactive brands', function () {
    $org = Organization::factory()->create();
    Brand::factory()->for($org)->create(['name' => 'Dead Brand', 'is_active' => false]);

    $this->getJson(route('registration.brand.search', ['q' => 'Dead']))
        ->assertOk()
        ->assertJsonCount(0);
});

test('new brand registration creates user and brand application', function () {
    $this->post(route('registration.brand.store'), [
        'flow' => 'new',
        'brand_name' => 'My New Brand',
        'description' => 'A cool brand',
        'contact_person' => 'Alice Wong',
        'email' => 'alice@example.com',
        'phone' => '012-345 6789',
        'password' => 'SecureP@ss1',
        'password_confirmation' => 'SecureP@ss1',
    ])->assertRedirect(route('registration.success'));

    $this->assertDatabaseHas('users', ['email' => 'alice@example.com']);
    $this->assertDatabaseHas('brand_applications', [
        'brand_name' => 'My New Brand',
        'contact_email' => 'alice@example.com',
        'status' => 'pending',
    ]);
});

test('brand claim registration creates application for existing brand', function () {
    $org = Organization::factory()->create();
    $brand = Brand::factory()->for($org)->create(['name' => 'Tealive', 'is_active' => true]);

    $this->post(route('registration.brand.store'), [
        'flow' => 'claim',
        'brand_id' => $brand->id,
        'contact_person' => 'Bob Lim',
        'email' => 'bob@example.com',
        'phone' => '011-222 3333',
        'password' => 'SecureP@ss1',
        'password_confirmation' => 'SecureP@ss1',
    ])->assertRedirect(route('registration.success'));

    $this->assertDatabaseHas('brand_applications', [
        'brand_id' => $brand->id,
        'contact_email' => 'bob@example.com',
        'status' => 'pending',
    ]);
});

test('brand registration validates required fields for new flow', function () {
    $this->post(route('registration.brand.store'), [
        'flow' => 'new',
    ])->assertSessionHasErrors(['brand_name', 'contact_person', 'email', 'password']);
});

test('brand registration validates required fields for claim flow', function () {
    $this->post(route('registration.brand.store'), [
        'flow' => 'claim',
    ])->assertSessionHasErrors(['brand_id', 'contact_person', 'email', 'password']);
});

// --- Agency Registration ---

test('agency registration page loads', function () {
    $this->get(route('registration.agency.create'))
        ->assertOk()
        ->assertSee('Collector Agency Registration');
});

test('agency registration creates user and agency', function () {
    $this->post(route('registration.agency.store'), [
        'name' => 'CleanCycle Sdn Bhd',
        'contact_person' => 'Charlie Tan',
        'email' => 'charlie@example.com',
        'phone' => '012-111 2222',
        'description' => 'Recycling agency',
        'fleet_size' => 5,
        'coverage_area' => 'Penang Island',
        'password' => 'SecureP@ss1',
        'password_confirmation' => 'SecureP@ss1',
    ])->assertRedirect(route('registration.success'));

    $this->assertDatabaseHas('users', ['email' => 'charlie@example.com']);
    $this->assertDatabaseHas('collector_agencies', [
        'name' => 'CleanCycle Sdn Bhd',
        'email' => 'charlie@example.com',
        'status' => 'pending',
    ]);
});

test('agency registration validates required fields', function () {
    $this->post(route('registration.agency.store'), [])
        ->assertSessionHasErrors(['name', 'contact_person', 'email', 'password']);
});

// --- Success Page ---

test('registration success page loads', function () {
    $this->get(route('registration.success'))
        ->assertOk();
});
