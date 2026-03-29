<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('updates profile fields', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->putJson('/api/v1/profile', [
            'name' => 'Updated Name',
            'username' => 'nick',
            'email' => $user->email,
            'phone' => '012-345 6789',
            'bio' => 'Test bio',
        ]);

    $response->assertSuccessful()
        ->assertJsonPath('name', 'Updated Name')
        ->assertJsonPath('username', 'nick')
        ->assertJsonPath('phone', '012-345 6789')
        ->assertJsonPath('bio', 'Test bio');

    expect($user->fresh()->name)->toBe('Updated Name');
});

it('validates required name on profile update', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/v1/profile', [
            'name' => '',
            'email' => $user->email,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

it('uploads avatar', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->post('/api/v1/profile/avatar', [
            'avatar' => UploadedFile::fake()->image('avatar.jpg', 200, 200),
        ]);

    $response->assertSuccessful();
    expect($response->json('avatar_url'))->not->toBeNull();
    expect($user->fresh()->avatar_path)->not->toBeNull();
    Storage::disk('public')->assertExists($user->fresh()->avatar_path);
});

it('removes avatar', function () {
    Storage::fake('public');
    $user = User::factory()->create(['avatar_path' => 'avatars/old.jpg']);
    Storage::disk('public')->put('avatars/old.jpg', 'fake-image');

    $response = $this->actingAs($user, 'sanctum')
        ->deleteJson('/api/v1/profile/avatar');

    $response->assertSuccessful();
    expect($response->json('avatar_url'))->toBeNull();
    expect($user->fresh()->avatar_path)->toBeNull();
});

it('changes password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->putJson('/api/v1/profile/password', [
            'current_password' => 'old-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

    $response->assertSuccessful()
        ->assertJsonPath('message', 'Password updated successfully.');

    expect(Hash::check('new-password-123', $user->fresh()->password))->toBeTrue();
});

it('rejects wrong current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('correct-password'),
    ]);

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/v1/profile/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('current_password');
});

it('requires authentication for profile endpoints', function () {
    $this->putJson('/api/v1/profile', ['name' => 'Test', 'email' => 'a@b.com'])->assertUnauthorized();
    $this->postJson('/api/v1/profile/avatar')->assertUnauthorized();
    $this->deleteJson('/api/v1/profile/avatar')->assertUnauthorized();
    $this->putJson('/api/v1/profile/password')->assertUnauthorized();
});
