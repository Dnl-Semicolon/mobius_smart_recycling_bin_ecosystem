<?php

use App\Models\Bin;
use App\Models\PickupRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('collector can fetch personal stats', function () {
    $collector = User::factory()->collector()->create();
    $bin = Bin::factory()->create(['fill_level' => 90]);

    PickupRequest::factory()->completed($collector)->count(3)->create(['bin_id' => $bin->id]);
    PickupRequest::factory()->claimed($collector)->create(['bin_id' => $bin->id]);

    Sanctum::actingAs($collector);

    $response = $this->getJson(route('api.collector.stats'));

    $response->assertOk()
        ->assertJsonPath('message', 'Collector stats retrieved successfully.')
        ->assertJsonStructure([
            'data' => [
                'total_completed',
                'this_week',
                'active_now',
                'avg_minutes',
            ],
        ]);

    expect($response->json('data.total_completed'))->toBe(3)
        ->and($response->json('data.active_now'))->toBe(1);
});

test('new collector gets zero stats', function () {
    $collector = User::factory()->collector()->create();

    Sanctum::actingAs($collector);

    $response = $this->getJson(route('api.collector.stats'));

    $response->assertOk();

    expect($response->json('data.total_completed'))->toBe(0)
        ->and($response->json('data.this_week'))->toBe(0)
        ->and($response->json('data.active_now'))->toBe(0)
        ->and($response->json('data.avg_minutes'))->toBeNull();
});

test('admin cannot access collector stats', function () {
    Sanctum::actingAs(User::factory()->admin()->create());

    $this->getJson(route('api.collector.stats'))
        ->assertForbidden();
});
