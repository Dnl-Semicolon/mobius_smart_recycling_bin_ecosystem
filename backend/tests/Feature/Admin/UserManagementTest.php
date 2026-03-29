<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can view users list', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->collector()->create(['name' => 'Test Collector']);

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertSuccessful()
        ->assertSee('Test Collector');
});

test('admin can view create user form', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.users.create'))
        ->assertSuccessful()
        ->assertSee('Create User');
});

test('admin can create a new collector', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'New Collector',
            'email' => 'newcollector@mobius.test',
            'password' => 'password123',
            'roles' => ['collector'],
        ])
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('success');

    $user = User::where('email', 'newcollector@mobius.test')->first();
    expect($user)->not->toBeNull()
        ->and($user->getRolesArray())->toBe(['collector']);
});

test('admin can update a users role', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->publicUser()->create();

    $this->actingAs($admin)
        ->put(route('admin.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'roles' => ['collector'],
        ])
        ->assertRedirect(route('admin.users.edit', $user))
        ->assertSessionHas('success');

    expect($user->fresh()->getRolesArray())->toBe(['collector']);
});

test('admin cannot delete themselves', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $admin))
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('error');

    expect(User::find($admin->id))->not->toBeNull();
});

test('admin can delete another user', function () {
    $admin = User::factory()->admin()->create();
    $other = User::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $other))
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('success');

    expect(User::find($other->id))->toBeNull();
});

test('validates email uniqueness on create', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create(['email' => 'taken@test.com']);

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'Dup',
            'email' => 'taken@test.com',
            'password' => 'password123',
            'role' => 'collector',
        ])
        ->assertSessionHasErrors('email');
});

test('validates password minimum length on create', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'Short Pass',
            'email' => 'short@test.com',
            'password' => 'abc',
            'role' => 'collector',
        ])
        ->assertSessionHasErrors('password');
});

test('collector cannot access user management', function () {
    $collector = User::factory()->collector()->create();

    $this->actingAs($collector)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

test('admin can filter users by role', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->collector()->create(['name' => 'Visible Collector']);
    User::factory()->create(['name' => 'Hidden User']);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['role' => 'collector']))
        ->assertSuccessful()
        ->assertSee('Visible Collector')
        ->assertDontSee('Hidden User');
});
