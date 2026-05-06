<?php

namespace App\Http\Controllers;

use App\Domains\Auth\Models\PasswordSetting;
use Illuminate\Http\Request;

class PasswordSettingController extends Controller
{
    public function index()
    {
        $settings = [
            'password_expiration_days' => (int) PasswordSetting::getValue('password_expiration_days', 90),
            'password_history_count' => (int) PasswordSetting::getValue('password_history_count', 3),
        ];

        return view('pages.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'password_expiration_days' => ['required', 'integer', 'min:1', 'max:365'],
            'password_history_count' => ['required', 'integer', 'min:1', 'max:24'],
        ]);

        PasswordSetting::setValue('password_expiration_days', (string) $validated['password_expiration_days']);
        PasswordSetting::setValue('password_history_count', (string) $validated['password_history_count']);

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
