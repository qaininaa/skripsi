<?php

namespace App\View\Composers;

use App\Services\SidebarService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Sidebar view composer.
 *
 * Injects role-aware navigation data ($sidebarData) into the sidebar
 * blade component for the authenticated user.
 */
class SidebarComposer
{
    public function __construct(
        protected SidebarService $sidebarService
    ) {
    }

    public function compose(View $view): void
    {
        $view->with('sidebarData', $this->sidebarService->buildForUser(Auth::user()));
    }
}
