<?php

namespace App\Models;

use App\Enums\PickupStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PickupRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'bin_id',
        'request_type',
        'requested_by',
        'reason',
        'status',
        'assigned_to',
        'assigned_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PickupStatus::class,
            'assigned_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function bin(): BelongsTo
    {
        return $this->belongsTo(Bin::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Alias for assignedTo — views reference $pickupRequest->claimedBy.
     */
    public function claimedBy(): BelongsTo
    {
        return $this->assignedTo();
    }

    /**
     * Map assigned_at as claimed_at for views.
     */
    protected function claimedAt(): Attribute
    {
        return Attribute::get(fn () => $this->assigned_at);
    }

    public function routeStops(): HasMany
    {
        return $this->hasMany(RouteStop::class);
    }

    public function isPending(): bool
    {
        return $this->status === PickupStatus::Pending;
    }

    public function isClaimed(): bool
    {
        return $this->status === PickupStatus::Claimed;
    }

    /**
     * Cancel a pending or claimed pickup request.
     */
    public function cancel(): bool
    {
        if (! $this->isPending() && ! $this->isClaimed()) {
            return false;
        }

        $this->update([
            'status' => PickupStatus::Cancelled,
            'assigned_to' => null,
            'assigned_at' => null,
        ]);

        return true;
    }

    /**
     * Return a claimed pickup back to the pending queue.
     */
    public function unclaim(): bool
    {
        if (! $this->isClaimed()) {
            return false;
        }

        $this->update([
            'status' => PickupStatus::Pending,
            'assigned_to' => null,
            'assigned_at' => null,
        ]);

        return true;
    }
}
