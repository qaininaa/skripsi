<?php

namespace App\Http\Controllers\ReportType;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportType\ReportTypeStoreRequest;
use App\Http\Requests\ReportType\ReportTypeUpdateRequest;
use Domain\ReportType\Models\ReportType;
use Domain\ReportType\Services\ReportTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller for report type aggregate management actions.
 */
class ReportTypeController extends Controller
{
    public function __construct(private ReportTypeService $reportTypeService) {}

    /**
     * Show paginated report type list.
     */
    public function index(): View
    {
        $reportTypes = $this->reportTypeService->paginateForManagement();

        return view('pages.report-types.index', compact('reportTypes'));
    }

    /**
     * Show report type create form.
     */
    public function create(): View
    {
        return view('pages.report-types.create');
    }

    /**
     * Persist a new report type from validated DTO.
     */
    public function store(ReportTypeStoreRequest $request): RedirectResponse
    {
        $dto = $request->toDTO();

        $duplicate = $this->reportTypeService->findDuplicate($dto);

        if ($duplicate) {
            return redirect()
                ->route('report-types.edit', $duplicate)
                ->with('info', 'Jenis laporan dengan Kode SOP, Versi SOP, dan Nomor Annex tersebut sudah ada. Anda dapat mengubah data yang sudah ada di sini.');
        }

        $reportType = $this->reportTypeService->createReportType($dto, $this->meta($request));

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Jenis laporan berhasil dibuat. Silakan tambahkan section dan lokasi.');
    }

    /**
     * Show report type detail page.
     */
    public function show(ReportType $reportType): View
    {
        $reportType->load([
            'sections.locations.room',
            'media',
            'incubatorTypes',
        ]);

        $locations = $this->reportTypeService->locationsForShow();

        return view('pages.report-types.show', compact('reportType', 'locations'));
    }

    /**
     * Show report type edit form.
     */
    public function edit(ReportType $reportType): View
    {
        $reportType->load(['media', 'incubatorTypes']);

        return view('pages.report-types.edit', compact('reportType'));
    }

    /**
     * Update an existing report type from validated DTO.
     */
    public function update(ReportTypeUpdateRequest $request, ReportType $reportType): RedirectResponse
    {
        $this->reportTypeService->updateReportType($reportType, $request->toDTO(), $this->meta($request));

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Jenis laporan berhasil diperbarui.');
    }

    /**
     * Delete report type when no reports are linked.
     */
    public function destroy(Request $request, ReportType $reportType): RedirectResponse
    {
        if ($this->reportTypeService->hasReports($reportType)) {
            return back()->with('error', 'Jenis laporan tidak bisa dihapus karena masih memiliki laporan terkait.');
        }

        $this->reportTypeService->deleteReportType($reportType, $this->meta($request));

        return redirect()
            ->route('report-types.index')
            ->with('success', 'Jenis laporan berhasil dihapus.');
    }

    /**
     * Build audit log meta from current request.
     *
     * @return array{user_id: string|null, ip_address: string|null, user_agent: string|null}
     */
    private function meta(Request $request): array
    {
        return [
            'user_id' => $request->user()?->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];
    }
}
