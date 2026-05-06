<?php

namespace App\Domains\AnalystReport\ReportClaiming\Http\Controllers;

use App\Domains\AnalystReport\Models\AnalystReport;
use App\Domains\AnalystReport\ReportClaiming\DTOs\ReportClaimingFilterDTO;
use App\Domains\AnalystReport\ReportClaiming\Services\ReportClaimingService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Controller for analyst report claiming flow.
 */
class ReportClaimingController extends Controller
{
    public function __construct(private ReportClaimingService $claimingService) {}

    /**
     * Show analyst report listing.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $filter = ReportClaimingFilterDTO::fromRequest($request);

        $data = $this->claimingService->listingData((string) Auth::id(), $filter);

        return view('pages.laporan.index', [
            'items' => $data['items'],
            'status' => $data['status'],
            'counts' => $data['counts'],
        ]);
    }

    /**
     * Show report edit form and claim ownership when allowed.
     *
     * @param AnalystReport $report
     * @return View|RedirectResponse
     */
    public function isi(AnalystReport $report): View|RedirectResponse
    {
        $data = $this->claimingService->editViewData($report, (string) Auth::id());

        if (($data['forbidden'] ?? false) === true) {
            return redirect()->route('laporan.index')
                ->with('error', (string) ($data['message'] ?? 'Akses ditolak.'));
        }

        unset($data['forbidden']);

        return view('pages.laporan.isi', $data);
    }
}
