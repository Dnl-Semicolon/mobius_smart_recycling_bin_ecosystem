<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('send otp stores code in session and flashes it', function () {
    $user = User::factory()->admin()->create(['phone' => null]);

    $this->actingAs($user)
        ->post(route('admin.profile.send-otp'), ['phone' => '012-345 6789'])
        ->assertRedirect()
        ->assertSessionHas('otp_code')
        ->assertSessionHas('success');

    expect($user->fresh()->phone)->toBe('012-345 6789');
});

test('send otp requires phone field', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post(route('admin.profile.send-otp'), ['phone' => ''])
        ->assertSessionHasErrors('phone');
});

test('verify otp sets phone_verified_at on user', function () {
    $user = User::factory()->admin()->create(['phone' => '012-345 6789', 'phone_verified_at' => null]);

    $this->actingAs($user)
        ->withSession([
            'otp_code' => '123456',
            'otp_phone' => '012-345 6789',
            'otp_expires_at' => now()->addMinutes(5)->timestamp,
        ])
        ->post(route('admin.profile.verify-otp'), ['otp' => '123456'])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($user->fresh()->phone_verified_at)->not->toBeNull();
});

test('verify otp rejects wrong code', function () {
    $user = User::factory()->admin()->create(['phone_verified_at' => null]);

    $this->actingAs($user)
        ->withSession([
            'otp_code' => '123456',
            'otp_phone' => '012-345 6789',
            'otp_expires_at' => now()->addMinutes(5)->timestamp,
        ])
        ->post(route('admin.profile.verify-otp'), ['otp' => '000000'])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($user->fresh()->phone_verified_at)->toBeNull();
});

test('verify otp rejects expired code', function () {
    $user = User::factory()->admin()->create(['phone_verified_at' => null]);

    $this->actingAs($user)
        ->withSession([
            'otp_code' => '123456',
            'otp_phone' => '012-345 6789',
            'otp_expires_at' => now()->subMinutes(1)->timestamp,
        ])
        ->post(route('admin.profile.verify-otp'), ['otp' => '123456'])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($user->fresh()->phone_verified_at)->toBeNull();
});

test('verify otp rejects when no otp was sent', function () {
    $user = User::factory()->admin()->create(['phone_verified_at' => null]);

    $this->actingAs($user)
        ->post(route('admin.profile.verify-otp'), ['otp' => '123456'])
        ->assertRedirect()
        ->assertSessionHas('error');
});

test('otp code is exactly 6 digits', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post(route('admin.profile.send-otp'), ['phone' => '012-345 6789']);

    $code = session('otp_code');
    expect($code)->toHaveLength(6)
        ->and(ctype_digit($code))->toBeTrue();
});

test('collector can use otp flow', function () {
    $user = User::factory()->collector()->create(['phone_verified_at' => null]);

    $this->actingAs($user)
        ->post(route('collector.profile.send-otp'), ['phone' => '011-222 3333'])
        ->assertRedirect()
        ->assertSessionHas('otp_code');
});

test('store owner can use otp flow', function () {
    $user = User::factory()->storeOwner()->create(['phone_verified_at' => null]);

    $this->actingAs($user)
        ->post(route('store.profile.send-otp'), ['phone' => '011-222 3333'])
        ->assertRedirect()
        ->assertSessionHas('otp_code');
});
