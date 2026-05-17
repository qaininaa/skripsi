<?php

namespace App\Domains\ReportType\Http\Controllers;

use App\Domains\ReportType\Http\Requests\SectionLocationRequest;
use App\Domains\ReportType\Models\ReportSection;
use Domain\Location\Models\Location;
use App\Domains\ReportType\Models\ReportType;
use App\Domains\ReportType\Services\SectionLocationService;
use App\Http\Controllers\Controller;

class ReportLocationController extends Controller
{
    public function __construct(private SectionLocationService $service) {}

    public function store(SectionLocationRequest $request, ReportType $reportType, ReportSection $section)
    {
        $result = $this->service->attach($section, $request->validated('location_id'));

        return redirect()
            ->route('report-types.show', $reportType)
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function destroy(ReportType $reportType, ReportSection $section, Location $location)
    {
        $this->service->detach($section, $location);

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Lokasi berhasil dihapus dari section.');
    }
}
