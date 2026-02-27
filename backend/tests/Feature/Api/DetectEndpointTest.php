<?php

use App\Models\Bin;
use App\Models\DetectionEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

// Happy path
test('store creates detection event and returns 201', function () {
    $bin = Bin::factory()->create();
    $image = UploadedFile::fake()->image('cup.jpg', 640, 480);

    $response = $this->postJson('/api/v1/detect', [
        'bin_id' => $bin->id,
        'image' => $image,
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'id',
                'bin_id',
                'detections',
                'model_version',
                'latency_ms',
                'image_path',
                'detected_at',
            ],
            'message',
        ])
        ->assertJsonPath('data.bin_id', $bin->id)
        ->assertJsonPath('data.detections', [])
        ->assertJsonPath('data.model_version', null)
        ->assertJsonPath('message', 'Detection event created successfully.');

    $this->assertDatabaseHas('detection_events', [
        'bin_id' => $bin->id,
        'waste_type' => null,
        'confidence' => null,
    ]);

    Storage::disk('public')->assertExists($response->json('data.image_path'));
});

test('store accepts custom detected_at', function () {
    $bin = Bin::factory()->create();
    $image = UploadedFile::fake()->image('cup.jpg');
    $detectedAt = '2026-02-06T10:00:00+08:00';

    $response = $this->postJson('/api/v1/detect', [
        'bin_id' => $bin->id,
        'image' => $image,
        'detected_at' => $detectedAt,
    ]);

    $response->assertCreated();

    $event = DetectionEvent::find($response->json('data.id'));
    expect($event->detected_at)->not->toBeNull();
});

test('store defaults detected_at to now when not provided', function () {
    $bin = Bin::factory()->create();
    $image = UploadedFile::fake()->image('cup.jpg');

    $response = $this->postJson('/api/v1/detect', [
        'bin_id' => $bin->id,
        'image' => $image,
    ]);

    $response->assertCreated();

    $event = DetectionEvent::find($response->json('data.id'));
    expect($event->detected_at)->not->toBeNull();
});

test('store saves image to date-based path', function () {
    $bin = Bin::factory()->create();
    $image = UploadedFile::fake()->image('cup.jpg');

    $response = $this->postJson('/api/v1/detect', [
        'bin_id' => $bin->id,
        'image' => $image,
    ]);

    $response->assertCreated();

    $imagePath = $response->json('data.image_path');
    $datePath = now()->format('Y/m/d');

    expect($imagePath)->toStartWith("detection_images/{$datePath}/");
    Storage::disk('public')->assertExists($imagePath);
});

test('store accepts png images', function () {
    $bin = Bin::factory()->create();
    $image = UploadedFile::fake()->image('cup.png');

    $response = $this->postJson('/api/v1/detect', [
        'bin_id' => $bin->id,
        'image' => $image,
    ]);

    $response->assertCreated();
});

// Validation failures
test('store requires bin_id', function () {
    $image = UploadedFile::fake()->image('cup.jpg');

    $response = $this->postJson('/api/v1/detect', [
        'image' => $image,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['bin_id']);
});

test('store requires image', function () {
    $bin = Bin::factory()->create();

    $response = $this->postJson('/api/v1/detect', [
        'bin_id' => $bin->id,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['image']);
});

test('store rejects non-existent bin', function () {
    $image = UploadedFile::fake()->image('cup.jpg');

    $response = $this->postJson('/api/v1/detect', [
        'bin_id' => 9999,
        'image' => $image,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['bin_id']);
});

test('store rejects non-image files', function () {
    $bin = Bin::factory()->create();
    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    $response = $this->postJson('/api/v1/detect', [
        'bin_id' => $bin->id,
        'image' => $file,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['image']);
});

test('store rejects images over 5MB', function () {
    $bin = Bin::factory()->create();
    $image = UploadedFile::fake()->image('huge.jpg')->size(6000);

    $response = $this->postJson('/api/v1/detect', [
        'bin_id' => $bin->id,
        'image' => $image,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['image']);
});

test('store rejects empty request', function () {
    $response = $this->postJson('/api/v1/detect', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['bin_id', 'image']);
});

// Existing read-only resource is unaffected
test('detection-events resource POST still returns 405', function () {
    $bin = Bin::factory()->create();

    $response = $this->postJson('/api/v1/detection-events', [
        'bin_id' => $bin->id,
    ]);

    $response->assertStatus(405);
});
