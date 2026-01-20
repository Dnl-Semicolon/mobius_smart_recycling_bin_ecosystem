<?php

use App\Logging\Sampling\TailSampler;
use App\Logging\WideEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clear the wide events log before each test
    $logPath = storage_path('logs/wide-events.log');
    if (file_exists($logPath)) {
        file_put_contents($logPath, '');
    }
});

test('api request includes X-Request-Id header', function () {
    $response = $this->getJson('/api/v1/persons');

    $response->assertHeader('X-Request-Id');
    expect($response->headers->get('X-Request-Id'))->toMatch('/^[a-f0-9-]{36}$/');
});

test('wide event is logged to wide-events channel', function () {
    $this->getJson('/api/v1/persons');

    $logPath = storage_path('logs/wide-events.log');
    expect(file_exists($logPath))->toBeTrue();

    $logContent = file_get_contents($logPath);
    expect($logContent)->not->toBeEmpty();

    $event = json_decode(trim($logContent), true);
    expect($event)->not->toBeNull();
    expect($event)->toHaveKey('_meta');
    expect($event)->toHaveKey('trace');
    expect($event)->toHaveKey('http');
    expect($event)->toHaveKey('timing');
});

test('wide event contains http metadata', function () {
    $this->getJson('/api/v1/persons?page=1');

    $logPath = storage_path('logs/wide-events.log');
    $event = json_decode(trim(file_get_contents($logPath)), true);

    expect($event['http']['method'])->toBe('GET');
    expect($event['http']['path'])->toBe('api/v1/persons');
    expect($event['http']['query_params'])->toBe('page=1');
    expect($event['http']['status_code'])->toBe(200);
});

test('wide event contains timing data', function () {
    $this->getJson('/api/v1/persons');

    $logPath = storage_path('logs/wide-events.log');
    $event = json_decode(trim(file_get_contents($logPath)), true);

    expect($event['timing']['duration_ms'])->toBeGreaterThan(0);
    expect($event['timing']['outcome'])->toBe('success');
    expect($event['timing']['timestamp'])->not->toBeNull();
});

test('wide event contains service metadata', function () {
    $this->getJson('/api/v1/persons');

    $logPath = storage_path('logs/wide-events.log');
    $event = json_decode(trim(file_get_contents($logPath)), true);

    expect($event['service']['name'])->not->toBeNull();
    expect($event['service']['environment'])->toBe('testing');
});

test('wide event captures 404 as error outcome', function () {
    $this->getJson('/api/v1/persons/99999');

    $logPath = storage_path('logs/wide-events.log');
    $event = json_decode(trim(file_get_contents($logPath)), true);

    expect($event['http']['status_code'])->toBe(404);
    expect($event['timing']['outcome'])->toBe('error');
});

test('exactly one event emitted per request', function () {
    $logPath = storage_path('logs/wide-events.log');

    // Make first request
    $this->getJson('/api/v1/persons');

    $lines1 = array_filter(explode("\n", file_get_contents($logPath)));
    expect($lines1)->toHaveCount(1);

    // Verify the event has the correct request_id
    $event = json_decode($lines1[array_key_first($lines1)], true);
    expect($event['trace']['request_id'])->not->toBeEmpty();
});

test('tail sampler always samples errors', function () {
    $event = new WideEvent;
    $event->captureError(new \RuntimeException('test'));

    $sampler = new TailSampler;

    expect($sampler->shouldSample($event))->toBeTrue();
});

test('tail sampler always samples 5xx responses', function () {
    $event = new WideEvent;
    $event->enrich('http.status_code', 500);

    // Need to mock getStatusCode since it reads from internal data
    $response = new \Symfony\Component\HttpFoundation\Response('Error', 500);
    $event->captureResponse($response);

    $sampler = new TailSampler;

    expect($sampler->shouldSample($event))->toBeTrue();
});

test('tail sampler always samples 4xx when configured', function () {
    config(['wide-events.sampling.keep_client_errors' => true]);

    $event = new WideEvent;
    $response = new \Symfony\Component\HttpFoundation\Response('Not Found', 404);
    $event->captureResponse($response);

    $sampler = new TailSampler;

    expect($sampler->shouldSample($event))->toBeTrue();
});

