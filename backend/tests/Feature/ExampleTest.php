<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('root shows landing page for guests', function () {
    $this->get('/')->assertOk()->assertSee('Mobius');
});

test('root shows landing page for authenticated users', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get('/')->assertOk()->assertSee('Mobius');
});

test('/home redirects to root', function () {
    $this->get('/home')->assertRedirect('/');
});
