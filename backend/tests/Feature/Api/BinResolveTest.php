<?php

use App\Models\Bin;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('resolves a bin by serial number', function () {
    $bin = Bin::factory()->create(['serial_number' => 'MBR-TEST-001']);

    $this->getJson('/api/v1/bins/resolve/MBR-TEST-001')
        ->assertSuccessful()
        ->assertJsonPath('data.id', $bin->id)
        ->assertJsonPath('data.serial_number', 'MBR-TEST-001');
});

it('returns 404 for unknown serial', function () {
    $this->getJson('/api/v1/bins/resolve/MBR-NOPE-999')
        ->assertNotFound()
        ->assertJsonPath('message', 'Bin not found.');
});

it('does not require authentication', function () {
    Bin::factory()->create(['serial_number' => 'MBR-PUB-001']);

    $this->getJson('/api/v1/bins/resolve/MBR-PUB-001')
        ->assertSuccessful();
});

it('accepts a heartbeat from a bin', function () {
    $bin = Bin::factory()->create(['serial_number' => 'MBR-HB-001']);

    $this->postJson("/api/v1/bins/{$bin->id}/heartbeat", [
        'fill_level' => 42,
        'compartments' => [
            'solids' => ['fill_percent' => 35, 'volume_ml' => 5200, 'weight_g' => 1800],
            'liquid' => ['fill_percent' => 42, 'volume_ml' => 1260, 'weight_g' => 1260],
        ],
        'ip_address' => '192.168.1.50:9001',
    ])
        ->assertSuccessful()
        ->assertJsonPath('data.fill_level', 42);

    $bin->refresh();
    expect($bin->fill_level)->toBe(42)
        ->and($bin->compartments)->toBeArray()
        ->and($bin->compartments['solids']['fill_percent'])->toBe(35)
        ->and($bin->last_seen_at)->not->toBeNull()
        ->and($bin->ip_address)->toBe('192.168.1.50:9001');
});

it('rejects heartbeat with invalid fill level', function () {
    $bin = Bin::factory()->create();

    $this->postJson("/api/v1/bins/{$bin->id}/heartbeat", [
        'fill_level' => 150,
    ])->assertUnprocessable();
});

it('heartbeat does not require authentication', function () {
    $bin = Bin::factory()->create();

    $this->postJson("/api/v1/bins/{$bin->id}/heartbeat", [
        'fill_level' => 10,
    ])->assertSuccessful();
});
