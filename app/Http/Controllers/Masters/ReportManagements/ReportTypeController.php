<?php

namespace App\Http\Controllers\Masters\ReportManagements;

use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\ReportTypes\StoreReportTypeRequest;
use App\Http\Requests\Masters\ReportTypes\UpdateReportTypeRequest;
use App\Models\ReportLocation;
use App\Models\ReportType;
use App\Services\Masters\ReportManagements\ReportTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportTypeController extends Controller
{
    public function __construct(private ReportTypeService $service) {}

    public function index(): View
    {
        $reportTypes = ReportType::withCount('sections')
            ->orderBy('annex_number')
            ->paginate(15);

        return view('pages.report-types.index', compact('reportTypes'));
    }

    public function create(): View
    {
        return view('pages.report-types.create');
    }

    public function store(StoreReportTypeRequest $request): RedirectResponse
    {
        $reportType = $this->service->create($request->validated(), $this->meta($request));

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Jenis laporan berhasil dibuat. Silakan tambahkan seksi dan lokasi.');
    }

    public function show(ReportType $reportType): View
    {
        $reportType->load(['sections.locations.room', 'media', 'incubatorConfigs']);
        $locations = ReportLocation::with('room')->orderBy('room_id')->orderBy('location_number')->get();

        return view('pages.report-types.show', compact('reportType', 'locations'));
    }

    public function edit(ReportType $reportType): View
    {
        $reportType->load(['media', 'incubatorConfigs']);

        return view('pages.report-types.edit', compact('reportType'));
    }

    public function update(UpdateReportTypeRequest $request, ReportType $reportType): RedirectResponse
    {
        $this->service->update($reportType, $request->validated(), $this->meta($request));

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Jenis laporan berhasil diperbarui.');
    }

    public function destroy(Request $request, ReportType $reportType): RedirectResponse
    {
        if ($reportType->reports()->exists()) {
            return back()->with('error', 'Jenis laporan tidak bisa dihapus karena masih memiliki laporan terkait.');
        }

        $this->service->delete($reportType, $this->meta($request));

        return redirect()
            ->route('report-types.index')
            ->with('success', 'Jenis laporan berhasil dihapus.');
    }

    private function meta(Request $request): array
    {
        return [
            'user_id'    => $request->user()?->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];
    }
}