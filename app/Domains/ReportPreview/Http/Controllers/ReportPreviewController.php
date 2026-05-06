<?php

namespace App\Domains\ReportPreview\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Services\Reports\ReportViewService;
use Illuminate\View\View;

/**
 * Controller for report preview page rendering across roles.
 */
class ReportPreviewController extends Controller
{
    public function __construct(private ReportViewService $viewService) {}

    /**
     * Show report preview in read-only mode.
     *
     * @param Report $report
     * @return View
     */
    public function show(Report $report): View
    {
        $this->viewService->loadRelations($report);

        $viewData = $this->viewService->buildViewData($report, false);

        $isAdminPreview = auth()->user()?->role === 'admin';

        return view('pages.laporan.isi', array_merge(
            compact('report', 'isAdminPreview'),
            $viewData
        ));
    }
}