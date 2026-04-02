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
}
