<?php

use App\Models\Bin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

test('bin serial number is auto-generated on creation', function () {
    $bin = Bin::factory()->create();

    $year = date('Y');
    expect($bin->serial_number)->toMatch("/^MBR-{$year}-\\d{3}$/");
});

test('serial numbers are sequential', function () {
    $bin1 = Bin::factory()->create();
    $bin2 = Bin::factory()->create();
    $bin3 = Bin::factory()->create();

    $year = date('Y');
    expect($bin1->serial_number)->toBe("MBR-{$year}-001")
        ->and($bin2->serial_number)->toBe("MBR-{$year}-002")
        ->and($bin3->serial_number)->toBe("MBR-{$year}-003");
});

test('serial number skips soft-deleted bins', function () {
    $bin1 = Bin::factory()->create();
    $bin2 = Bin::factory()->create();
    $bin2->delete(); // soft delete

    $bin3 = Bin::factory()->create();

    $year = date('Y');
    expect($bin3->serial_number)->toBe("MBR-{$year}-003");
});

test('serial numbers are unique', function () {
    $bins = Bin::factory()->count(5)->create();

    $serials = $bins->pluck('serial_number')->toArray();
    expect($serials)->toHaveCount(5)
        ->and(array_unique($serials))->toHaveCount(5);
});

test('admin can create bin without providing serial number', function () {
    $response = $this->post(route('admin.bins.store'), [
        'fill_level' => 50,
        'status' => 'active',
    ]);

    $response->assertRedirect();

    $bin = Bin::first();
    $year = date('Y');
    expect($bin->serial_number)->toMatch("/^MBR-{$year}-\\d{3}$/");
});

test('create form does not contain serial number input', function () {
    $response = $this->get(route('admin.bins.create'));

    $response->assertSuccessful()
        ->assertDontSee('name="serial_number"', escape: false);
});

test('custom serial number is preserved when explicitly set', function () {
    $bin = Bin::factory()->create(['serial_number' => 'CUSTOM-001']);

    expect($bin->serial_number)->toBe('CUSTOM-001');
});
