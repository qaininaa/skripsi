<?php

namespace Domain\User\Models;

use Domain\PasswordPolicy\Models\PasswordSetting;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * User model.
 *
 * Represents an application user with role-based access.
 */
class User extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'role',
        'password',
        'last_password_changed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'last_password_changed_at' => 'datetime',
        ];
    }

    public function passwordHistories()
    {
        return $this->hasMany(PasswordHistory::class)->latest();
    }

    /**
     * Determine if user password has expired based on policy.
     */
    public function isPasswordExpired(): bool
    {
        $expirationDays = (int) PasswordSetting::getValue('password_expiration_days', 90);

        if (! $this->last_password_changed_at) {
            return true;
        }

        return $this->last_password_changed_at->addDays($expirationDays)->isPast();
    }

    /**
     * Whether user must change password before continuing.
     */
    public function mustChangePassword(): bool
    {
        if ($this->role === 'super') {
            return false;
        }

        return $this->isPasswordExpired();
    }
}
