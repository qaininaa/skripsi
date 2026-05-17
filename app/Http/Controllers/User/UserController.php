<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserIndexRequest;
use App\Http\Requests\User\UserStoreRequest;
use Domain\User\Models\User;
use Domain\User\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private UserService $userService) {}

    public function index(UserIndexRequest $request): View
    {
        $users = $this->userService->getDataUsers($request->toDTO());

        return view('pages.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('pages.users.create');
    }

    public function store(UserStoreRequest $request): RedirectResponse
    {
        if ($request->input('role') === 'manajer' && $this->userService->isManagerTaken()) {
            return back()->withInput()->withErrors([
                'role' => 'Sudah ada pengguna dengan role Manajer. Hanya boleh ada 1 Manajer.',
            ]);
        }

        $this->userService->createUser($request->toDTO(), $this->meta($request));

        return redirect()
            ->route('users.index')
            ->with('success', 'Pengguna baru berhasil dibuat. Password default.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $this->userService->resetPassword($user, $this->meta($request));

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

        $this->userService->deleteUser($user, $this->meta($request));

        return redirect()
            ->route('users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    /**
     * @return array{user_id: string|null, ip_address: string|null, user_agent: string|null}
     */
    private function meta(Request $request): array
    {
        return [
            'user_id' => $request->user()?->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];
    }
}
