<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Web Login
|--------------------------------------------------------------------------
*/

test('login page renders', function () {
    $this->get(route('login'))->assertOk()->assertSee('Sign in');
});

test('register page renders', function () {
    $this->get(route('register'))->assertOk()->assertSee('Create account');
});

test('admin can log in and is redirected to admin dashboard', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->post(route('login'), [
        'email' => $admin->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('admin.dashboard'));
    $this->assertAuthenticatedAs($admin);
});

test('collector can log in and is redirected to collector dashboard', function () {
    $collector = User::factory()->collector()->create();

    $response = $this->post(route('login'), [
        'email' => $collector->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('collector.dashboard'));
    $this->assertAuthenticatedAs($collector);
});

test('public user can log in and is redirected to public dashboard', function () {
    $publicUser = User::factory()->create();

    $response = $this->post(route('login'), [
        'email' => $publicUser->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('public.dashboard'));
    $this->assertAuthenticatedAs($publicUser);
});

test('login fails with wrong password', function () {
    $user = User::factory()->create();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('user can log out', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

test('user can register as public user', function () {
    $response = $this->post(route('register'), [
        'name' => 'New User',
        'email' => 'new@example.com',
        'password' => 'NewPass1!',
        'password_confirmation' => 'NewPass1!',
    ]);

    $response->assertRedirect(route('verification.notice'));
    $this->assertAuthenticated();

    $user = User::where('email', 'new@example.com')->first();
    expect($user->getRolesArray())->toBe(['public_user']);
});

/*
|--------------------------------------------------------------------------
| Role-based access control
|--------------------------------------------------------------------------
*/

test('admin can access admin routes', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk();
});

test('collector cannot access admin routes', function () {
    $collector = User::factory()->collector()->create();

    $this->actingAs($collector)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('public user cannot access admin routes', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('guest is redirected to login from admin routes', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});

test('collector can access collector routes', function () {
    $collector = User::factory()->collector()->create();

    $this->actingAs($collector)
        ->get(route('collector.dashboard'))
        ->assertOk();
});

test('admin cannot access collector routes', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('collector.dashboard'))
        ->assertForbidden();
});

test('public user can access public routes', function () {
    $publicUser = User::factory()->create();

    $this->actingAs($publicUser)
        ->get(route('public.dashboard'))
        ->assertOk()
        ->assertSee('Community');
});

test('admin cannot access public routes', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('public.dashboard'))
        ->assertForbidden();
});

test('collector cannot access public routes', function () {
    $collector = User::factory()->collector()->create();

    $this->actingAs($collector)
        ->get(route('public.dashboard'))
        ->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| API Token Authentication (for Flutter)
|--------------------------------------------------------------------------
*/

test('api login returns a bearer token', function () {
    $user = User::factory()->admin()->create();

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'test-device',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role']]);
});

test('api login fails with invalid credentials', function () {
    $user = User::factory()->create();

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'wrong',
        'device_name' => 'test-device',
    ])->assertUnprocessable();
});

test('api user endpoint returns authenticated user', function () {
    $user = User::factory()->admin()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', "Bearer $token")
        ->getJson('/api/v1/auth/user')
        ->assertOk()
        ->assertJsonPath('id', $user->id);
});

test('api logout revokes current token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test')->plainTextToken;

    $this->withHeader('Authorization', "Bearer $token")
        ->postJson('/api/v1/auth/logout')
        ->assertOk();

    // Token should have been deleted from the database
    expect($user->tokens()->count())->toBe(0);
});
