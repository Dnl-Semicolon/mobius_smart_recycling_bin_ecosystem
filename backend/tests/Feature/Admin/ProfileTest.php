<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('shows the profile settings page', function () {
    $this->get(route('admin.profile.edit'))
        ->assertSuccessful()
        ->assertSeeText('Public profile')
        ->assertSeeText('Account')
        ->assertSeeText('Change password');
});

it('updates profile information', function () {
    $this->put(route('admin.profile.update'), [
        'name' => 'Updated Admin',
        'username' => 'the_admin',
        'email' => $this->admin->email,
        'phone' => '012-345 6789',
        'bio' => 'Managing the recycling ecosystem.',
    ])->assertRedirect();

    $this->admin->refresh();

    expect($this->admin->name)->toBe('Updated Admin')
        ->and($this->admin->username)->toBe('the_admin')
        ->and($this->admin->phone)->toBe('012-345 6789')
        ->and($this->admin->bio)->toBe('Managing the recycling ecosystem.');
});

it('updates profile with avatar', function () {
    Storage::fake('public');

    $this->put(route('admin.profile.update'), [
        'name' => $this->admin->name,
        'email' => $this->admin->email,
        'avatar' => UploadedFile::fake()->image('avatar.jpg', 200, 200),
    ])->assertRedirect();

    $this->admin->refresh();

    expect($this->admin->avatar_path)->not->toBeNull();
    Storage::disk('public')->assertExists($this->admin->avatar_path);
});

it('replaces old avatar when uploading new one', function () {
    Storage::fake('public');

    // Upload first avatar
    $this->put(route('admin.profile.update'), [
        'name' => $this->admin->name,
        'email' => $this->admin->email,
        'avatar' => UploadedFile::fake()->image('first.jpg'),
    ]);

    $firstPath = $this->admin->fresh()->avatar_path;

    // Upload second avatar
    $this->put(route('admin.profile.update'), [
        'name' => $this->admin->name,
        'email' => $this->admin->email,
        'avatar' => UploadedFile::fake()->image('second.jpg'),
    ]);

    $this->admin->refresh();

    expect($this->admin->avatar_path)->not->toBe($firstPath);
    Storage::disk('public')->assertMissing($firstPath);
    Storage::disk('public')->assertExists($this->admin->avatar_path);
});

it('removes avatar', function () {
    Storage::fake('public');

    // Upload avatar first
    $this->admin->update([
        'avatar_path' => UploadedFile::fake()->image('avatar.jpg')->store('avatars', 'public'),
    ]);

    $path = $this->admin->avatar_path;
    Storage::disk('public')->assertExists($path);

    $this->delete(route('admin.profile.avatar.remove'))
        ->assertRedirect();

    $this->admin->refresh();

    expect($this->admin->avatar_path)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

it('rejects avatar larger than 2MB', function () {
    Storage::fake('public');

    $this->put(route('admin.profile.update'), [
        'name' => $this->admin->name,
        'email' => $this->admin->email,
        'avatar' => UploadedFile::fake()->image('huge.jpg')->size(3000),
    ])->assertSessionHasErrors('avatar');
});

it('rejects non-image avatar', function () {
    Storage::fake('public');

    $this->put(route('admin.profile.update'), [
        'name' => $this->admin->name,
        'email' => $this->admin->email,
        'avatar' => UploadedFile::fake()->create('doc.pdf', 100),
    ])->assertSessionHasErrors('avatar');
});

it('validates bio max length', function () {
    $this->put(route('admin.profile.update'), [
        'name' => $this->admin->name,
        'email' => $this->admin->email,
        'bio' => str_repeat('a', 501),
    ])->assertSessionHasErrors('bio');
});

it('prevents duplicate email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->put(route('admin.profile.update'), [
        'name' => $this->admin->name,
        'email' => 'taken@example.com',
    ])->assertSessionHasErrors('email');
});
