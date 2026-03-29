<?php

namespace App\Models;

use App\Enums\WasteType;
use App\Observers\DetectionEventObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[ObservedBy(DetectionEventObserver::class)]
class DetectionEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'bin_id',
        'user_id',
        'waste_type',
        'confidence',
        'weight_g',
        'detected_brand_id',
        'image_path',
        'feedback_accurate',
        'detected_at',
    ];

    protected function casts(): array
    {
        return [
            'waste_type' => WasteType::class,
            'confidence' => 'integer',
            'weight_g' => 'integer',
            'detected_at' => 'datetime',
            'feedback_accurate' => 'boolean',
        ];
    }

    public function bin(): BelongsTo
    {
        return $this->belongsTo(Bin::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detectedBrand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'detected_brand_id');
    }

    /**
     * @return array{labels: list<string>, data: list<int>}
     */
    public static function weeklyChart(?Bin $bin = null): array
    {
        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('D');
            $query = $bin
                ? $bin->detectionEvents()->whereDate('detected_at', $date)
                : static::whereDate('detected_at', $date);
            $data[] = $query->count();
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
