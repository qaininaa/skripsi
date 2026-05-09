<?php

namespace App\Domains\ReportType\Http\Controllers;

use App\Domains\ReportType\Http\Requests\ReportType\StoreReportTypeRequest;
use App\Domains\ReportType\Http\Requests\ReportType\UpdateReportTypeRequest;
use App\Domains\ReportType\Models\ReportType;
use App\Domains\ReportType\Services\ReportTypeService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportTypeController extends Controller
{
    public function __construct(private ReportTypeService $service) {}

    public function index()
    {
        $reportTypes = $this->service->paginateForManagement();

        return view('pages.report-types.index', compact('reportTypes'));
    }

    public function create()
    {
        return view('pages.report-types.create');
    }

    public function store(StoreReportTypeRequest $request)
    {
        $duplicate = $this->service->findDuplicate($request->validated());

        if ($duplicate) {
            return redirect()
                ->route('report-types.edit', $duplicate)
                ->with('info', 'Jenis laporan dengan Kode SOP, Versi SOP, dan Nomor Annex tersebut sudah ada. Anda dapat mengubah data yang sudah ada di sini.');
        }

        $reportType = $this->service->create($request->validated(), $this->meta($request));

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Jenis laporan berhasil dibuat. Silakan tambahkan section dan lokasi.');
    }

    public function show(ReportType $reportType)
    {
        $reportType->load([
            'sections.locations.room',
            'media',
            'incubatorTypes',
        ]);

        $locations = $this->service->locationsForShow();

        return view('pages.report-types.show', compact('reportType', 'locations'));
    }

    public function edit(ReportType $reportType)
    {
        $reportType->load(['media', 'incubatorTypes']);

        return view('pages.report-types.edit', compact('reportType'));
    }

    public function update(UpdateReportTypeRequest $request, ReportType $reportType)
    {
        $this->service->update($reportType, $request->validated(), $this->meta($request));

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Jenis laporan berhasil diperbarui.');
    }

    public function destroy(Request $request, ReportType $reportType)
    {
        if ($this->service->hasReports($reportType)) {
            return back()->with('error', 'Jenis laporan tidak bisa dihapus karena masih memiliki laporan terkait.');
        }

        $this->service->delete($reportType, $this->meta($request));

        return redirect()
            ->route('report-types.index')
            ->with('success', 'Jenis laporan berhasil dihapus.');
    }

    private function meta($request): array
    {
        return [
            'user_id' => optional($request->user())->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];
    }
}
