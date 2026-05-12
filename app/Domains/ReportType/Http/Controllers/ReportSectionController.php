<?php

namespace App\Domains\ReportType\Http\Controllers;

use App\Domains\ReportType\Http\Requests\ReportSection\StoreReportSectionRequest;
use App\Domains\ReportType\Http\Requests\ReportSection\UpdateReportSectionRequest;
use App\Domains\ReportType\Models\ReportSection;
use App\Domains\ReportType\Models\ReportType;
use App\Domains\ReportType\Services\SectionService;
use App\Http\Controllers\Controller;

class ReportSectionController extends Controller
{
    public function __construct(private SectionService $service) {}

    public function store(StoreReportSectionRequest $request, ReportType $reportType)
    {
        $this->service->create(
            $reportType,
            $request->validated(),
            $request->boolean('has_machine_setup')
        );

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Section berhasil ditambahkan.');
    }

    public function update(UpdateReportSectionRequest $request, ReportType $reportType, ReportSection $section)
    {
        $this->service->update(
            $section,
            $request->validated(),
            $request->boolean('has_machine_setup')
        );

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Section berhasil diperbarui.');
    }

    public function destroy(ReportType $reportType, ReportSection $section)
    {
        $this->service->delete($section);

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Section berhasil dihapus.');
    }
}
