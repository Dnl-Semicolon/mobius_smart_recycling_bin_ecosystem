<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('unauthenticated root redirects to login', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});

test('admin root redirects to admin dashboard', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get('/');

    $response->assertRedirect(route('admin.dashboard'));
});

test('collector root redirects to collector dashboard', function () {
    $collector = User::factory()->collector()->create();

    $response = $this->actingAs($collector)->get('/');

    $response->assertRedirect(route('collector.dashboard'));
});

test('public user root redirects to public dashboard', function () {
    $publicUser = User::factory()->create();

    $response = $this->actingAs($publicUser)->get('/');

    $response->assertRedirect(route('public.dashboard'));
});
