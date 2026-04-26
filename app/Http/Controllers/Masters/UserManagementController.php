<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\Users\StoreUserRequest;
use App\Http\Requests\Masters\Users\UpdateUserRequest;
use App\Models\User;
use App\Services\Masters\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function __construct(private UserService $service) {}

    public function index(Request $request): View
    {
        $search = $request->input('search');
        $role   = $request->input('role');

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

    public function create(): View
    {
        return view('pages.users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        if ($request->input('role') === 'manajer' && $this->service->isManajerTaken()) {
            return back()->withInput()->withErrors([
                'role' => 'Sudah ada pengguna dengan role Manajer. Hanya boleh ada 1 Manajer.',
            ]);
        }

        $this->service->create($request->validated(), $this->meta($request));

        return redirect()
            ->route('users.index')
            ->with('success', 'Pengguna baru berhasil dibuat.');
    }

    public function edit(User $user): View
    {
        return view('pages.users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $roleChanged = $request->input('role') === 'manajer' && $user->role !== 'manajer';

        if ($roleChanged && $this->service->isManajerTaken($user->id)) {
            return back()->withInput()->withErrors([
                'role' => 'Sudah ada pengguna dengan role Manajer. Hanya boleh ada 1 Manajer.',
            ]);
        }

        $this->service->update($user, $request->validated(), $this->meta($request));

        return redirect()
            ->route('users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            return redirect()
                ->route('users.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $this->service->delete($user, $this->meta($request));

        return redirect()
            ->route('users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    private function meta(Request $request): array
    {
        return [
            'user_id'    => $request->user()?->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];
    }
}