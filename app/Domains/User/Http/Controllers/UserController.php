<?php

namespace App\Domains\User\Http\Controllers;

use App\Domains\User\DTOs\UserDTO;
use App\Domains\User\Http\Requests\CreateUserRequest;
use App\Domains\User\Models\User;
use App\Domains\User\Services\UserService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private UserService $service) {}

    public function index(Request $request): View
    {
        $search = $request->input('search');
        $role = $request->input('role');

        $users = $this->service->paginateForManagement($search, $role);

        return view('pages.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('pages.users.create');
    }

    public function store(CreateUserRequest $request): RedirectResponse
    {
        if ($request->input('role') === 'manajer' && $this->service->isManagerTaken()) {
            return back()->withInput()->withErrors([
                'role' => 'Sudah ada pengguna dengan role Manajer. Hanya boleh ada 1 Manajer.',
            ]);
        }

        $this->service->create(UserDTO::fromArray($request->validated()), $this->meta($request));

        return redirect()
            ->route('users.index')
            ->with('success', 'Pengguna baru berhasil dibuat. Password default.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $this->service->resetPassword($user, $this->meta($request));

        return redirect()
            ->route('users.index')
            ->with('success', "Password pengguna {$user->username} direset ke default");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()?->id === $user->id) {
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
            'user_id' => $request->user()?->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];
    }
}
