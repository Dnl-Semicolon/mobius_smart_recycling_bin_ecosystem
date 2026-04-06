<?php

namespace App\Rules;

use App\Support\PhoneNormalizer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class UniqueNormalizedPhone implements ValidationRule
{
    /**
     * @param  (Closure(Builder): void)|null  $scope
     */
    public function __construct(
        private readonly string $table,
        private readonly string $column = 'phone',
        private readonly ?int $ignoreId = null,
        private readonly string $idColumn = 'id',
        private readonly ?Closure $scope = null,
        private readonly string $message = 'This phone number has already been used.',
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $normalized = PhoneNormalizer::normalize(is_string($value) ? $value : null);

        if ($normalized === null) {
            return;
        }

        $query = DB::table($this->table)->where($this->column, $normalized);

        if ($this->ignoreId !== null) {
            $query->where($this->idColumn, '!=', $this->ignoreId);
        }

        if ($this->scope !== null) {
            ($this->scope)($query);
        }

        if ($query->exists()) {
            $fail($this->message);
        }
    }
}
