<?php

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('shows notifications index page', function () {
    AppNotification::factory()->count(5)->create();

    $this->get(route('admin.notifications.index'))
        ->assertSuccessful()
        ->assertSeeText('Notifications');
});

it('shows empty state when no notifications', function () {
    $this->get(route('admin.notifications.index'))
        ->assertSuccessful()
        ->assertSeeText('No notifications yet');
});

it('filters notifications by type', function () {
    AppNotification::factory()->welcome()->create();
    AppNotification::factory()->pointsEarned(10)->create();

    $this->get(route('admin.notifications.index', ['type' => 'welcome']))
        ->assertSuccessful();
});

it('denies non-admin access to notifications', function () {
    $user = User::factory()->create(); // public_user

    $this->actingAs($user)
        ->get(route('admin.notifications.index'))
        ->assertForbidden();
});
