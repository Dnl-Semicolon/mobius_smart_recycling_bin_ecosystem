<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows home page without auth', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Sign In')
        ->assertSee('Register Your Brand')
        ->assertSee('Register Your Agency')
        ->assertSee('Pricing')
        ->assertSee('Get Started');
});

it('shows dashboard link when authenticated', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee('Manage Subscription');
});

it('does not contain placeholder text', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Mobius')
        ->assertDontSee('Coming Soon')
        ->assertDontSee('Under Construction');
});

it('renders all stakeholder sections', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('For Recyclers')
        ->assertSee('For Brands')
        ->assertSee('For Collection Agencies')
        ->assertSee('How It Works');
});
