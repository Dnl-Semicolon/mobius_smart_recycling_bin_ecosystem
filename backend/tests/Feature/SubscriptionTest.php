<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows pricing plans page without auth', function () {
    $this->get(route('subscribe.plans'))
        ->assertOk()
        ->assertSee('Plans & Pricing')
        ->assertSee('Basic')
        ->assertSee('Premium')
        ->assertSee('RM49')
        ->assertSee('RM149');
});

it('requires auth for checkout', function () {
    $this->post(route('subscribe.checkout'), ['price' => 'basic'])
        ->assertRedirect(route('login'));
});

it('requires auth for manage', function () {
    $this->get(route('subscribe.manage'))
        ->assertRedirect(route('login'));
});

it('validates price parameter on checkout', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('subscribe.checkout'), ['price' => 'invalid'])
        ->assertSessionHasErrors(['price']);
});

it('shows manage page for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('subscribe.manage'))
        ->assertOk()
        ->assertSee('No active subscription');
});

it('shows success page for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('subscribe.success'))
        ->assertOk()
        ->assertSee('Subscription Active');
});

it('shows cancel page for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('subscribe.cancel'))
        ->assertOk()
        ->assertSee('Payment Cancelled');
});
