<?php

use App\Models\Plan;
use App\Models\RegistrationRequest;
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
