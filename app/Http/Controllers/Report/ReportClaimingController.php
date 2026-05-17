<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Http\Requests\Report\ReportClaimingIndexRequest;
use Domain\Report\Models\AnalystReport;
use Domain\Report\Services\ReportClaimingService;
use Illuminate\Http\RedirectResponse;
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
     */
    public function index(ReportClaimingIndexRequest $request): View
    {
        $data = $this->claimingService->listingData((string) Auth::id(), $request->toDTO());

        return view('pages.laporan.index', [
            'items' => $data['items'],
            'status' => $data['status'],
            'counts' => $data['counts'],
        ]);
    }

    /**
     * Show report edit form and claim ownership when allowed.
     */
    public function isi(AnalystReport $report): View|RedirectResponse
    {
        $data = $this->claimingService->editViewData($report, (string) Auth::id());

        if (($data['forbidden'] ?? false) === true) {
            return redirect()
                ->route('laporan.index')
                ->with('error', (string) ($data['message'] ?? 'Akses ditolak.'));
        }

        unset($data['forbidden']);

        return view('pages.laporan.isi', $data);
    }
}
