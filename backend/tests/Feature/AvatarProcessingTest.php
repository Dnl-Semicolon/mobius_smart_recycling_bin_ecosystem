<?php

use App\Models\User;
use App\Services\AvatarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

it('crops a landscape image to a 512x512 square JPEG', function () {
    $service = app(AvatarService::class);

    // Create a 800x400 landscape image
    $file = UploadedFile::fake()->image('landscape.jpg', 800, 400);

    $path = $service->process($file);

    expect($path)->toStartWith('avatars/');
    expect($path)->toEndWith('.jpg');

    Storage::disk('public')->assertExists($path);

    $contents = Storage::disk('public')->get($path);
    $image = imagecreatefromstring($contents);

    expect(imagesx($image))->toBe(512);
    expect(imagesy($image))->toBe(512);

    imagedestroy($image);
});

it('crops a portrait image to a 512x512 square JPEG', function () {
    $service = app(AvatarService::class);

    $file = UploadedFile::fake()->image('portrait.png', 400, 800);

    $path = $service->process($file);

    Storage::disk('public')->assertExists($path);

    $contents = Storage::disk('public')->get($path);
    $image = imagecreatefromstring($contents);

    expect(imagesx($image))->toBe(512);
    expect(imagesy($image))->toBe(512);

    imagedestroy($image);
});

it('handles an already square image', function () {
    $service = app(AvatarService::class);

    $file = UploadedFile::fake()->image('square.jpg', 1024, 1024);

    $path = $service->process($file);

    $contents = Storage::disk('public')->get($path);
    $image = imagecreatefromstring($contents);

    expect(imagesx($image))->toBe(512);
    expect(imagesy($image))->toBe(512);

    imagedestroy($image);
});

it('deletes the old avatar when uploading a new one via API', function () {
    $user = User::factory()->create(['avatar_path' => 'avatars/old.jpg']);
    Storage::disk('public')->put('avatars/old.jpg', 'old');

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/profile/avatar', [
            'avatar' => UploadedFile::fake()->image('new.jpg', 600, 400),
        ])
        ->assertSuccessful();

    Storage::disk('public')->assertMissing('avatars/old.jpg');

    $user->refresh();
    expect($user->avatar_path)->not->toBe('avatars/old.jpg');
    Storage::disk('public')->assertExists($user->avatar_path);
});

it('processes avatar on web profile update', function () {
    $user = User::factory()->create(['roles' => ['admin']]);

    $response = $this->actingAs($user)
        ->from(route('admin.profile.edit'))
        ->put(route('admin.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->image('web.jpg', 500, 300),
        ]);

    $response->assertRedirect();

    $user->refresh();
    expect((string) $user->avatar_path)->toStartWith('avatars/');

    $contents = Storage::disk('public')->get($user->avatar_path);
    $image = imagecreatefromstring($contents);

    expect(imagesx($image))->toBe(512);
    expect(imagesy($image))->toBe(512);

    imagedestroy($image);
});
