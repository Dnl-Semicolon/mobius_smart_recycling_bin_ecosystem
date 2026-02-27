<?php

use App\Models\Bin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

test('admin can simulate a detection event', function () {
    $bin = Bin::factory()->create(['fill_level' => 10]);

    $response = $this->post(route('admin.detection-events.simulate'), [
        'bin_id' => $bin->id,
        'waste_type' => 'paper_cup',
        'confidence' => 85,
    ]);

    $response->assertRedirect(route('admin.detection-events.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('detection_events', [
        'bin_id' => $bin->id,
        'waste_type' => 'paper_cup',
        'confidence' => 85,
    ]);

    // Observer should have bumped fill_level by 5
    expect($bin->fresh()->fill_level)->toBe(15);
});

test('simulating with no confidence uses a random value', function () {
    $bin = Bin::factory()->create(['fill_level' => 0]);

    $this->post(route('admin.detection-events.simulate'), [
        'bin_id' => $bin->id,
        'waste_type' => 'straw',
    ]);

    $event = $bin->detectionEvents()->first();
    expect($event->confidence)->toBeGreaterThanOrEqual(75)
        ->and($event->confidence)->toBeLessThanOrEqual(99);
});

test('simulate requires a valid bin and waste type', function () {
    $this->post(route('admin.detection-events.simulate'), [])
        ->assertSessionHasErrors(['bin_id', 'waste_type']);
});

test('simulate rejects invalid waste type', function () {
    $bin = Bin::factory()->create();

    $this->post(route('admin.detection-events.simulate'), [
        'bin_id' => $bin->id,
        'waste_type' => 'pizza_box',
    ])->assertSessionHasErrors('waste_type');
});

test('non-admin cannot simulate detections', function () {
    $collector = User::factory()->collector()->create();
    $bin = Bin::factory()->create();

    $this->actingAs($collector)
        ->post(route('admin.detection-events.simulate'), [
            'bin_id' => $bin->id,
            'waste_type' => 'paper_cup',
        ])
        ->assertForbidden();
});

test('detection events index shows simulate form', function () {
    $this->get(route('admin.detection-events.index'))
        ->assertOk()
        ->assertSee('Simulate Detection');
});
