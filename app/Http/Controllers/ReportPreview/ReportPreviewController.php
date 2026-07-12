<?php

namespace App\Http\Controllers\ReportPreview;

use App\Http\Controllers\Controller;
use App\Services\Reports\ReportViewService;
use Domain\Report\Models\Report;
use Illuminate\View\View;

/**
 * Controller for report preview page rendering across roles.
 */
class ReportPreviewController extends Controller
{
    public function __construct(private ReportViewService $viewService) {}

    /**
     * Show report preview in read-only mode.
     */
    public function show(Report $report): View
    {
        $this->viewService->loadRelations($report);

        $viewData = $this->viewService->buildViewData($report, false);

        $isAdminPreview = auth()->user()?->role === 'admin';

        return view('pages.reports.fill', array_merge(
            compact('report', 'isAdminPreview'),
            $viewData
        ));
    }
}
