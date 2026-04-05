<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'role' => 'super',
            'password' => Hash::make('admin123'),
            'last_password_changed_at' => $now,
        ]);
        User::create([
            'name' => 'Admin QC',
            'username' => 'adminqc',
            'role' => 'admin',
            'password' => Hash::make('admin123'),
            'last_password_changed_at' => $now,
        ]);

        // 4 Akun Analis
        User::create([
            'name' => 'Analis 1',
            'username' => 'analis1',
            'role' => 'analis',
            'password' => Hash::make('admin123'),
            'last_password_changed_at' => $now,
        ]);
        User::create([
            'name' => 'Analis 2',
            'username' => 'analis2',
            'role' => 'analis',
            'password' => Hash::make('admin123'),
            'last_password_changed_at' => $now,
        ]);
        User::create([
            'name' => 'Analis 3',
            'username' => 'analis3',
            'role' => 'analis',
            'password' => Hash::make('admin123'),
            'last_password_changed_at' => $now,
        ]);
        User::create([
            'name' => 'Analis 4',
            'username' => 'analis4',
            'role' => 'analis',
            'password' => Hash::make('admin123'),
            'last_password_changed_at' => $now,
        ]);
        User::create([
            'name' => 'Manajer',
            'username' => 'manajer',
            'role' => 'manajer',
            'password' => Hash::make('admin123'),
            'last_password_changed_at' => $now,
        ]);
        User::create([
            'name' => 'Supervisor 1',
            'username' => 'supervisor1',
            'role' => 'supervisor',
            'password' => Hash::make('admin123'),
            'last_password_changed_at' => $now,
        ]);
        User::create([
            'name' => 'Supervisor 2',
            'username' => 'supervisor2',
            'role' => 'supervisor',
            'password' => Hash::make('admin123'),
            'last_password_changed_at' => $now,
        ]);
    }
}
