<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function index()
    {
        $settings = [
            'password_expiration_days' => (int) SystemSetting::getValue('password_expiration_days', 90),
            'password_history_count'   => (int) SystemSetting::getValue('password_history_count', 3),
        ];

        return view('dashboard.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'password_expiration_days' => ['required', 'integer', 'min:1', 'max:365'],
            'password_history_count'   => ['required', 'integer', 'min:1', 'max:24'],
        ]);

        SystemSetting::setValue('password_expiration_days', (string) $validated['password_expiration_days']);
        SystemSetting::setValue('password_history_count',   (string) $validated['password_history_count']);

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
