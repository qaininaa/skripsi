<?php

namespace Database\Seeders;

use App\Models\PasswordSetting;
use Illuminate\Database\Seeder;

class PasswordSettingSeeder extends Seeder
{
    public function run(): void
    {
        PasswordSetting::setValue('password_expiration_days', '90');
        PasswordSetting::setValue('password_history_count', '3');
    }
}
