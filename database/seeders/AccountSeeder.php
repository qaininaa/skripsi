<?php

namespace Database\Seeders;

use Domain\User\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

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
            'name' => 'Maya',
            'username' => 'adminqc',
            'role' => 'admin',
            'password' => Hash::make('admin123'),
            'last_password_changed_at' => $now,
        ]);
        User::create([
            'name' => 'Karina Ghaisani',
            'username' => 'analyst1',
            'role' => 'analyst',
            'password' => Hash::make('admin123'),
            'last_password_changed_at' => $now,
        ]);
        User::create([
            'name' => 'Farhanah Basri',
            'username' => 'analyst2',
            'role' => 'analyst',
            'password' => Hash::make('admin123'),
            'last_password_changed_at' => $now,
        ]);
        User::create([
            'name' => 'Sandiva Amalia',
            'username' => 'analyst3',
            'role' => 'analyst',
            'password' => Hash::make('admin123'),
            'last_password_changed_at' => $now,
        ]);
        User::create([
            'name' => 'Rafly Aziz',
            'username' => 'analyst4',
            'role' => 'analyst',
            'password' => Hash::make('admin123'),
            'last_password_changed_at' => $now,
        ]);
        User::create([
            'name' => 'Ananda Sadewa',
            'username' => 'analyst5',
            'role' => 'analyst',
            'password' => Hash::make('admin123'),
            'last_password_changed_at' => $now,
        ]);
        User::create([
            'name' => 'Saputra',
            'username' => 'analyst6',
            'role' => 'analyst',
            'password' => Hash::make('admin123'),
            'last_password_changed_at' => $now,
        ]);
        User::create([
            'name' => 'Dewi Ikha',
            'username' => 'manager',
            'role' => 'manager',
            'password' => Hash::make('admin123'),
            'last_password_changed_at' => $now,
        ]);
        User::create([
            'name' => 'Restu',
            'username' => 'supervisor1',
            'role' => 'supervisor',
            'password' => Hash::make('admin123'),
            'last_password_changed_at' => $now,
        ]);
    }
}
