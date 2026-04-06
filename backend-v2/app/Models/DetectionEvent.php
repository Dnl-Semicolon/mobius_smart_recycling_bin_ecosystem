<?php

namespace App\Models;

use App\Enums\WasteType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'bin_session_id', 'waste_type', 'input_method', 'detected_brand_id',
    'confidence', 'image_path', 'ai_output',
])]
class DetectionEvent extends Model
{
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
     * Convenience accessor: get the bin through the session.
     */
    public function getBinAttribute(): ?Bin
    {
        return $this->binSession?->bin;
    }

    /**
     * Convenience accessor: get the user_id through the session.
     */
    public function getUserIdAttribute(): ?int
    {
        return $this->binSession?->user_id;
    }

    /**
     * Convenience accessor: get the user through the session.
     */
    public function getUserAttribute(): ?User
    {
        return $this->binSession?->user;
    }
}
