<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use Billable, HasApiTokens, HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'bio',
        'avatar_path',
        'password',
        'roles',
        'points_balance',
        'current_streak',
        'longest_streak',
        'last_recycled_at',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'roles' => 'array',
            'last_recycled_at' => 'datetime',
            'points_balance' => 'integer',
            'current_streak' => 'integer',
            'longest_streak' => 'integer',
        ];
    }

    public function zones(): BelongsToMany
    {
        return $this->belongsToMany(Zone::class, 'zone_collector')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function collectionRoutes(): HasMany
    {
        return $this->hasMany(CollectionRoute::class, 'collector_id');
    }

    public function detectionEvents(): HasMany
    {
        return $this->hasMany(DetectionEvent::class);
    }

    public function recyclingTransactions(): HasMany
    {
        return $this->hasMany(RecyclingTransaction::class);
    }

    public function appNotifications(): HasMany
    {
        return $this->hasMany(AppNotification::class);
    }

    public function outlets(): BelongsToMany
    {
        return $this->belongsToMany(Outlet::class, 'outlet_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(Redemption::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function managedAgency(): HasOne
    {
        return $this->hasOne(CollectorAgency::class, 'user_id');
    }

    public function collectorAgencies(): BelongsToMany
    {
        return $this->belongsToMany(CollectorAgency::class, 'agency_collector')
            ->withPivot('status', 'invited_at', 'joined_at')
            ->withTimestamps();
    }

    /**
     * @return list<string>
     */
    public function getRolesArray(): array
    {
        $roles = $this->getAttribute('roles');

        if (is_array($roles) && $roles !== []) {
            return $roles;
        }

        return ['public_user'];
    }

    public function hasRole(UserRole $role): bool
    {
        return in_array($role->value, $this->getRolesArray(), true);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(UserRole::Admin);
    }

    public function isCollector(): bool
    {
        return $this->hasRole(UserRole::Collector);
    }

    public function isStoreOwner(): bool
    {
        return $this->hasRole(UserRole::StoreOwner);
    }

    public function isAgencyAdmin(): bool
    {
        return $this->hasRole(UserRole::AgencyAdmin);
    }

    public function addRole(UserRole $role): void
    {
        $roles = $this->getRolesArray();

        if (! in_array($role->value, $roles, true)) {
            $roles[] = $role->value;
            $this->roles = $roles;
            $this->save();
        }
    }

    public function removeRole(UserRole $role): void
    {
        $roles = array_values(array_filter(
            $this->getRolesArray(),
            fn (string $r) => $r !== $role->value,
        ));

        $this->roles = $roles;
        $this->save();
    }

    public function primaryRole(): UserRole
    {
        $roles = $this->getRolesArray();

        return UserRole::from($roles[0]);
    }
}
