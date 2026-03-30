<?php

use App\Enums\ApplicationStatus;
use App\Models\Brand;
use App\Models\BrandApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows brand registration form', function () {
    $this->get(route('registration.brand.create'))
        ->assertOk()
        ->assertSee('Partner with Mobius');
});

it('returns brands from search endpoint', function () {
    Brand::factory()->create(['name' => 'Starbucks', 'user_id' => null, 'status' => 'approved']);
    Brand::factory()->create(['name' => 'Secret Recipe', 'user_id' => null, 'status' => 'approved']);

    $response = $this->getJson(route('registration.brand.search', ['q' => 'star']));

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['name' => 'Starbucks']);
});

it('excludes claimed brands from search', function () {
    Brand::factory()->create(['name' => 'Claimed Xylophone', 'user_id' => User::factory(), 'status' => 'approved']);
    Brand::factory()->create(['name' => 'Available Xylophone', 'user_id' => null, 'status' => 'approved']);

    $response = $this->getJson(route('registration.brand.search', ['q' => 'Xylophone']));

    $response->assertOk()
        ->assertJsonCount(1)
        ->assertJsonFragment(['name' => 'Available Xylophone']);
});

it('excludes brands with pending applications from search', function () {
    $brand = Brand::factory()->create(['name' => 'Pending Zeppelin', 'user_id' => null, 'status' => 'approved']);
    BrandApplication::create([
        'brand_id' => $brand->id,
        'user_id' => User::factory()->create()->id,
        'status' => 'pending',
        'brand_name' => $brand->name,
        'contact_person' => 'Test',
        'contact_email' => 'test@test.com',
    ]);

    $response = $this->getJson(route('registration.brand.search', ['q' => 'Zeppelin']));

    $response->assertOk()
        ->assertJsonCount(0);
});

it('submits a brand claim for existing brand', function () {
    $brand = Brand::factory()->create(['user_id' => null, 'status' => 'approved']);

    $this->post(route('registration.brand.store'), [
        'flow' => 'claim',
        'brand_id' => $brand->id,
        'contact_person' => 'Alice Wong',
        'email' => 'alice@example.com',
        'phone' => '+60123456789',
        'password' => 'SecureP@ss1',
        'password_confirmation' => 'SecureP@ss1',
    ])->assertRedirect(route('registration.success'));

    $application = BrandApplication::where('brand_id', $brand->id)->first();
    expect($application)->not->toBeNull()
        ->and($application->status)->toBe(ApplicationStatus::Pending)
        ->and($application->brand_name)->toBe($brand->name);

    // Brand should remain unclaimed
    expect($brand->fresh()->user_id)->toBeNull();
});

it('submits a new brand request', function () {
    $this->post(route('registration.brand.store'), [
        'flow' => 'new',
        'brand_name' => 'Awesome Tea Co',
        'description' => 'New bubble tea brand',
        'website_url' => 'https://awesometea.com',
        'contact_person' => 'Bob Lee',
        'email' => 'bob@awesometea.com',
        'phone' => '+60198765432',
        'password' => 'SecureP@ss1',
        'password_confirmation' => 'SecureP@ss1',
    ])->assertRedirect(route('registration.success'));

    $application = BrandApplication::where('brand_name', 'Awesome Tea Co')->first();
    expect($application)->not->toBeNull()
        ->and($application->brand_id)->toBeNull()
        ->and($application->status)->toBe(ApplicationStatus::Pending);
});

it('validates required fields for claim flow', function () {
    $this->post(route('registration.brand.store'), ['flow' => 'claim'])
        ->assertSessionHasErrors(['brand_id', 'contact_person', 'email', 'password']);
});

it('validates required fields for new brand flow', function () {
    $this->post(route('registration.brand.store'), ['flow' => 'new'])
        ->assertSessionHasErrors(['brand_name', 'contact_person', 'email', 'password']);
});

it('rejects duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->post(route('registration.brand.store'), [
        'flow' => 'new',
        'brand_name' => 'Some Brand',
        'contact_person' => 'Jane',
        'email' => 'taken@example.com',
        'password' => 'SecureP@ss1',
        'password_confirmation' => 'SecureP@ss1',
    ])->assertSessionHasErrors(['email']);
});

it('shows success page', function () {
    $this->get(route('registration.success'))
        ->assertOk()
        ->assertSee('Application Received');
});
