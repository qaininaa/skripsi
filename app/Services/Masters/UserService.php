<?php

namespace App\Services\Masters;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class UserService
{
    public function create(array $validated, array $meta): User
    {
        $validated['last_password_changed_at'] = null; // paksa ganti password saat login pertama

        $user = User::create($validated);

        $this->auditLog('create_user', "Membuat pengguna baru: {$user->name} ({$user->username})", $meta);

        return $user;
    }

    public function update(User $user, array $validated, array $meta): User
    {
        if (empty($validated['password'])) {
            unset($validated['password']);
        } elseif ($user->role === 'super') {
            $validated['last_password_changed_at'] = now();
        } else {
            $validated['last_password_changed_at'] = null; // paksa ganti password saat login berikutnya
        }

        $user->update($validated);

        $this->auditLog('update_user', "Memperbarui pengguna: {$user->name} ({$user->username})", $meta);

        return $user;
    }

    public function delete(User $user, array $meta): void
    {
        $info = "{$user->name} ({$user->username})";

        $user->delete();

        $this->auditLog('delete_user', "Menghapus pengguna: {$info}", $meta);
    }

    public function isManajerTaken(?int $excludeUserId = null): bool
    {
        return User::where('role', 'manajer')
            ->when($excludeUserId, fn ($q) => $q->where('id', '!=', $excludeUserId))
            ->exists();
    }

    private function auditLog(string $action, string $description, array $meta): void
    {
        AuditLog::create([
            'user_id'     => $meta['user_id'] ?? null,
            'action'      => $action,
            'description' => $description,
            'ip_address'  => $meta['ip_address'] ?? null,
            'user_agent'  => $meta['user_agent'] ?? null,
        ]);
    }
}