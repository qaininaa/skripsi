<?php

namespace App\Http\Controllers;

use App\Domains\Auth\DTOs\PasswordChangeDTO;
use App\Domains\Auth\Http\Requests\LoginRequest;
use App\Domains\Auth\Services\AuthService;
use App\Domains\Auth\Services\PasswordService;
use App\Http\Controllers\Controller;
use App\Rules\PasswordComplexity;
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
        $dto = $request->toDTO();
        $this->authService->authenticate($dto, $request);
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

    public function updateChangePassword(Request $request): RedirectResponse
    {
        if ($this->passwordService->shouldBypassForSuper($request->user())) {
            return redirect()->route('dashboard');
        }

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', new PasswordComplexity],
        ]);

        $dto = PasswordChangeDTO::fromArray($validated);

        $this->passwordService->changePassword(
            $request->user(),
            $dto
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
