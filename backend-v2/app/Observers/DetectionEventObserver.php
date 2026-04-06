<?php

namespace App\Observers;

use App\Models\DetectionEvent;
use App\Services\DetectionService;

class DetectionEventObserver
{
    public function __construct(private DetectionService $detectionService) {}

    public function created(DetectionEvent $detectionEvent): void
    {
        $this->detectionService->processDetection($detectionEvent);
    }
}
