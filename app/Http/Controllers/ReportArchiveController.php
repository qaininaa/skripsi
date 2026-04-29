<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ReportArchiveController extends Controller
{
    private const FOLDERS = [
        'ahu-6-1-1-b' => [
            'name' => '6.1.1 B',
            'description' => 'Semua laporan Annex 18',
            'annex_number' => 18,
        ],
    ];

    public function index(Request $request)
    {
        $search = (string) $request->query('search', '');
        $folderKey = $request->query('folder');
        $activeFolder = self::FOLDERS[$folderKey] ?? null;

        $folders = collect(self::FOLDERS)->map(function (array $folder, string $key) {
            $count = $this->archivedReportsQuery()
                ->whereHas('reportType', function ($q) use ($folder) {
                    $q->where('annex_number', $folder['annex_number']);
                })
                ->count();

            return array_merge($folder, [
                'key' => $key,
                'count' => $count,
            ]);
        })->values();

        $reports = null;
        if ($activeFolder) {
            $reports = $this->archivedReportsQuery()
                ->whereHas('reportType', function ($q) use ($activeFolder) {
                    $q->where('annex_number', $activeFolder['annex_number']);
                })
                ->when($search !== '', function ($q) use ($search) {
                    $q->where(function ($q2) use ($search) {
                        $q2->where('product_name', 'like', "%{$search}%")
                            ->orWhere('batch_number', 'like', "%{$search}%");
                    });
                })
                ->orderByDesc('created_at')
                ->paginate(15)
                ->withQueryString();

            // Apply snapshot so renamed/deleted annex/type name doesn't affect listing
            $reports->each(fn ($r) => $r->applyReportTypeSnapshot());
        }

        return view('pages.arsip.index', [
            'folders' => $folders,
            'activeFolder' => $activeFolder ? array_merge($activeFolder, ['key' => $folderKey]) : null,
            'reports' => $reports,
            'search' => $search,
        ]);
    }

    public function show(Request $request, Report $report)
    {
        // Ensure the report is approved by manager
        $report->loadMissing('approvals');
        $managerApproval = $report->approvals->where('step', 3)->where('status', 'approved')->first();
        abort_unless($managerApproval, 404);

        $report->load(['reportType.sections.locations.room', 'reportType.sections.locations.frequency', 'environmentalEntries']);
        $report->applyReportTypeSnapshot();

        $entryMap = [];
        foreach ($report->environmentalEntries as $entry) {
            $entryMap[$entry->report_section_id][$entry->period_number][$entry->shift] = $entry;
        }

        $sectionTypes = $report->reportType->sections->pluck('measurement_type')->unique();
        $needsAirSampler = $sectionTypes->contains('air_sampler');
        $needsInkubator = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();
        $needsMedium = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();
        $folderKey = $request->query('folder');
        $backUrl = route('arsip-laporan.index', $folderKey ? ['folder' => $folderKey] : []);

        return view('pages.supervisor.laporan-cetak', compact(
            'report', 'entryMap', 'needsAirSampler', 'needsInkubator', 'needsMedium', 'backUrl'
        ) + [
            'showPrint' => true,
            'autoPrint' => true,
            'printPreviewOnly' => true,
        ]);
    }

    private function archivedReportsQuery(): Builder
    {
        return Report::query()
            ->with(['reportType'])
            ->whereHas('approvals', function ($q) {
                $q->where('step', 3)->where('status', 'approved');
            });
    }
}
