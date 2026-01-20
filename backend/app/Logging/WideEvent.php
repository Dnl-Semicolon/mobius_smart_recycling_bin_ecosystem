<?php

namespace App\Logging;

use App\Logging\Filters\PiiFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class WideEvent
{
    private float $startTime;

    private bool $emitted = false;

    /**
     * @var array<string, mixed>
     */
    private array $data = [
        '_meta' => [
            'schema_version' => '1.0',
            'emitted_at' => null,
        ],
        'trace' => [
            'request_id' => null,
            'trace_id' => null,
            'span_id' => null,
            'parent_span_id' => null,
        ],
        'service' => [
            'name' => null,
            'version' => null,
            'environment' => null,
            'deployment_id' => null,
            'git_sha' => null,
            'region' => null,
            'host' => null,
        ],
        'http' => [
            'method' => null,
            'path' => null,
            'route' => null,
            'query_params' => null,
            'status_code' => null,
            'request_size_bytes' => null,
            'response_size_bytes' => null,
            'client_ip' => null,
            'user_agent' => null,
        ],
        'timing' => [
            'timestamp' => null,
            'duration_ms' => null,
            'outcome' => null,
        ],
        'user' => [],
        'business' => [
            'custom' => [],
        ],
        'error' => null,
        'dependencies' => [],
    ];

    public function __construct(?Request $request = null)
    {
        $this->startTime = microtime(true);

        $this->data['trace']['request_id'] = Str::uuid()->toString();
        $this->data['trace']['span_id'] = Str::uuid()->toString();
        $this->data['timing']['timestamp'] = now()->toIso8601String();

        $this->data['service']['name'] = config('app.name');
        $this->data['service']['version'] = config('wide-events.service_version');
        $this->data['service']['environment'] = config('app.env');
        $this->data['service']['deployment_id'] = config('wide-events.deployment_id');
        $this->data['service']['git_sha'] = config('wide-events.git_sha');
        $this->data['service']['region'] = config('wide-events.region');
        $this->data['service']['host'] = gethostname() ?: null;

        if ($request !== null) {
            $this->extractTraceHeaders($request);
        }
    }

    /**
     * Extract trace ID from incoming request headers.
     */
    private function extractTraceHeaders(Request $request): void
    {
        $traceId = $request->header('X-Trace-Id')
            ?? $request->header('traceparent');

        if ($traceId !== null) {
            $this->data['trace']['trace_id'] = $traceId;
        }

        $parentSpanId = $request->header('X-Parent-Span-Id');
        if ($parentSpanId !== null) {
            $this->data['trace']['parent_span_id'] = $parentSpanId;
        }
    }

    /**
     * Enrich the event with a value at a dot-notation path.
     */
    public function enrich(string $key, mixed $value): self
    {
        data_set($this->data, $key, $value);

        return $this;
    }

    /**
     * Enrich with multiple key-value pairs.
     *
     * @param  array<string, mixed>  $fields
     */
    public function enrichMany(array $fields): self
    {
        foreach ($fields as $key => $value) {
            $this->enrich($key, $value);
        }

        return $this;
    }

    /**
     * Add an external dependency call to the event.
     *
     * @param  array<string, mixed>  $extra
     */
    public function addDependency(
        string $name,
        string $type,
        float $latencyMs,
        bool $success,
        array $extra = []
    ): self {
        $this->data['dependencies'][] = array_merge([
            'name' => $name,
            'type' => $type,
            'latency_ms' => round($latencyMs, 2),
            'success' => $success,
        ], $extra);

        return $this;
    }

    /**
     * Capture request metadata.
     */
    public function captureRequest(Request $request): self
    {
        $this->data['http']['method'] = $request->method();
        $this->data['http']['path'] = $request->path();
        $this->data['http']['route'] = $request->route()?->getName();
        $this->data['http']['query_params'] = $request->getQueryString();
        $this->data['http']['client_ip'] = $request->ip();
        $this->data['http']['user_agent'] = $request->userAgent();

        $content = $request->getContent();
        $this->data['http']['request_size_bytes'] = is_string($content) ? strlen($content) : 0;

        $this->extractTraceHeaders($request);

        return $this;
    }

    /**
     * Capture response metadata.
     */
    public function captureResponse(Response $response): self
    {
        $this->data['http']['status_code'] = $response->getStatusCode();
        $this->data['http']['response_size_bytes'] = strlen((string) $response->getContent());

        $statusCode = $response->getStatusCode();
        $this->data['timing']['outcome'] = $statusCode >= 400 ? 'error' : 'success';

        return $this;
    }

    /**
     * Capture exception details.
     */
    public function captureError(\Throwable $e, bool $includeStack = false): self
    {
        $this->data['error'] = [
            'type' => get_class($e),
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'retriable' => false,
            'stack' => $includeStack ? $e->getTraceAsString() : null,
        ];

        $this->data['timing']['outcome'] = 'error';

        return $this;
    }

    /**
     * Capture user context (non-PII).
     */
    public function captureUser(?object $user): self
    {
        if ($user === null) {
            return $this;
        }

        $this->data['user'] = [
            'id' => $user->id ?? null,
            'session_id' => session()->getId(),
        ];

        return $this;
    }

    public function getRequestId(): string
    {
        return $this->data['trace']['request_id'];
    }

    public function getTraceId(): ?string
    {
        return $this->data['trace']['trace_id'];
    }

    public function getDurationMs(): float
    {
        return (microtime(true) - $this->startTime) * 1000;
    }

    public function getStatusCode(): ?int
    {
        return $this->data['http']['status_code'];
    }

    /**
     * @return array<string, mixed>
     */
    public function getUserData(): array
    {
        return $this->data['user'];
    }

    public function hasError(): bool
    {
        return $this->data['error'] !== null;
    }

    public function wasEmitted(): bool
    {
        return $this->emitted;
    }

    public function markEmitted(): self
    {
        $this->emitted = true;

        return $this;
    }

    /**
     * Convert to array, applying PII filtering and finalizing timing.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $this->data['timing']['duration_ms'] = round($this->getDurationMs(), 2);
        $this->data['_meta']['emitted_at'] = now()->toIso8601String();

        $piiFilter = app(PiiFilter::class);

        return $piiFilter->filter($this->data);
    }

    public function toJson(): string
    {
        return json_encode(
            $this->toArray(),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ) ?: '{}';
    }
}
