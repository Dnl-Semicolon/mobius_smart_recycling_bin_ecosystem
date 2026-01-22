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
        'bin_id',
        'waste_type',
        'confidence',
        'image_path',
        'detected_at',
    ];

    protected function casts(): array
    {
        return [
            'waste_type' => WasteType::class,
            'confidence' => 'integer',
            'detected_at' => 'datetime',
        ];
    }

    public function bin(): BelongsTo
    {
        return $this->belongsTo(Bin::class);
    }
}
