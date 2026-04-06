<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'selected_plan_id', 'company_name', 'contact_name', 'contact_email',
    'contact_phone', 'type', 'description', 'status', 'admin_notes',
    'reviewed_by', 'reviewed_at', 'phone_verified_at', 'email_verified_at',
    'email_verification_token',
])]
class RegistrationRequest extends Model
{
    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'email_verified_at' => 'datetime',
        ];
    }

    public function selectedPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'selected_plan_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
