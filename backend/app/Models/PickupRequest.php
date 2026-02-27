<?php

namespace App\Models;

use App\Enums\PickupStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class PickupRequest extends Model
{
    use HasFactory;

    public const COMPLETE_RESULT_COMPLETED = 'completed';

    public const COMPLETE_RESULT_FORBIDDEN = 'forbidden';

    public const COMPLETE_RESULT_INVALID_STATE = 'invalid_state';

    protected $fillable = [
        'bin_id',
        'status',
        'claimed_by',
        'claimed_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PickupStatus::class,
            'claimed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function bin(): BelongsTo
    {
        return $this->belongsTo(Bin::class);
    }

    public function claimedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }

    /**
     * Active means pending or claimed (not yet completed).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [PickupStatus::Pending, PickupStatus::Claimed]);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', PickupStatus::Pending);
    }

    public function scopeClaimed(Builder $query): Builder
    {
        return $query->where('status', PickupStatus::Claimed);
    }

    public function isPending(): bool
    {
        return $this->status === PickupStatus::Pending;
    }

    public function isClaimed(): bool
    {
        return $this->status === PickupStatus::Claimed;
    }

    public function isCompleted(): bool
    {
        return $this->status === PickupStatus::Completed;
    }

    public function isCancelled(): bool
    {
        return $this->status === PickupStatus::Cancelled;
    }

    /**
     * Cancel a pending or claimed pickup request.
     */
    public function cancel(): bool
    {
        if ($this->isCompleted() || $this->isCancelled()) {
            return false;
        }

        $this->update(['status' => PickupStatus::Cancelled]);

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
            'claimed_by' => null,
            'claimed_at' => null,
        ]);

        return true;
    }

    public function claimByCollector(int $collectorId): bool
    {
        $updated = self::query()
            ->whereKey($this->getKey())
            ->where('status', PickupStatus::Pending)
            ->update([
                'status' => PickupStatus::Claimed,
                'claimed_by' => $collectorId,
                'claimed_at' => now(),
            ]);

        $this->refresh();

        return $updated === 1;
    }

    /**
     * @return self::COMPLETE_RESULT_*
     */
    public function completeByCollector(int $collectorId): string
    {
        $result = DB::transaction(function () use ($collectorId): string {
            $updated = self::query()
                ->whereKey($this->getKey())
                ->where('status', PickupStatus::Claimed)
                ->where('claimed_by', $collectorId)
                ->update([
                    'status' => PickupStatus::Completed,
                    'completed_at' => now(),
                ]);

            if ($updated === 1) {
                Bin::query()
                    ->whereKey($this->bin_id)
                    ->update(['fill_level' => 0]);

                return self::COMPLETE_RESULT_COMPLETED;
            }

            $fresh = self::query()->find($this->getKey());

            if ($fresh && $fresh->isClaimed() && $fresh->claimed_by !== $collectorId) {
                return self::COMPLETE_RESULT_FORBIDDEN;
            }

            return self::COMPLETE_RESULT_INVALID_STATE;
        });

        $this->refresh();

        return $result;
    }
}
