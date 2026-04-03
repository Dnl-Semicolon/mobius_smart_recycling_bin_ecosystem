<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RoboflowService
{
    private const CLASS_MAP = [
        'cup' => 'paper_cup',
        'paper_cup' => 'paper_cup',
        'plastic_cup' => 'plastic_cup',
        'lid' => 'lid',
        'straw' => 'straw',
        'napkin' => 'napkin',
        'liquid_waste' => 'liquid_waste',
    ];

    public function detect(string $base64Image): ?array
    {
        try {
            $response = Http::timeout(15)
                ->withBody($base64Image, 'application/x-www-form-urlencoded')
                ->post(sprintf(
                    '%s/%s?api_key=%s',
                    config('services.roboflow.api_url'),
                    config('services.roboflow.model_id'),
                    config('services.roboflow.api_key'),
                ));

            if (! $response->successful()) {
                Log::warning('Roboflow API failed', ['status' => $response->status()]);

                return null;
            }

            $predictions = $response->json('predictions', []);

            if (empty($predictions)) {
                return null;
            }

            // Take highest confidence prediction
            $best = collect($predictions)->sortByDesc('confidence')->first();
            $rawClass = strtolower($best['class'] ?? '');
            $wasteType = self::CLASS_MAP[$rawClass] ?? 'paper_cup';

            return [
                'waste_type' => $wasteType,
                'confidence' => (int) round(($best['confidence'] ?? 0) * 100),
                'x' => $best['x'] ?? 0,
                'y' => $best['y'] ?? 0,
                'width' => $best['width'] ?? 0,
                'height' => $best['height'] ?? 0,
                'raw' => $best,
            ];
        } catch (\Exception $e) {
            Log::error('Roboflow detection failed', ['error' => $e->getMessage()]);

            return null;
        }
    }
}
