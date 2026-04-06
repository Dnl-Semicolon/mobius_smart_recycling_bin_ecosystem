<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'Test@Example.COM ',
        'phone' => '012-345 6789',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    $user = User::first();

    expect($user)->not->toBeNull();
    expect($user->email)->toBe('test@example.com');
    expect($user->phone)->toBe('+60123456789');
    expect($user->getRolesArray())->toBe([UserRole::PublicUser->value]);
});

test('registration rejects duplicate canonical phone numbers', function () {
    User::factory()->create([
        'phone' => '+60123456789',
    ]);

    $response = $this
        ->from(route('register'))
        ->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'other@example.com',
            'phone' => '012-345 6789',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

    $response
        ->assertRedirect(route('register'))
        ->assertSessionHasErrors('phone');
});
