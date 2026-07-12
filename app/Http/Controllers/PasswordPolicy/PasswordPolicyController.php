<?php

namespace App\Http\Controllers\PasswordPolicy;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordPolicy\PasswordPolicyUpdateRequest;
use Domain\PasswordPolicy\Services\PasswordPolicyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PasswordPolicyController extends Controller
{
    public function __construct(private PasswordPolicyService $passwordPolicyService) {}

    public function index(): View
    {
        $settings = $this->passwordPolicyService->getSettings();

        return view('pages.settings.index', compact('settings'));
    }

    public function update(PasswordPolicyUpdateRequest $request): RedirectResponse
    {
        $this->passwordPolicyService->updateSettings($request->toDTO());

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
