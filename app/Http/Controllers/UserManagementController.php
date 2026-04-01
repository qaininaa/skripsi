<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(): View
    {
        $users = User::latest()->paginate(10);

        return view('dashboard.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        return view('dashboard.users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255', 'unique:users,username'],
            'role'     => ['required', Rule::in(['super admin', 'admin-qc', 'analis', 'supervisor', 'manajer'])],
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
            'description' => 'Membuat pengguna baru: ' . $user->name . ' (' . $user->username . ')',
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
        return view('dashboard.users.edit', compact('user'));
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
            'role'     => ['required', Rule::in(['super admin', 'admin-qc', 'analis', 'supervisor', 'manajer'])],
            'password' => ['nullable', 'string', 'confirmed'],
        ]);

        if ($validated['role'] === 'manajer' && $user->role !== 'manajer' && User::where('role', 'manajer')->exists()) {
            return back()->withInput()->withErrors([
                'role' => 'Sudah ada pengguna dengan role Manajer. Hanya boleh ada 1 Manajer.',
            ]);
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            // Set null so user is forced to change password on next login
            $validated['last_password_changed_at'] = null;
        }

        $user->update($validated);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'update_user',
            'description' => 'Memperbarui pengguna: ' . $user->name . ' (' . $user->username . ')',
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

        $deletedUserInfo = $user->name . ' (' . $user->username . ')';

        $user->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'delete_user',
            'description' => 'Menghapus pengguna: ' . $deletedUserInfo,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()
            ->route('users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}
