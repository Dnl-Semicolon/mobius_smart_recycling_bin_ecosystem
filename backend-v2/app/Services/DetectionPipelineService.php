<?php

namespace App\Services;

use App\Models\Bin;
use App\Models\BinSession;
use App\Models\Brand;
use App\Models\DetectionEvent;
use Illuminate\Support\Facades\Storage;

class DetectionPipelineService
{
    private const WEIGHT_GRAMS = [
        'paper_cup' => 15,
        'plastic_cup' => 10,
        'lid' => 5,
        'straw' => 2,
        'napkin' => 3,
        'liquid_waste' => 0,
    ];

    public function __construct(
        public RoboflowService $roboflow,
        public OpenAIBrandService $openai,
    ) {}

    public function processFrame(BinSession $session, string $base64Image, string $inputMethod): ?DetectionEvent
    {
        $detection = $this->roboflow->detect($base64Image);

        if (! $detection) {
            return null;
        }

        $brandId = null;
        $openaiResponse = null;

        if (in_array($detection['waste_type'], ['paper_cup', 'plastic_cup'])) {
            $brandSlug = $this->openai->detectBrand($base64Image);
            $openaiResponse = $brandSlug;

            if ($brandSlug && $brandSlug !== 'unknown') {
                $brandId = Brand::where('ai_slug', $brandSlug)->value('id');
            }
        }

        $imagePath = sprintf('detections/session_%d_event_%s.jpg', $session->id, uniqid());
        Storage::disk('public')->put($imagePath, base64_decode($base64Image));

        $event = DetectionEvent::create([
            'bin_session_id' => $session->id,
            'waste_type' => $detection['waste_type'],
            'input_method' => $inputMethod,
            'detected_brand_id' => $brandId,
            'confidence' => $detection['confidence'],
            'image_path' => $imagePath,
            'ai_output' => [
                'roboflow' => $detection['raw'],
                'openai_brand' => $openaiResponse,
            ],
        ]);

        $this->updateBinState($session->bin, $detection['waste_type']);

        return $event;
    }

    private function updateBinState(Bin $bin, string $wasteType): void
    {
        $addedWeight = self::WEIGHT_GRAMS[$wasteType] ?? 0;
        $newWeight = $bin->weight_grams + $addedWeight;
        $capacityGrams = $bin->capacity_liters * 50;
        $fillPercentage = min(100, (int) round(($newWeight / $capacityGrams) * 100));

        $sensorLevels = [
            'level_25' => $fillPercentage >= 25,
            'level_50' => $fillPercentage >= 50,
            'level_75' => $fillPercentage >= 75,
            'level_100' => $fillPercentage >= 100,
        ];

        $bin->update([
            'weight_grams' => $newWeight,
            'fill_level' => $fillPercentage,
            'sensor_levels' => $sensorLevels,
        ]);
    }
}
