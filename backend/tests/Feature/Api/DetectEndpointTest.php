<?php

use App\Models\Bin;
use App\Models\DetectionEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

// Happy path — image only (legacy/server-side ML flow)
test('store creates detection event with image only', function () {
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
                'image_path',
                'detected_at',
            ],
            'message',
        ])
        ->assertJsonPath('data.bin_id', $bin->id)
        ->assertJsonPath('data.detections', [])
        ->assertJsonPath('message', 'Detection event created successfully.');

    $this->assertDatabaseHas('detection_events', [
        'bin_id' => $bin->id,
        'waste_type' => null,
        'confidence' => null,
    ]);

    Storage::disk('public')->assertExists($response->json('data.image_path'));
});

// Happy path — edge compute (bin sends waste_type + confidence)
test('store creates detection with waste_type and confidence from edge compute', function () {
    $bin = Bin::factory()->active()->empty()->create();
    $image = UploadedFile::fake()->image('cup.jpg');

    $response = $this->postJson('/api/v1/detect', [
        'bin_id' => $bin->id,
        'image' => $image,
        'waste_type' => 'paper_cup',
        'confidence' => 85,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.detections.0.waste_type', 'paper_cup')
        ->assertJsonPath('data.detections.0.confidence', 85);

    $this->assertDatabaseHas('detection_events', [
        'bin_id' => $bin->id,
        'waste_type' => 'paper_cup',
        'confidence' => 85,
    ]);

    // Observer should have bumped fill level (+5 for paper_cup)
    expect($bin->fresh()->fill_level)->toBe(5);
});

// Edge compute with user_id awards points
test('store with waste_type and user_id awards points', function () {
    $bin = Bin::factory()->active()->empty()->create();
    $user = User::factory()->publicUser()->create(['points_balance' => 0]);

    $response = $this->postJson('/api/v1/detect', [
        'bin_id' => $bin->id,
        'waste_type' => 'paper_cup',
        'confidence' => 90,
        'user_id' => $user->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.user_id', $user->id);

    expect($user->fresh()->points_balance)->toBe(15);

    $this->assertDatabaseHas('recycling_transactions', [
        'user_id' => $user->id,
        'points_earned' => 15,
        'type' => 'earned',
    ]);
});

// Without image (bin doesn't always send images)
test('store works without image when waste_type is provided', function () {
    $bin = Bin::factory()->active()->empty()->create();

    $response = $this->postJson('/api/v1/detect', [
        'bin_id' => $bin->id,
        'waste_type' => 'lid',
        'confidence' => 75,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.image_path', null)
        ->assertJsonPath('data.detections.0.waste_type', 'lid');

    expect($bin->fresh()->fill_level)->toBe(2);
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
        ->assertJsonValidationErrors(['bin_id']);
});

test('store rejects invalid waste_type', function () {
    $bin = Bin::factory()->create();

    $response = $this->postJson('/api/v1/detect', [
        'bin_id' => $bin->id,
        'waste_type' => 'banana_peel',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['waste_type']);
});

test('store rejects confidence out of range', function () {
    $bin = Bin::factory()->create();

    $response = $this->postJson('/api/v1/detect', [
        'bin_id' => $bin->id,
        'waste_type' => 'paper_cup',
        'confidence' => 150,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['confidence']);
});

test('store rejects non-existent user_id', function () {
    $bin = Bin::factory()->create();

    $response = $this->postJson('/api/v1/detect', [
        'bin_id' => $bin->id,
        'waste_type' => 'paper_cup',
        'confidence' => 80,
        'user_id' => 9999,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['user_id']);
});

// Classify endpoint
test('classify updates waste_type and triggers fill level', function () {
    $bin = Bin::factory()->active()->empty()->create();
    $event = DetectionEvent::factory()->create([
        'bin_id' => $bin->id,
        'waste_type' => null,
        'confidence' => null,
    ]);

    $response = $this->patchJson("/api/v1/detect/{$event->id}/classify", [
        'waste_type' => 'plastic_cup',
        'confidence' => 92,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.waste_type', 'plastic_cup')
        ->assertJsonPath('data.confidence', 92);

    expect($bin->fresh()->fill_level)->toBe(5);
});

test('classify with user_id awards points', function () {
    $bin = Bin::factory()->active()->empty()->create();
    $user = User::factory()->publicUser()->create(['points_balance' => 0]);
    $event = DetectionEvent::factory()->create([
        'bin_id' => $bin->id,
        'user_id' => $user->id,
        'waste_type' => null,
        'confidence' => null,
    ]);

    $this->patchJson("/api/v1/detect/{$event->id}/classify", [
        'waste_type' => 'lid',
        'confidence' => 88,
    ]);

    expect($user->fresh()->points_balance)->toBe(5);
    $this->assertDatabaseHas('recycling_transactions', [
        'user_id' => $user->id,
        'points_earned' => 5,
    ]);
});

test('classify rejects already classified event', function () {
    $event = DetectionEvent::factory()->create([
        'waste_type' => 'paper_cup',
        'confidence' => 85,
    ]);

    $response = $this->patchJson("/api/v1/detect/{$event->id}/classify", [
        'waste_type' => 'lid',
        'confidence' => 90,
    ]);

    $response->assertStatus(409)
        ->assertJsonPath('message', 'Detection event has already been classified.');
});

test('classify requires waste_type and confidence', function () {
    $event = DetectionEvent::factory()->create([
        'waste_type' => null,
        'confidence' => null,
    ]);

    $response = $this->patchJson("/api/v1/detect/{$event->id}/classify", []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['waste_type', 'confidence']);
});

// Existing read-only resource is unaffected
test('detection-events resource POST still returns 405', function () {
    $bin = Bin::factory()->create();

    $response = $this->postJson('/api/v1/detection-events', [
        'bin_id' => $bin->id,
    ]);

    $response->assertStatus(405);
});