test('tail sampler skips 4xx when configured off', function () {
    config(['wide-events.sampling.keep_client_errors' => false]);
    config(['wide-events.sampling.base_rate' => 0]);

    $event = new WideEvent;
    $response = new \Symfony\Component\HttpFoundation\Response('Not Found', 404);
    $event->captureResponse($response);

    $sampler = new TailSampler;

    expect($sampler->shouldSample($event))->toBeFalse();
});

test('tail sampler samples slow requests', function () {
    config(['wide-events.sampling.p99_threshold_ms' => 0]); // 0ms threshold

    $event = new WideEvent;
    usleep(1000); // 1ms delay

    $sampler = new TailSampler;

    expect($sampler->shouldSample($event))->toBeTrue();
});

test('tail sampler respects base rate for success', function () {
    config(['wide-events.sampling.base_rate' => 0]);
    config(['wide-events.sampling.p99_threshold_ms' => 999999]);

    $event = new WideEvent;
    $response = new \Symfony\Component\HttpFoundation\Response('OK', 200);
    $event->captureResponse($response);

    $sampler = new TailSampler;

    // With 0% rate, should not sample success
    expect($sampler->shouldSample($event))->toBeFalse();
});

test('tail sampler returns false when disabled', function () {
    config(['wide-events.sampling.enabled' => false]);

    $event = new WideEvent;
    $event->captureError(new \RuntimeException('test')); // Even errors

    $sampler = new TailSampler;

    expect($sampler->shouldSample($event))->toBeFalse();
});

test('wide event can be resolved from container', function () {
    $event = app(WideEvent::class);

    expect($event)->toBeInstanceOf(WideEvent::class);
    expect($event->getRequestId())->not->toBeEmpty();
});

test('wide event is scoped per request', function () {
    $event1 = app(WideEvent::class);
    $event2 = app(WideEvent::class);

    // Same request, same instance
    expect($event1)->toBe($event2);
});

test('log output is valid json per line', function () {
    $this->getJson('/api/v1/persons');

    $logPath = storage_path('logs/wide-events.log');
    $lines = array_filter(explode("\n", file_get_contents($logPath)));

    foreach ($lines as $line) {
        $decoded = json_decode($line, true);
        expect($decoded)->not->toBeNull();
        expect(json_last_error())->toBe(JSON_ERROR_NONE);
    }
});

test('exception handler captures error in wide event', function () {
    // Hit a non-existent route to trigger 404
    $this->getJson('/api/v1/nonexistent');

    $logPath = storage_path('logs/wide-events.log');
    $event = json_decode(trim(file_get_contents($logPath)), true);

    expect($event['http']['status_code'])->toBe(404);
    expect($event['timing']['outcome'])->toBe('error');
});

test('validation errors are captured in wide event business context', function () {
    $response = $this->postJson('/api/v1/persons', []);

    $response->assertUnprocessable();

    $logPath = storage_path('logs/wide-events.log');
    $event = json_decode(trim(file_get_contents($logPath)), true);

    expect($event['http']['status_code'])->toBe(422);
    expect($event['business']['validation']['failed'])->toBeTrue();
    expect($event['business']['validation']['error_count'])->toBeGreaterThan(0);
    expect($event['business']['validation']['fields'])->toContain('name');
    expect($event['business']['validation']['messages'])->toHaveKey('name');
    expect($event['business']['validation']['request'])->toBe(App\Http\Requests\Example\StorePersonRequest::class);
});

test('wide event error field captures exception details when thrown', function () {
    $event = new \App\Logging\WideEvent;

    $exception = new \InvalidArgumentException('Test validation error', 422);
    $event->captureError($exception);

    $data = $event->toArray();

    expect($data['error'])->not->toBeNull();
    expect($data['error']['type'])->toBe('InvalidArgumentException');
    expect($data['error']['code'])->toBe(422);
    expect($data['error']['message'])->toBe('Test validation error');
});
