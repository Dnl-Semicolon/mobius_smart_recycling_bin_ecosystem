<?php

use App\Models\Plan;
use App\Models\RegistrationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('get started submissions store normalized contact details', function () {
    $plan = Plan::create([
        'name' => 'Starter',
        'price_monthly' => 0,
        'price_yearly' => 0,
        'staff_limit' => 1,
        'bin_limit' => 1,
        'outlet_limit' => 1,
        'api_access' => false,
        'is_active' => true,
    ]);

    $response = $this->post(route('get-started.store'), [
        'contact_name' => 'Jane Doe',
        'contact_email' => 'Sales@Example.COM ',
        'contact_phone' => '012-345 6789',
        'company_name' => 'Example Co',
        'type' => 'beverage_company',
        'selected_plan_id' => $plan->id,
    ]);

    $response->assertRedirect(route('get-started.confirmation'));

    $lead = RegistrationRequest::first();

    expect($lead)->not->toBeNull();
    expect($lead->contact_email)->toBe('sales@example.com');
    expect($lead->contact_phone)->toBe('+60123456789');
    expect($lead->status)->toBe('pending');
});

test('get started rejects contact details already used by users', function () {
    $plan = Plan::create([
        'name' => 'Starter',
        'price_monthly' => 0,
        'price_yearly' => 0,
        'staff_limit' => 1,
        'bin_limit' => 1,
        'outlet_limit' => 1,
        'api_access' => false,
        'is_active' => true,
    ]);

    User::factory()->create([
        'email' => 'sales@example.com',
        'phone' => '+60123456789',
    ]);

    $response = $this
        ->from(route('get-started'))
        ->post(route('get-started.store'), [
            'contact_name' => 'Jane Doe',
            'contact_email' => 'Sales@Example.COM ',
            'contact_phone' => '012-345 6789',
            'company_name' => 'Example Co',
            'type' => 'beverage_company',
            'selected_plan_id' => $plan->id,
        ]);

    $response
        ->assertRedirect(route('get-started'))
        ->assertSessionHasErrors(['contact_email', 'contact_phone']);
});

test('get started rejects contact details already used by pending leads', function () {
    $plan = Plan::create([
        'name' => 'Starter',
        'price_monthly' => 0,
        'price_yearly' => 0,
        'staff_limit' => 1,
        'bin_limit' => 1,
        'outlet_limit' => 1,
        'api_access' => false,
        'is_active' => true,
    ]);

    RegistrationRequest::create([
        'company_name' => 'Existing Co',
        'contact_name' => 'Jane Doe',
        'contact_email' => 'sales@example.com',
        'contact_phone' => '+60123456789',
        'type' => 'beverage_company',
        'status' => 'pending',
    ]);

    $response = $this
        ->from(route('get-started'))
        ->post(route('get-started.store'), [
            'contact_name' => 'Jane Doe',
            'contact_email' => 'Sales@Example.COM ',
            'contact_phone' => '012-345 6789',
            'company_name' => 'Example Co',
            'type' => 'beverage_company',
            'selected_plan_id' => $plan->id,
        ]);

    $response
        ->assertRedirect(route('get-started'))
        ->assertSessionHasErrors(['contact_email', 'contact_phone']);
});
