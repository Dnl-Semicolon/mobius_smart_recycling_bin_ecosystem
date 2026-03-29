<?php

use App\Models\Bin;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('qr code endpoint returns SVG for valid bin', function () {
    $bin = Bin::factory()->create(['serial_number' => 'MBR-TEST-001']);

    $response = $this->getJson("/api/v1/bins/{$bin->id}/qr");

    $response->assertOk();
    $response->assertHeader('content-type', 'image/svg+xml');
    expect($response->getContent())->toContain('<svg');
});

test('qr code endpoint returns 404 for missing bin', function () {
    $response = $this->getJson('/api/v1/bins/99999/qr');

    $response->assertNotFound();
});

test('qr code endpoint does not require authentication', function () {
    $bin = Bin::factory()->create(['serial_number' => 'MBR-PUB-QR-001']);

    $this->getJson("/api/v1/bins/{$bin->id}/qr")
        ->assertOk();
});
