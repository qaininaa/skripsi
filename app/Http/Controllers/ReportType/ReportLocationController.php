<?php

namespace App\Http\Controllers\ReportType;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportType\SectionLocationStoreRequest;
use Domain\Location\Models\Location;
use Domain\ReportType\Models\ReportSection;
use Domain\ReportType\Models\ReportType;
use Domain\ReportType\Services\SectionLocationService;
use Illuminate\Http\RedirectResponse;

/**
 * Controller for managing location assignments under report sections.
 */
class ReportLocationController extends Controller
{
    public function __construct(private SectionLocationService $sectionLocationService) {}

    /**
     * Attach a location to a report section from validated DTO.
     */
    public function store(
        SectionLocationStoreRequest $request,
        ReportType $reportType,
        ReportSection $section,
    ): RedirectResponse {
        $result = $this->sectionLocationService->attach($section, $request->toDTO());

        return redirect()
            ->route('report-types.show', $reportType)
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Detach a location from a report section.
     */
    public function destroy(
        ReportType $reportType,
        ReportSection $section,
        Location $location,
    ): RedirectResponse {
        $this->sectionLocationService->detach($section, $location);

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Lokasi berhasil dihapus dari section.');
    }
}
