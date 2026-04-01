<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

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

    public function isPasswordExpired(): bool
    {
        $expirationDays = (int) SystemSetting::getValue('password_expiration_days', 90);

        if (!$this->last_password_changed_at) {
            return true;
        }

        return $this->last_password_changed_at->addDays($expirationDays)->isPast();
    }

    public function mustChangePassword(): bool
    {
        if ($this->role === 'super admin') {
            return false;
        }

        return $this->isPasswordExpired();
    }
}
