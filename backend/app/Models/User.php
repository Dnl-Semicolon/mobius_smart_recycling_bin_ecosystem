<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'name',
        'email',
        'password',
        'phone',
        'phone_verified_at',
        'profile_photo_path',
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
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'roles' => 'array',
            'last_recycled_at' => 'datetime',
            'points_balance' => 'integer',
            'current_streak' => 'integer',
            'longest_streak' => 'integer',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function outlets(): HasMany
    {
        return $this->hasMany(Outlet::class);
    }

    public function binSessions(): HasMany
    {
        return $this->hasMany(BinSession::class);
    }

    public function recyclingTransactions(): HasMany
    {
        return $this->hasMany(RecyclingTransaction::class);
    }

    public function voucherClaims(): HasMany
    {
        return $this->hasMany(VoucherClaim::class);
    }

    public function collectionRoutes(): HasMany
    {
        return $this->hasMany(CollectionRoute::class, 'collector_id');
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

    public function isBrandOwner(): bool
    {
        return $this->hasRole(UserRole::BrandOwner);
    }

    public function isStoreOwner(): bool
    {
        return $this->hasRole(UserRole::StoreOwner);
    }

    public function isCollector(): bool
    {
        return $this->hasRole(UserRole::Collector);
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
