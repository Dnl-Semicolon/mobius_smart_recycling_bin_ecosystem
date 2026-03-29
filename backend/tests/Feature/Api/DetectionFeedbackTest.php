<?php

use App\Models\DetectionEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('records positive feedback', function () {
    $event = DetectionEvent::factory()->create();

    $response = $this->postJson("/api/v1/detection-events/{$event->id}/feedback", [
        'accurate' => true,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.id', $event->id)
        ->assertJsonPath('data.feedback_accurate', true)
        ->assertJsonPath('message', 'Feedback recorded successfully.');

    $this->assertDatabaseHas('detection_events', [
        'id' => $event->id,
        'feedback_accurate' => true,
    ]);
});

it('records negative feedback', function () {
    $event = DetectionEvent::factory()->create();

    $response = $this->postJson("/api/v1/detection-events/{$event->id}/feedback", [
        'accurate' => false,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.feedback_accurate', false);

    $this->assertDatabaseHas('detection_events', [
        'id' => $event->id,
        'feedback_accurate' => false,
    ]);
});

it('rejects missing accurate field', function () {
    $event = DetectionEvent::factory()->create();

    $response = $this->postJson("/api/v1/detection-events/{$event->id}/feedback", []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('accurate');
});

it('returns 404 for non-existent detection event', function () {
    $response = $this->postJson('/api/v1/detection-events/99999/feedback', [
        'accurate' => true,
    ]);

    $response->assertNotFound();
});

it('overwrites previous feedback', function () {
    $event = DetectionEvent::factory()->create(['feedback_accurate' => true]);

    $response = $this->postJson("/api/v1/detection-events/{$event->id}/feedback", [
        'accurate' => false,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.feedback_accurate', false);

    $this->assertDatabaseHas('detection_events', [
        'id' => $event->id,
        'feedback_accurate' => false,
    ]);
});
