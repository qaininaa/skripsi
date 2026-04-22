<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search');
        $role = $request->input('role');

        $users = User::when($search, fn ($q) => $q->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%");
        }))
            ->when($role, fn ($q) => $q->where('role', $role))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        return view('pages.users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255', 'unique:users,username'],
            'role' => ['required', Rule::in(['super', 'admin', 'analis', 'supervisor', 'manajer'])],
            'password' => ['required', 'string', 'confirmed'],
        ]);

        if ($validated['role'] === 'manajer' && User::where('role', 'manajer')->exists()) {
            return back()->withInput()->withErrors([
                'role' => 'Sudah ada pengguna dengan role Manajer. Hanya boleh ada 1 Manajer.',
            ]);
        }

        // null forces user to change password on first login
        $validated['last_password_changed_at'] = null;

        $user = User::create($validated);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'create_user',
            'description' => 'Membuat pengguna baru: '.$user->name.' ('.$user->username.')',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'Pengguna baru berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        return view('pages.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'role' => ['required', Rule::in(['super', 'admin', 'analis', 'supervisor', 'manajer'])],
            'password' => ['nullable', 'string', 'confirmed'],
        ]);

        if ($validated['role'] === 'manajer' && $user->role !== 'manajer' && User::where('role', 'manajer')->exists()) {
            return back()->withInput()->withErrors([
                'role' => 'Sudah ada pengguna dengan role Manajer. Hanya boleh ada 1 Manajer.',
            ]);
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        } elseif ($user->role === 'super') {
            // Super admin tidak perlu dipaksa ganti password saat login
            $validated['last_password_changed_at'] = now();
        } else {
            // User lain dipaksa ganti password saat login berikutnya
            $validated['last_password_changed_at'] = null;
        }

        $user->update($validated);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'update_user',
            'description' => 'Memperbarui pengguna: '.$user->name.' ('.$user->username.')',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Opsional: cegah menghapus diri sendiri
        if (auth()->id() === $user->id) {
            return redirect()
                ->route('users.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $deletedUserInfo = $user->name.' ('.$user->username.')';

        $user->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete_user',
            'description' => 'Menghapus pengguna: '.$deletedUserInfo,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}
