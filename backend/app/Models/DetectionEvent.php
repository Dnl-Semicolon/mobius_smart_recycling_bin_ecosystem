<?php

namespace App\Models;

use App\Enums\WasteType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetectionEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'bin_session_id',
        'waste_type',
        'input_method',
        'detected_brand_id',
        'confidence',
        'image_path',
        'ai_output',
    ];

    protected function casts(): array
    {
        return [
            'waste_type' => WasteType::class,
            'confidence' => 'integer',
            'ai_output' => 'array',
        ];
    }

    public function binSession(): BelongsTo
    {
        return $this->belongsTo(BinSession::class);
    }

    public function detectedBrand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'detected_brand_id');
    }

    /**
     * Compat accessor: old schema had detected_at, new uses created_at.
     */
    public function getDetectedAtAttribute(): ?\Illuminate\Support\Carbon
    {
        return $this->created_at;
    }

    /**
     * Compat accessor: old views reference $detection->bin directly.
     * New schema goes through binSession.
     */
    public function getBinAttribute(): ?Bin
    {
        return $this->binSession?->bin;
    }

    /**
     * Weekly detection chart data.
     */
    public static function weeklyChart(?Bin $bin = null): array
    {
        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('D');
            $query = static::whereDate('created_at', $date);
            if ($bin) {
                $query->whereHas('binSession', fn ($q) => $q->where('bin_id', $bin->id));
            }
            $data[] = $query->count();
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
