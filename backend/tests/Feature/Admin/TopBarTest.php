<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('renders quick-create dropdown in the top bar', function () {
    $this->get(route('admin.dashboard'))
        ->assertSuccessful()
        ->assertSee('data-testid="quick-create-trigger"', false)
        ->assertSee('New Outlet')
        ->assertSee('New Bin')
        ->assertSee('New User');
});

it('renders notifications bell in the top bar', function () {
    $this->get(route('admin.dashboard'))
        ->assertSuccessful()
        ->assertSee('data-testid="notifications-bell"', false);
});

it('renders avatar menu with user name and email', function () {
    $this->get(route('admin.dashboard'))
        ->assertSuccessful()
        ->assertSee('data-testid="avatar-menu-trigger"', false)
        ->assertSee($this->admin->name)
        ->assertSee($this->admin->email)
        ->assertSee('Profile')
        ->assertSee('Sign out');
});

it('renders initials avatar when user has no avatar_path', function () {
    $this->admin->update(['avatar_path' => null]);

    $response = $this->get(route('admin.dashboard'));
    $response->assertSuccessful();

    $initial = strtoupper(substr($this->admin->name, 0, 1));
    $response->assertSee($initial);
});
