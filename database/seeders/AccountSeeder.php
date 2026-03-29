<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'role' => 'super admin',
            'email' => 'admin@company.com',
            'password' => Hash::make('admin123'),
        ]);
        User::create([
            'name' => 'Admin QC',
            'username' => 'adminqc',
            'role' => 'admin-qc',
            'email' => 'adminqc@company.com',
            'password' => Hash::make('admin123'),
        ]);

        // 4 Akun Analis
        User::create([
            'name' => 'Analis 1',
            'username' => 'analis1',
            'role' => 'analis',
            'email' => 'analis1@company.com',
            'password' => Hash::make('admin123'),
        ]);
        User::create([
            'name' => 'Analis 2',
            'username' => 'analis2',
            'role' => 'analis',
            'email' => 'analis2@company.com',
            'password' => Hash::make('admin123'),
        ]);
        User::create([
            'name' => 'Analis 3',
            'username' => 'analis3',
            'role' => 'analis',
            'email' => 'analis3@company.com',
            'password' => Hash::make('admin123'),
        ]);
        User::create([
            'name' => 'Analis 4',
            'username' => 'analis4',
            'role' => 'analis',
            'email' => 'analis4@company.com',
            'password' => Hash::make('admin123'),
        ]);
        User::create([
            'name' => 'Manajer',
            'username' => 'manajer',
            'role' => 'manajer',
            'email' => 'manajer@company.com',
            'password' => Hash::make('admin123'),
        ]);
        User::create([
            'name' => 'Supervisor 1',
            'username' => 'supervisor1',
            'role' => 'supervisor',
            'email' => 'supervisor1@company.com',
            'password' => Hash::make('admin123'),
        ]);
        User::create([
            'name' => 'Supervisor 2',
            'username' => 'supervisor2',
            'role' => 'supervisor',
            'email' => 'supervisor2@company.com',
            'password' => Hash::make('admin123'),
        ]);
    }
}
