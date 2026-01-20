# Wide Events Logging System

This document describes the wide events logging system implementation for the Mobius backend.

## Overview

Wide events emit **one high-dimensionality JSON log line per request** containing all debugging context. This replaces scattered log statements with a single structured record per request.

## Quick Start

The system is enabled by default. Every HTTP request automatically generates a wide event in `storage/logs/wide-events.log`.

### Check It's Working

```bash
# Start the server
php artisan serve

# Make a request
curl http://localhost:8000/api/v1/persons -I

# Check the log
cat storage/logs/wide-events.log
```

You should see a JSON line with request details.

## Enriching Events in Controllers

Inject `WideEvent` and call `enrich()` to add business context:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Logging\WideEvent;
use App\Models\Person;

class PersonController extends Controller
{
    public function __construct(private WideEvent $wideEvent) {}

    public function show(Person $person): JsonResponse
    {
        // Add business context to the wide event
        $this->wideEvent->enrich('business.person.id', $person->id);
        $this->wideEvent->enrich('business.person.address_count', $person->addresses()->count());

        return PersonResource::make($person->load('addresses'))
            ->response();
    }
}
```

### Dot Notation

Use dot notation for nested paths:

```php
$this->wideEvent->enrich('business.order.id', 'order_123');
$this->wideEvent->enrich('business.order.total_cents', 1599);
$this->wideEvent->enrich('business.order.currency', 'MYR');
```

### Bulk Enrichment

Use `enrichMany()` for multiple fields:

```php
$this->wideEvent->enrichMany([
    'business.cart.id' => $cart->id,
    'business.cart.item_count' => $cart->items->count(),
    'business.cart.total' => $cart->total,
]);
```

## Tracking External Dependencies

Track external service calls with timing:

```php
$startTime = microtime(true);
$result = Http::get('https://api.example.com/data');

$this->wideEvent->addDependency(
    name: 'example-api',
    type: 'http',
    latencyMs: (microtime(true) - $startTime) * 1000,
    success: $result->successful(),
    extra: ['endpoint' => '/data', 'status' => $result->status()]
);
```

## Configuration

### Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `WIDE_EVENTS_ENABLED` | `true` | Enable/disable wide event logging |
| `WIDE_EVENTS_P99_THRESHOLD_MS` | `2000` | Slow request threshold in milliseconds |
| `WIDE_EVENTS_SAMPLE_RATE` | `1.0` | Base sampling rate (1.0 = 100%) |
| `WIDE_EVENTS_KEEP_CLIENT_ERRORS` | `true` | Always log 4xx errors |
| `WIDE_EVENTS_INCLUDE_STACK` | `false` | Include stack traces in errors |
| `APP_VERSION` | `1.0.0` | Service version in events |

### Tail Sampling

The system uses tail sampling to reduce log volume while keeping important events:

| Condition | Sample Rate |
|-----------|-------------|
| Errors (exceptions or 5xx) | 100% |
| Slow requests (> threshold) | 100% |
| Client errors (4xx) | 100% (configurable) |
| Normal success | Configurable (default 100%) |

For production, set `WIDE_EVENTS_SAMPLE_RATE=0.05` to sample 5% of success traffic.

## Event Schema

Each event contains:

```json
{
  "_meta": {
    "schema_version": "1.0",
    "emitted_at": "2026-01-17T15:30:45.123Z"
  },
  "trace": {
    "request_id": "uuid",
    "trace_id": null,
    "span_id": "uuid",
    "parent_span_id": null
  },
  "service": {
    "name": "Mobius",
    "version": "1.0.0",
    "environment": "local",
    "host": "hostname"
  },
  "http": {
    "method": "GET",
    "path": "api/v1/persons",
    "route": "api.v1.persons.index",
    "query_params": "page=1",
    "status_code": 200,
    "client_ip": "127.0.0.1",
    "user_agent": "Mozilla/5.0..."
  },
  "timing": {
    "timestamp": "2026-01-17T15:30:45.000Z",
    "duration_ms": 45.23,
    "outcome": "success"
  },
  "user": {},
  "business": {
    "custom": {}
  },
  "error": null,
  "dependencies": []
}
```

## Correlation

Every response includes an `X-Request-Id` header matching the event's `trace.request_id`. Use this to correlate client requests with server logs.

To propagate trace context from upstream services, send `X-Trace-Id` header with incoming requests.

## PII Filtering

The system automatically filters sensitive data:

**Redacted fields** (replaced with `[REDACTED]`):
- password, password_confirmation
- token, api_key, secret
- credit_card, card_number, cvv, pan
- ssn, social_security

**Hashed fields** (replaced with `sha256:{hash_prefix}`):
- email
- phone

Configure in `config/wide-events.php`:

```php
'pii' => [
    'blocklist' => ['password', 'token', 'secret', ...],
    'hash_fields' => ['email', 'phone'],
],
```

## Files

| File | Purpose |
|------|---------|
| `config/wide-events.php` | Configuration |
| `app/Logging/WideEvent.php` | Core event class |
| `app/Logging/WideEventMiddleware.php` | Request lifecycle handling |
| `app/Logging/WideEventServiceProvider.php` | Service bindings |
| `app/Logging/Sampling/TailSampler.php` | Sampling logic |
| `app/Logging/Filters/PiiFilter.php` | PII redaction |
| `app/Logging/Formatters/WideEventFormatter.php` | JSON output format |

## Testing

Run the wide events test suite:

```bash
php artisan test --compact --filter=WideEvent
```

## Disabling

To disable wide events:

```env
WIDE_EVENTS_ENABLED=false
```

Or remove the middleware from `bootstrap/app.php`.
