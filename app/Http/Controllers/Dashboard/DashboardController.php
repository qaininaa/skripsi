<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use InvalidArgumentException;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    public function index(): View
    {
        $user = Auth::user();

        try {
            $view = $this->dashboardService->resolveViewByRole($user?->role);
        } catch (InvalidArgumentException) {
            abort(403, 'Role tidak memiliki halaman dashboard.');
        }

        return view($view, $this->dashboardService->buildViewData($user));
    }
}
