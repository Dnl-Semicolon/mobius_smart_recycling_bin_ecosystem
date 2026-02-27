<?php

use App\Models\Bin;
use App\Models\DetectionEvent;
use App\Models\Outlet;
use App\Models\PickupRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('public stats endpoint returns community stats without auth', function () {
    $bin = Bin::factory()->create();
    DetectionEvent::factory()->count(5)->create(['bin_id' => $bin->id]);
    PickupRequest::factory()->completed()->count(2)->create();
    Outlet::factory()->count(3)->create();

    $response = $this->getJson(route('api.public.stats'));

    $response->assertOk()
        ->assertJsonPath('message', 'Community stats retrieved successfully.')
        ->assertJsonStructure([
            'data' => [
                'total_detections',
                'pickups_completed',
                'active_bins',
                'outlets_served',
                'waste_type_distribution',
            ],
        ]);

    expect($response->json('data.total_detections'))->toBe(5)
        ->and($response->json('data.pickups_completed'))->toBe(2)
        ->and($response->json('data.outlets_served'))->toBe(3);
});

test('public stats returns zeros when no data exists', function () {
    $response = $this->getJson(route('api.public.stats'));

    $response->assertOk();

    expect($response->json('data.total_detections'))->toBe(0)
        ->and($response->json('data.pickups_completed'))->toBe(0)
        ->and($response->json('data.active_bins'))->toBe(0)
        ->and($response->json('data.outlets_served'))->toBe(0)
        ->and($response->json('data.waste_type_distribution'))->toBeEmpty();
});
