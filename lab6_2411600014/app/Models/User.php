<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN   = 'admin';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_STAFF   = 'staff';

    /**
     * The attributes that are mass assignable.
     *
     * NOTE: `role` is deliberately NOT fillable, so a crafted registration
     * request can never promote itself to admin. Set it with forceFill().
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** Stock movements this user has recorded (accountability tracking). */
    public function inventoryTransactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /** Admins and managers may create/edit products and adjust stock. */
    public function canManageStock(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_MANAGER], true);
    }

    /** Recipients of low stock alerts. */
    public function scopeManagers(Builder $query): Builder
    {
        return $query->whereIn('role', [self::ROLE_ADMIN, self::ROLE_MANAGER]);
    }
}
