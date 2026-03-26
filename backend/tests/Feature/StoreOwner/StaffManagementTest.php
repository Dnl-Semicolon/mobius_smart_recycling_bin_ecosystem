<?php

use App\Enums\UserRole;
use App\Models\Brand;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('HQ user can view staff list', function () {
    $user = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $user->id]);
    $outlet = Outlet::factory()->create(['brand_id' => $brand->id]);

    $manager = User::factory()->storeOwner()->create();
    $outlet->managers()->attach($manager->id, ['role' => 'manager']);

    $response = $this->actingAs($user)->get(route('store.staff.index'));

    $response->assertOk()
        ->assertSee($manager->name)
        ->assertSee($outlet->name);
});

it('branch manager cannot access staff page', function () {
    $hqUser = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $hqUser->id]);
    $outlet = Outlet::factory()->create(['brand_id' => $brand->id]);

    $branchUser = User::factory()->storeOwner()->create();
    $outlet->managers()->attach($branchUser->id, ['role' => 'manager']);

    $response = $this->actingAs($branchUser)->get(route('store.staff.index'));

    $response->assertForbidden();
});

it('HQ can invite existing user as branch manager', function () {
    $user = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $user->id]);
    $outlet = Outlet::factory()->create(['brand_id' => $brand->id]);

    $existingUser = User::factory()->create(['roles' => ['public_user']]);

    $response = $this->actingAs($user)->post(route('store.staff.invite'), [
        'email' => $existingUser->email,
        'outlet_id' => $outlet->id,
    ]);

    $response->assertRedirect(route('store.staff.index'));
    expect($outlet->managers()->where('user_id', $existingUser->id)->exists())->toBeTrue();
    $existingUser->refresh();
    expect($existingUser->hasRole(UserRole::StoreOwner))->toBeTrue();
});

it('HQ can invite new user as branch manager', function () {
    $user = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $user->id]);
    $outlet = Outlet::factory()->create(['brand_id' => $brand->id]);

    $response = $this->actingAs($user)->post(route('store.staff.invite'), [
        'email' => 'newmanager@example.com',
        'outlet_id' => $outlet->id,
    ]);

    $response->assertRedirect(route('store.staff.index'));
    $newUser = User::where('email', 'newmanager@example.com')->first();
    expect($newUser)->not->toBeNull();
    expect($newUser->hasRole(UserRole::StoreOwner))->toBeTrue();
    expect($outlet->managers()->where('user_id', $newUser->id)->exists())->toBeTrue();
});

it('HQ can remove branch manager', function () {
    $user = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $user->id]);
    $outlet = Outlet::factory()->create(['brand_id' => $brand->id]);

    $manager = User::factory()->storeOwner()->create();
    $outlet->managers()->attach($manager->id, ['role' => 'manager']);

    $response = $this->actingAs($user)->delete(route('store.staff.remove', $manager));

    $response->assertRedirect(route('store.staff.index'));
    expect($outlet->managers()->where('user_id', $manager->id)->exists())->toBeFalse();
    $manager->refresh();
    expect($manager->hasRole(UserRole::StoreOwner))->toBeFalse();
});

it('cannot invite to outlet outside brand scope', function () {
    $user = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $user->id]);
    Outlet::factory()->create(['brand_id' => $brand->id]);

    $otherBrand = Brand::factory()->approved()->create();
    $otherOutlet = Outlet::factory()->create(['brand_id' => $otherBrand->id]);

    $response = $this->actingAs($user)->post(route('store.staff.invite'), [
        'email' => 'test@example.com',
        'outlet_id' => $otherOutlet->id,
    ]);

    $response->assertStatus(302);
    expect(User::where('email', 'test@example.com')->exists())->toBeFalse();
});
