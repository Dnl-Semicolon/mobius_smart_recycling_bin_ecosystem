<?php

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns paginated notifications for the user', function () {
    $user = User::factory()->create();
    AppNotification::factory()->count(3)->for($user)->create();
    AppNotification::factory()->count(2)->for(User::factory())->create(); // other user

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/notifications');

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure(['data' => [['id', 'type', 'title', 'body', 'is_read', 'created_at']]]);
});

it('returns newest notifications first', function () {
    $user = User::factory()->create();
    $old = AppNotification::factory()->for($user)->create(['created_at' => now()->subDay()]);
    $new = AppNotification::factory()->for($user)->create(['created_at' => now()]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/notifications');

    expect($response->json('data.0.id'))->toBe($new->id);
    expect($response->json('data.1.id'))->toBe($old->id);
});

it('returns correct unread count', function () {
    $user = User::factory()->create();
    AppNotification::factory()->count(3)->for($user)->create();
    AppNotification::factory()->count(2)->for($user)->read()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/notifications/unread-count');

    $response->assertSuccessful()
        ->assertJsonPath('unread_count', 3);
});

it('marks a notification as read', function () {
    $user = User::factory()->create();
    $notification = AppNotification::factory()->for($user)->create();

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/notifications/{$notification->id}/read")
        ->assertSuccessful();

    expect($notification->fresh()->read_at)->not->toBeNull();
});

it('prevents marking another users notification as read', function () {
    $user = User::factory()->create();
    $other = AppNotification::factory()->for(User::factory())->create();

    $this->actingAs($user, 'sanctum')
        ->postJson("/api/v1/notifications/{$other->id}/read")
        ->assertForbidden();
});

it('marks all notifications as read', function () {
    $user = User::factory()->create();
    AppNotification::factory()->count(5)->for($user)->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/notifications/read-all')
        ->assertSuccessful();

    expect($user->appNotifications()->whereNull('read_at')->count())->toBe(0);
});

it('requires authentication', function () {
    $this->getJson('/api/v1/notifications')->assertUnauthorized();
    $this->getJson('/api/v1/notifications/unread-count')->assertUnauthorized();
    $this->postJson('/api/v1/notifications/1/read')->assertUnauthorized();
    $this->postJson('/api/v1/notifications/read-all')->assertUnauthorized();
});

it('creates welcome notification on registration', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'device_name' => 'Test Phone',
    ]);

    $response->assertCreated();

    $user = User::where('email', 'test@example.com')->first();
    expect($user->appNotifications()->where('type', 'welcome')->count())->toBe(1);
});
