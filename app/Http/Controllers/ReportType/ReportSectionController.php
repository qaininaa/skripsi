<?php

namespace App\Http\Controllers\ReportType;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportType\ReportSectionStoreRequest;
use App\Http\Requests\ReportType\ReportSectionUpdateRequest;
use Domain\ReportType\Models\ReportSection;
use Domain\ReportType\Models\ReportType;
use Domain\ReportType\Services\ReportSectionService;
use Illuminate\Http\RedirectResponse;

/**
 * Controller for managing sections under a report type.
 */
class ReportSectionController extends Controller
{
    public function __construct(private ReportSectionService $sectionService) {}

    /**
     * Persist a new section in a report type.
     */
    public function store(ReportSectionStoreRequest $request, ReportType $reportType): RedirectResponse
    {
        $this->sectionService->createSection($reportType, $request->toDTO());

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Section berhasil ditambahkan.');
    }

    /**
     * Update an existing section in a report type.
     */
    public function update(
        ReportSectionUpdateRequest $request,
        ReportType $reportType,
        ReportSection $section,
    ): RedirectResponse {
        $this->sectionService->updateSection($section, $request->toDTO());

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Section berhasil diperbarui.');
    }

    /**
     * Delete a section.
     */
    public function destroy(ReportType $reportType, ReportSection $section): RedirectResponse
    {
        $this->sectionService->deleteSection($section);

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Section berhasil dihapus.');
    }
}
