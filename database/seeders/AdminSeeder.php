<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin',
            'username' => 'superAdmin',
            'email' => 'admin@company.com',
            'password' => Hash::make('admin123'),
        ]);
    }
}
