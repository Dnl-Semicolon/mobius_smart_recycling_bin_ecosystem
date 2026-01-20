<?php

namespace App\Logging;

use App\Logging\Sampling\TailSampler;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class WideEventMiddleware
{
    private const EXCLUDED_PATHS = [
        'wide-events-viewer.html',
        'wide-events-log',
    ];

    public function __construct(
        private WideEvent $wideEvent,
        private TailSampler $sampler
    ) {}

    /**
     * Handle an incoming request.
     *
     * Captures request metadata and passes through to next handler.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldSkipRequest($request)) {
            return $next($request);
        }

        $this->wideEvent->captureRequest($request);

        $response = $next($request);

        $response->headers->set('X-Request-Id', $this->wideEvent->getRequestId());

        if ($traceId = $this->wideEvent->getTraceId()) {
            $response->headers->set('X-Trace-Id', $traceId);
        }

        return $response;
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     *
     * This is where we finalize the wide event and emit it exactly once.
     */
    public function terminate(Request $request, Response $response): void
    {
        if ($this->shouldSkipRequest($request)) {
            return;
        }

        if ($this->wideEvent->wasEmitted()) {
            return;
        }

        $this->wideEvent->captureResponse($response);
        $this->wideEvent->captureUser(auth()->user());
        $this->wideEvent->markEmitted();

        if ($this->sampler->shouldSample($this->wideEvent)) {
            Log::channel('wide-events')->info('request', $this->wideEvent->toArray());
        }
    }

    private function shouldSkipRequest(Request $request): bool
    {
        return in_array($request->path(), self::EXCLUDED_PATHS, true);
    }
}
