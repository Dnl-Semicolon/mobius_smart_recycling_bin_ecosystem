<?php

namespace App\Observers;

use App\Models\DetectionEvent;
use App\Services\DetectionService;

class DetectionEventObserver
{
    public function __construct(private DetectionService $detectionService) {}

    /**
     * When a detection event is created with a waste_type, process it:
     * bump fill level, auto-create pickup if needed, award points.
     */
    public function created(DetectionEvent $detectionEvent): void
    {
        $this->detectionService->processDetection($detectionEvent);
    }
}
