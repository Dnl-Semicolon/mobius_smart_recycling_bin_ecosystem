<?php

namespace App\Logging\Sampling;

use App\Logging\WideEvent;

class TailSampler
{
    /**
     * Determine if the event should be sampled (logged).
     *
     * Tail sampling keeps 100% of actionable events (errors, slow requests)
     * and samples remaining traffic at the configured base rate.
     */
    public function shouldSample(WideEvent $event): bool
    {
        if (! config('wide-events.sampling.enabled', true)) {
            return false;
        }

        // RULE 1: Always keep errors (100%)
        if ($event->hasError()) {
            return true;
        }

        $statusCode = $event->getStatusCode();

        if ($statusCode !== null && $statusCode >= 500) {
            return true;
        }

        // RULE 2: Always keep slow requests (above p99 threshold)
        $threshold = config('wide-events.sampling.p99_threshold_ms', 2000);
        if ($event->getDurationMs() > $threshold) {
            return true;
        }

        // RULE 3: Keep client errors (4xx) if configured
        if (config('wide-events.sampling.keep_client_errors', true)) {
            if ($statusCode !== null && $statusCode >= 400 && $statusCode < 500) {
                return true;
            }
        }

        // RULE 4: Random sample remaining traffic
        $rate = config('wide-events.sampling.base_rate', 1.0);

        return mt_rand() / mt_getrandmax() < $rate;
    }
}
