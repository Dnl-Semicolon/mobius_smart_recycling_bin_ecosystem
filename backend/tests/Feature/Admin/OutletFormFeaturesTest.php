<?php

use App\Enums\ContractStatus;
use App\Models\Outlet;
use App\Models\User;
use App\Services\OutletPhotoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

/* ─── Photo Upload ─── */

it('stores an outlet with a photo', function () {
    Storage::fake('public');
    $photo = UploadedFile::fake()->image('storefront.jpg', 800, 600);

    $response = $this->post(route('admin.outlets.store'), [
        'name' => 'Photo Test Outlet',
        'address' => '123 Test Street',
        'contract_status' => ContractStatus::Active->value,
        'photo' => $photo,
    ]);

    $outlet = Outlet::where('name', 'Photo Test Outlet')->first();

    expect($outlet)->not->toBeNull()
        ->and($outlet->photo_path)->not->toBeNull()
        ->and($outlet->photo_path)->toStartWith('outlets/');

    Storage::disk('public')->assertExists($outlet->photo_path);
    $response->assertRedirectToRoute('admin.outlets.show', $outlet);
});

it('replaces photo on update', function () {
    Storage::fake('public');

    $outlet = Outlet::factory()->active()->create(['photo_path' => 'outlets/old.jpg']);
    Storage::disk('public')->put('outlets/old.jpg', 'old-image-data');

    $newPhoto = UploadedFile::fake()->image('new-storefront.jpg', 1200, 800);

    $this->put(route('admin.outlets.update', $outlet), [
        'name' => $outlet->name,
        'address' => $outlet->address,
        'photo' => $newPhoto,
    ]);

    $outlet->refresh();

    expect($outlet->photo_path)->not->toBe('outlets/old.jpg');
    Storage::disk('public')->assertMissing('outlets/old.jpg');
    Storage::disk('public')->assertExists($outlet->photo_path);
});

it('removes photo on update when requested', function () {
    Storage::fake('public');

    $outlet = Outlet::factory()->active()->create(['photo_path' => 'outlets/remove-me.jpg']);
    Storage::disk('public')->put('outlets/remove-me.jpg', 'image-data');

    $this->put(route('admin.outlets.update', $outlet), [
        'name' => $outlet->name,
        'address' => $outlet->address,
        'remove_photo' => '1',
    ]);

    $outlet->refresh();

    expect($outlet->photo_path)->toBeNull();
    Storage::disk('public')->assertMissing('outlets/remove-me.jpg');
});

it('rejects oversized photo', function () {
    $photo = UploadedFile::fake()->image('huge.jpg')->size(3000); // 3MB

    $this->post(route('admin.outlets.store'), [
        'name' => 'Oversized Photo Outlet',
        'address' => '123 Test Street',
        'photo' => $photo,
    ])->assertSessionHasErrors('photo');
});

it('rejects non-image file as photo', function () {
    $file = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

    $this->post(route('admin.outlets.store'), [
        'name' => 'PDF Outlet',
        'address' => '123 Test Street',
        'photo' => $file,
    ])->assertSessionHasErrors('photo');
});

it('deletes photo when outlet is destroyed', function () {
    Storage::fake('public');

    $outlet = Outlet::factory()->active()->create(['photo_path' => 'outlets/doomed.jpg']);
    Storage::disk('public')->put('outlets/doomed.jpg', 'image-data');

    $this->delete(route('admin.outlets.destroy', $outlet));

    Storage::disk('public')->assertMissing('outlets/doomed.jpg');
});

/* ─── Photo URL Accessor ─── */

it('returns null photo_url when no photo', function () {
    $outlet = Outlet::factory()->create(['photo_path' => null]);

    expect($outlet->photo_url)->toBeNull();
});

it('returns storage url for photo_url', function () {
    $outlet = Outlet::factory()->create(['photo_path' => 'outlets/test.jpg']);

    expect($outlet->photo_url)->toContain('outlets/test.jpg');
});

/* ─── Operating Hours (JSON) ─── */

