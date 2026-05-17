<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\PasswordChangeRequest;
use Domain\User\Services\AuthService;
use Domain\User\Services\PasswordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
        private PasswordService $passwordService,
    ) {}

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $this->authService->authenticate($request->toDTO(), $request);
        $request->session()->regenerate();

        $user = $request->user();

        if ($this->authService->shouldRedirectToPasswordChange($user)) {
            return redirect()->route('password.change');
        }

        $routeName = $this->authService->resolveDashboardRouteName($user);
        if ($routeName !== null) {
            return redirect()->route($routeName);
        }

        return redirect('/');
    }

    public function showChangePassword(Request $request): View|RedirectResponse
    {
        if ($this->passwordService->shouldBypassForSuper($request->user())) {
            return redirect()->route('dashboard');
        }

        return view('auth.change-password');
    }

    public function updateChangePassword(PasswordChangeRequest $request): RedirectResponse
    {
        if ($this->passwordService->shouldBypassForSuper($request->user())) {
            return redirect()->route('dashboard');
        }

        $this->passwordService->changePassword(
            $request->user(),
            $request->toDTO(),
        );

        return redirect()->route('dashboard')
            ->with('success', 'Password berhasil diubah.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->authService->logout($request);

        return redirect()->route('login');
    }
}
