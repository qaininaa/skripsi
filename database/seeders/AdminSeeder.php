<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
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
    }
}
