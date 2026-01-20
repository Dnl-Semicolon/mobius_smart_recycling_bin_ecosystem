<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    /** @use HasFactory<\Database\Factories\AddressFactory> */
    use HasFactory;

    protected $fillable = [
        'person_id',
        'line_1',
        'line_2',
        'city',
        'state',
        'postal_code',
    ];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
