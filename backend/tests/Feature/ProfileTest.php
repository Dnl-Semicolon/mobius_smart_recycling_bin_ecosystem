<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

// --- Profile View ---

test('admin can see profile page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.profile.edit'))
        ->assertSuccessful()
        ->assertSee('Profile Information')
        ->assertSee('Change Password')
        ->assertSee($admin->name);
});

test('collector can see profile page', function () {
    $collector = User::factory()->collector()->create();

    $this->actingAs($collector)
        ->get(route('collector.profile.edit'))
        ->assertSuccessful()
        ->assertSee('Profile Information')
        ->assertSee($collector->name);
});

test('guest is redirected to login', function () {
    $this->get('/admin/profile')->assertRedirect(route('login'));
    $this->get('/collector/profile')->assertRedirect(route('login'));
});

// --- Profile Update ---

test('admin can update name', function () {
    $admin = User::factory()->admin()->create(['name' => 'Old Name']);

    $this->actingAs($admin)
        ->put(route('admin.profile.update'), [
            'name' => 'New Name',
            'email' => $admin->email,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($admin->fresh()->name)->toBe('New Name');
});

test('collector can update email', function () {
    $collector = User::factory()->collector()->create(['email' => 'old@test.com']);

    $this->actingAs($collector)
        ->put(route('collector.profile.update'), [
            'name' => $collector->name,
            'email' => 'new@test.com',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($collector->fresh()->email)->toBe('new@test.com');
});

test('cannot set email to another users email', function () {
    $admin = User::factory()->admin()->create();
    $other = User::factory()->create(['email' => 'taken@test.com']);

    $this->actingAs($admin)
        ->put(route('admin.profile.update'), [
            'name' => $admin->name,
            'email' => 'taken@test.com',
        ])
        ->assertSessionHasErrors('email');
});

test('name is required', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->put(route('admin.profile.update'), [
            'name' => '',
            'email' => $admin->email,
        ])
        ->assertSessionHasErrors('name');
});

test('email must be valid format', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->put(route('admin.profile.update'), [
            'name' => $admin->name,
            'email' => 'not-an-email',
        ])
        ->assertSessionHasErrors('email');
});

// --- Password Change ---

test('can change password with correct current password', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->put(route('admin.profile.password'), [
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(Hash::check('newpassword123', $admin->fresh()->password))->toBeTrue();
});

test('cannot change password with wrong current password', function () {
    $collector = User::factory()->collector()->create();

    $this->actingAs($collector)
        ->put(route('collector.profile.password'), [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])
        ->assertSessionHasErrors('current_password');
});

test('new password must be at least 8 characters', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->put(route('admin.profile.password'), [
            'current_password' => 'password',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])
        ->assertSessionHasErrors('password');
});

test('new password must be confirmed', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->put(route('admin.profile.password'), [
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'mismatch123',
        ])
        ->assertSessionHasErrors('password');
});
