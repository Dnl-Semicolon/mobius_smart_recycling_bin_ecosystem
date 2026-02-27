<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('api explorer page loads in local environment', function () {
    $this->get('/dev/api-explorer')
        ->assertSuccessful()
        ->assertSee('Mobius API Explorer')
        ->assertSee('Auth')
        ->assertSee('/api/v1/auth/login');
});

test('api explorer shows all endpoint groups', function () {
    $this->get('/dev/api-explorer')
        ->assertSuccessful()
        ->assertSee('Admin — Pickup Management')
        ->assertSee('Admin — Users')
        ->assertSee('Collector')
        ->assertSee('Public');
});
