<?php

namespace App\Models\Example;

use Database\Factories\Example\PersonFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'birthday', 'phone'];

    protected function casts(): array
    {
        return [
            'birthday' => 'date',
        ];
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    protected static function newFactory(): PersonFactory
    {
        return PersonFactory::new();
    }
}