it('stores structured operating hours as JSON', function () {
    $hours = json_encode([
        'monday' => ['open' => true, 'from' => '08:00', 'to' => '22:00'],
        'tuesday' => ['open' => true, 'from' => '08:00', 'to' => '22:00'],
        'wednesday' => ['open' => true, 'from' => '08:00', 'to' => '22:00'],
        'thursday' => ['open' => true, 'from' => '08:00', 'to' => '22:00'],
        'friday' => ['open' => true, 'from' => '08:00', 'to' => '22:00'],
        'saturday' => ['open' => true, 'from' => '09:00', 'to' => '23:00'],
        'sunday' => ['open' => false, 'from' => '', 'to' => ''],
    ]);

    $this->post(route('admin.outlets.store'), [
        'name' => 'Hours Test Outlet',
        'address' => '456 Test Ave',
        'operating_hours' => $hours,
    ]);

    $outlet = Outlet::where('name', 'Hours Test Outlet')->first();

    expect($outlet)->not->toBeNull()
        ->and($outlet->operating_hours)->toBe($hours)
        ->and($outlet->getStructuredHours())->toBeArray()
        ->and($outlet->getStructuredHours()['monday']['open'])->toBeTrue()
        ->and($outlet->getStructuredHours()['sunday']['open'])->toBeFalse();
});

it('returns null for legacy plain text operating hours', function () {
    $outlet = Outlet::factory()->create(['operating_hours' => 'Mon-Fri 9am-5pm']);

    expect($outlet->getStructuredHours())->toBeNull()
        ->and($outlet->operating_hours)->toBe('Mon-Fri 9am-5pm');
});

/* ─── Phone Validation ─── */

it('accepts valid Malaysian mobile numbers', function (string $phone) {
    $this->post(route('admin.outlets.store'), [
        'name' => 'Phone Test',
        'address' => '789 Test Blvd',
        'contact_phone' => $phone,
    ])->assertSessionDoesntHaveErrors('contact_phone');
})->with([
    'mobile formatted' => '012-345 6789',
    'mobile compact' => '0123456789',
    'mobile with dash' => '017-1234567',
    'landline' => '03-1234 5678',
]);

it('rejects invalid phone numbers', function (string $phone) {
    $this->post(route('admin.outlets.store'), [
        'name' => 'Bad Phone Test',
        'address' => '789 Test Blvd',
        'contact_phone' => $phone,
    ])->assertSessionHasErrors('contact_phone');
})->with([
    'too short' => '012-345',
    'no leading zero' => '12-345 6789',
    'letters' => 'not-a-phone',
]);

/* ─── Places Proxy Routes ─── */

it('requires authentication for places autocomplete', function () {
    auth()->logout();

    $this->get(route('admin.places.autocomplete', ['q' => 'starbucks']))
        ->assertForbidden();
});

it('validates query parameter for autocomplete', function () {
    $this->get(route('admin.places.autocomplete'))
        ->assertSessionHasErrors('q');
});

it('requires authentication for places details', function () {
    auth()->logout();

    $this->get(route('admin.places.details', ['place_id' => 'abc']))
        ->assertForbidden();
});

/* ─── OutletPhotoService ─── */

it('processes and resizes large images', function () {
    Storage::fake('public');

    $service = app(OutletPhotoService::class);
    $file = UploadedFile::fake()->image('big.jpg', 2400, 1600);
    $path = $service->process($file);

    expect($path)->toStartWith('outlets/')
        ->and($path)->toEndWith('.jpg');

    Storage::disk('public')->assertExists($path);
});

it('deletes photo from storage', function () {
    Storage::fake('public');
    Storage::disk('public')->put('outlets/delete-me.jpg', 'data');

    $service = app(OutletPhotoService::class);
    $service->delete('outlets/delete-me.jpg');

    Storage::disk('public')->assertMissing('outlets/delete-me.jpg');
});

it('handles null path gracefully on delete', function () {
    $service = app(OutletPhotoService::class);
    $service->delete(null);

    expect(true)->toBeTrue(); // No exception thrown
});
