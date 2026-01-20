<?php

use App\Logging\Filters\PiiFilter;
use App\Logging\WideEvent;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

test('wide event generates unique request id', function () {
    $event1 = new WideEvent;
    $event2 = new WideEvent;

    expect($event1->getRequestId())->not->toBeEmpty();
    expect($event2->getRequestId())->not->toBeEmpty();
    expect($event1->getRequestId())->not->toBe($event2->getRequestId());
});

test('wide event has correct schema structure', function () {
    $event = new WideEvent;
    $data = $event->toArray();

    expect($data)->toHaveKeys([
        '_meta',
        'trace',
        'service',
        'http',
        'timing',
        'user',
        'business',
        'error',
        'dependencies',
    ]);

    expect($data['_meta'])->toHaveKey('schema_version', '1.0');
    expect($data['trace'])->toHaveKey('request_id');
    expect($data['trace'])->toHaveKey('span_id');
});

test('enrich adds data at dot notation path', function () {
    $event = new WideEvent;
    $event->enrich('business.order.id', 'order_123');
    $event->enrich('business.order.total', 1599);

    $data = $event->toArray();

    expect($data['business']['order']['id'])->toBe('order_123');
    expect($data['business']['order']['total'])->toBe(1599);
});

test('enrichMany adds multiple fields', function () {
    $event = new WideEvent;
    $event->enrichMany([
        'business.cart.id' => 'cart_456',
        'business.cart.item_count' => 3,
    ]);

    $data = $event->toArray();

    expect($data['business']['cart']['id'])->toBe('cart_456');
    expect($data['business']['cart']['item_count'])->toBe(3);
});

test('addDependency tracks external calls', function () {
    $event = new WideEvent;
    $event->addDependency(
        name: 'postgres',
        type: 'db',
        latencyMs: 45.5,
        success: true,
        extra: ['query_count' => 3]
    );

    $data = $event->toArray();

    expect($data['dependencies'])->toHaveCount(1);
    expect($data['dependencies'][0]['name'])->toBe('postgres');
    expect($data['dependencies'][0]['type'])->toBe('db');
    expect($data['dependencies'][0]['latency_ms'])->toBe(45.5);
    expect($data['dependencies'][0]['success'])->toBeTrue();
    expect($data['dependencies'][0]['query_count'])->toBe(3);
});

test('captureRequest extracts request metadata', function () {
    $request = Request::create('/api/v1/users', 'GET', ['page' => '1']);
    $request->headers->set('User-Agent', 'TestAgent/1.0');

    $event = new WideEvent;
    $event->captureRequest($request);

    $data = $event->toArray();

    expect($data['http']['method'])->toBe('GET');
    expect($data['http']['path'])->toBe('api/v1/users');
    expect($data['http']['query_params'])->toBe('page=1');
    expect($data['http']['user_agent'])->toBe('TestAgent/1.0');
});

test('captureResponse sets status code and outcome', function () {
    $response = new Response('OK', 200);

    $event = new WideEvent;
    $event->captureResponse($response);

    $data = $event->toArray();

    expect($data['http']['status_code'])->toBe(200);
    expect($data['timing']['outcome'])->toBe('success');
});

test('captureResponse marks error outcome for 4xx and 5xx', function () {
    $response400 = new Response('Bad Request', 400);
    $response500 = new Response('Server Error', 500);

    $event400 = new WideEvent;
    $event400->captureResponse($response400);

    $event500 = new WideEvent;
    $event500->captureResponse($response500);

    expect($event400->toArray()['timing']['outcome'])->toBe('error');
    expect($event500->toArray()['timing']['outcome'])->toBe('error');
});

test('captureError stores exception details', function () {
    $exception = new \RuntimeException('Test error', 42);

    $event = new WideEvent;
    $event->captureError($exception);

    $data = $event->toArray();

    expect($data['error'])->not->toBeNull();
    expect($data['error']['type'])->toBe('RuntimeException');
    expect($data['error']['code'])->toBe(42);
    expect($data['error']['message'])->toBe('Test error');
    expect($data['error']['stack'])->toBeNull();
    expect($data['timing']['outcome'])->toBe('error');
});

