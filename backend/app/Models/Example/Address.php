<?php

namespace App\Models\Example;

use Database\Factories\Example\AddressFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    /** @use HasFactory<\Database\Factories\Example\AddressFactory> */
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

    protected static function newFactory(): AddressFactory
    {
        return AddressFactory::new();
    }
}
