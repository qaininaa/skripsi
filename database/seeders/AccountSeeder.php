<?php

namespace Database\Seeders;

use App\Models\User;
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
            'username' => 'analis1',
            'role' => 'analis',
            'password' => Hash::make('admin123'),
            'last_password_changed_at' => $now,
        ]);
        User::create([
            'name' => 'Farhanah Basri',
            'username' => 'analis2',
            'role' => 'analis',
            'password' => Hash::make('admin123'),
            'last_password_changed_at' => $now,
        ]);
        User::create([
            'name' => 'Sandiva Amalia',
            'username' => 'analis3',
            'role' => 'analis',
            'password' => Hash::make('admin123'),
            'last_password_changed_at' => $now,
        ]);
        User::create([
            'name' => 'Rafly Aziz',
            'username' => 'analis4',
            'role' => 'analis',
            'password' => Hash::make('admin123'),
            'last_password_changed_at' => $now,
        ]);
        User::create([
            'name' => 'Ananda Sadewa',
            'username' => 'analis5',
            'role' => 'analis',
            'password' => Hash::make('admin123'),
            'last_password_changed_at' => $now,
        ]);
        User::create([
            'name' => 'Saputra',
            'username' => 'analis6',
            'role' => 'analis',
            'password' => Hash::make('admin123'),
            'last_password_changed_at' => $now,
        ]);
        User::create([
            'name' => 'Dewi Ikha',
            'username' => 'manajer',
            'role' => 'manajer',
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