test('captureError includes stack when requested', function () {
    $exception = new \RuntimeException('Test error');

    $event = new WideEvent;
    $event->captureError($exception, includeStack: true);

    $data = $event->toArray();

    expect($data['error']['stack'])->not->toBeNull();
    expect($data['error']['stack'])->toContain('WideEventTest.php');
});

test('hasError returns true after captureError', function () {
    $event = new WideEvent;
    expect($event->hasError())->toBeFalse();

    $event->captureError(new \Exception('test'));
    expect($event->hasError())->toBeTrue();
});

test('wasEmitted and markEmitted track emission state', function () {
    $event = new WideEvent;

    expect($event->wasEmitted())->toBeFalse();

    $event->markEmitted();

    expect($event->wasEmitted())->toBeTrue();
});

test('getDurationMs returns positive value', function () {
    $event = new WideEvent;

    usleep(1000); // 1ms

    expect($event->getDurationMs())->toBeGreaterThan(0);
});

test('timing duration_ms is set in toArray', function () {
    $event = new WideEvent;

    usleep(1000); // 1ms

    $data = $event->toArray();

    expect($data['timing']['duration_ms'])->toBeGreaterThan(0);
});

test('toJson returns valid JSON string', function () {
    $event = new WideEvent;
    $event->enrich('business.test', 'value');

    $json = $event->toJson();
    $decoded = json_decode($json, true);

    expect($decoded)->not->toBeNull();
    expect($decoded['business']['test'])->toBe('value');
});

test('extracts trace id from X-Trace-Id header', function () {
    $request = Request::create('/test');
    $request->headers->set('X-Trace-Id', 'trace-123-456');

    $event = new WideEvent($request);

    expect($event->getTraceId())->toBe('trace-123-456');
});

test('service metadata populated from config', function () {
    config(['app.name' => 'TestApp']);
    config(['app.env' => 'testing']);
    config(['wide-events.service_version' => '2.0.0']);

    $event = new WideEvent;
    $data = $event->toArray();

    expect($data['service']['name'])->toBe('TestApp');
    expect($data['service']['environment'])->toBe('testing');
    expect($data['service']['version'])->toBe('2.0.0');
});

test('pii filter redacts blocklisted fields', function () {
    config(['wide-events.pii.blocklist' => ['password', 'secret']]);
    config(['wide-events.pii.hash_fields' => []]);

    $filter = new PiiFilter;
    $filtered = $filter->filter([
        'username' => 'john',
        'password' => 'secret123',
        'nested' => [
            'secret' => 'api_key_value',
        ],
    ]);

    expect($filtered['username'])->toBe('john');
    expect($filtered['password'])->toBe('[REDACTED]');
    expect($filtered['nested']['secret'])->toBe('[REDACTED]');
});

test('pii filter hashes specified fields', function () {
    config(['wide-events.pii.blocklist' => []]);
    config(['wide-events.pii.hash_fields' => ['email']]);

    $filter = new PiiFilter;
    $filtered = $filter->filter([
        'email' => 'test@example.com',
        'name' => 'John',
    ]);

    expect($filtered['name'])->toBe('John');
    expect($filtered['email'])->toStartWith('sha256:');
    expect($filtered['email'])->not->toContain('test@example.com');
});

test('pii filter handles nested arrays', function () {
    config(['wide-events.pii.blocklist' => ['password']]);
    config(['wide-events.pii.hash_fields' => ['email']]);

    $filter = new PiiFilter;
    $filtered = $filter->filter([
        'user' => [
            'profile' => [
                'email' => 'user@test.com',
                'password' => 'secret',
            ],
        ],
    ]);

    expect($filtered['user']['profile']['email'])->toStartWith('sha256:');
    expect($filtered['user']['profile']['password'])->toBe('[REDACTED]');
});
