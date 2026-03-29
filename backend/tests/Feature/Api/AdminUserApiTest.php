<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('admin can list users', function () {
    User::factory()->count(3)->create();
    $admin = User::factory()->admin()->create();

    Sanctum::actingAs($admin);

    $response = $this->getJson(route('api.admin.users.index'));

    $response->assertOk()
        ->assertJsonPath('message', 'Users retrieved successfully.');

    // 3 users + 1 admin = 4
    expect(count($response->json('data')))->toBe(4);
});

test('admin can filter users by role', function () {
    User::factory()->collector()->count(2)->create();
    User::factory()->publicUser()->create();
    $admin = User::factory()->admin()->create();

    Sanctum::actingAs($admin);

    $response = $this->getJson(route('api.admin.users.index', ['role' => 'collector']));

    $response->assertOk();
    expect(count($response->json('data')))->toBe(2);
});

test('admin can view a single user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->collector()->create(['name' => 'Test Collector']);

    Sanctum::actingAs($admin);

    $this->getJson(route('api.admin.users.show', $user))
        ->assertOk()
        ->assertJsonPath('data.name', 'Test Collector')
        ->assertJsonPath('data.role', 'collector')
        ->assertJsonPath('message', 'User retrieved successfully.');
});

test('collector cannot access admin user API', function () {
    Sanctum::actingAs(User::factory()->collector()->create());

    $this->getJson(route('api.admin.users.index'))
        ->assertForbidden();
});

test('public user cannot access admin user API', function () {
    Sanctum::actingAs(User::factory()->publicUser()->create());

    $this->getJson(route('api.admin.users.index'))
        ->assertForbidden();
});
