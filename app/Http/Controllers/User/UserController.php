<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserIndexRequest;
use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserUpdateRequest;
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
            ->with('success', 'Pengguna baru berhasil dibuat.');
    }

    public function edit(User $user): View
    {
        return view('pages.users.edit', compact('user'));
    }

    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {
        if ($request->input('role') === 'manajer' && $this->userService->isManagerTaken($user->id)) {
            return back()->withInput()->withErrors([
                'role' => 'Sudah ada pengguna dengan role Manajer. Hanya boleh ada 1 Manajer.',
            ]);
        }

        $dto = $request->toDTO();
        $this->userService->updateUser($user, $dto, $this->meta($request));

        $message = $dto->hasPasswordReset()
            ? "Pengguna {$user->username} berhasil diperbarui dan password direset."
            : "Pengguna {$user->username} berhasil diperbarui.";

        return redirect()
            ->route('users.index')
            ->with('success', $message);
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
