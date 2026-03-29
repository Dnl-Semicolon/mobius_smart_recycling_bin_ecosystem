<?php

use App\Models\Bin;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a report for authenticated user', function () {
    $user = User::factory()->create();
    $bin = Bin::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/reports', [
            'bin_id' => $bin->id,
            'type' => 'damaged_bin',
            'description' => 'The bin lid is broken and does not close properly.',
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.bin_id', $bin->id)
        ->assertJsonPath('data.type', 'damaged_bin')
        ->assertJsonPath('data.status', 'open')
        ->assertJsonPath('message', 'Report submitted successfully.');

    $this->assertDatabaseHas('reports', [
        'user_id' => $user->id,
        'bin_id' => $bin->id,
        'type' => 'damaged_bin',
        'description' => 'The bin lid is broken and does not close properly.',
    ]);
});

it('rejects report with missing fields', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/reports', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['bin_id', 'type', 'description']);
});

it('rejects report with invalid type', function () {
    $user = User::factory()->create();
    $bin = Bin::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/reports', [
            'bin_id' => $bin->id,
            'type' => 'nonexistent_type',
            'description' => 'Some description.',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['type']);
});

it('rejects report with description over 1000 chars', function () {
    $user = User::factory()->create();
    $bin = Bin::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/reports', [
            'bin_id' => $bin->id,
            'type' => 'damaged_bin',
            'description' => str_repeat('x', 1001),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['description']);
});

it('rejects report with non-existent bin', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/reports', [
            'bin_id' => 99999,
            'type' => 'damaged_bin',
            'description' => 'Some description.',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['bin_id']);
});

it('returns paginated reports for the user', function () {
    $user = User::factory()->create();
    Report::factory()->count(3)->create(['user_id' => $user->id]);
    Report::factory()->count(2)->create(); // other users

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/reports');

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('does not return other users reports', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    Report::factory()->count(2)->create(['user_id' => $other->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/reports');

    $response->assertSuccessful()
        ->assertJsonCount(0, 'data');
});

it('requires authentication for report endpoints', function () {
    $this->getJson('/api/v1/reports')->assertUnauthorized();
    $this->postJson('/api/v1/reports', [])->assertUnauthorized();
});
