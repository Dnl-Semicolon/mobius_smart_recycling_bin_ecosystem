<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    Sanctum::actingAs(User::factory()->admin()->create());
});

test('validates required person fields', function () {
    $response = $this->postJson('/api/v1/persons', []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors([
        'name',
        'birthday',
        'phone',
        'line_1',
        'city',
        'state',
        'postal_code',
    ]);
});
