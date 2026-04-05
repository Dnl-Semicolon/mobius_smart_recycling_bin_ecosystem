<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password', 'phone', 'profile_photo_path', 'organization_id', 'roles'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use Billable, HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'roles' => 'array',
            'last_recycled_at' => 'datetime',
            'points_balance' => 'integer',
            'current_streak' => 'integer',
            'longest_streak' => 'integer',
        ];
    }

    public function getRolesArray(): array
    {
        $roles = $this->getAttribute('roles');

        if (is_array($roles) && $roles !== []) {
            return $roles;
        }

        return [];
    }

    public function hasRole(UserRole $role): bool
    {
        return in_array($role->value, $this->getRolesArray(), true);
    }

    public function primaryRole(): UserRole
    {
        $roles = $this->getRolesArray();

        return UserRole::from($roles[0]);
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

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
