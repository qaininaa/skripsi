<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        SystemSetting::setValue('password_expiration_days', '90');
        SystemSetting::setValue('password_history_count', '3');
    }
}
