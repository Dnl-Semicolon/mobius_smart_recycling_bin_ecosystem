<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('registers a new user and returns token and user', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'device_name' => 'Test Phone',
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'token',
            'user' => [
                'id',
                'name',
                'email',
                'phone',
                'role',
                'roles',
                'points_balance',
                'current_streak',
                'longest_streak',
                'last_recycled_at',
                'created_at',
            ],
        ]);

    expect($response->json('user.name'))->toBe('Jane Doe')
        ->and($response->json('user.email'))->toBe('jane@example.com')
        ->and($response->json('user.role'))->toBe('public_user')
        ->and($response->json('user.roles'))->toBe(['public_user']);

    $this->assertDatabaseHas('users', [
        'email' => 'jane@example.com',
    ]);
});

it('fails registration with duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Jane Doe',
        'email' => 'taken@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'device_name' => 'Test Phone',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('fails registration with short password', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
        'device_name' => 'Test Phone',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['password']);
});

it('fails registration with mismatched password confirmation', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different123',
        'device_name' => 'Test Phone',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['password']);
});

it('logs in with valid credentials and returns all user fields', function () {
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'password' => 'password123',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'user@example.com',
        'password' => 'password123',
        'device_name' => 'Test Phone',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'token',
            'user' => [
                'id',
                'name',
                'email',
                'phone',
                'role',
                'roles',
                'points_balance',
                'current_streak',
                'longest_streak',
                'last_recycled_at',
                'created_at',
            ],
        ]);

    expect($response->json('user.id'))->toBe($user->id);
});

it('fails login with wrong password', function () {
    User::factory()->create([
        'email' => 'user@example.com',
        'password' => 'password123',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'user@example.com',
        'password' => 'wrongpassword',
        'device_name' => 'Test Phone',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('returns authenticated user with all fields', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/auth/user');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'id',
            'name',
            'email',
            'phone',
            'role',
            'roles',
            'points_balance',
            'current_streak',
            'longest_streak',
            'last_recycled_at',
            'created_at',
        ]);

    expect($response->json('id'))->toBe($user->id);
});

it('logs out and deletes the token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer '.$token,
    ])->postJson('/api/v1/auth/logout');

    $response->assertSuccessful()
        ->assertJson(['message' => 'Logged out successfully.']);

    // Token should be deleted from the database
    $this->assertDatabaseCount('personal_access_tokens', 0);
});
