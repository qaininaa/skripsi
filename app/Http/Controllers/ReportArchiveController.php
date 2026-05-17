<?php

namespace App\Http\Controllers;

use App\Domains\Report\Models\Report;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportArchiveController extends Controller
{
    private const FOLDERS = [
        'ahu-6-1-1-a' => [
            'name' => '6.1.1 A',
            'description' => 'Laporan Pemantauan Ruangan Produksi Injeksi HVAC 6.1.1 A Filling Line 1',
            'annex_number' => 17,
        ],
        'ahu-6-1-1-b' => [
            'name' => '6.1.1 B',
            'description' => 'Laporan Pemantauan Ruangan Produksi Injeksi HVAC 6.1.1 B Filling Line 1',
            'annex_number' => 18,
        ],
        'ahu-6-2-1' => [
            'name' => '6.2.1',
            'description' => 'Laporan Pemantauan Ruangan Laboratorium HVAC 6.2.1',
            'annex_number' => 21,
        ],
        'ahu-6-2-2' => [
            'name' => '6.2.2',
            'description' => 'Laporan Pemantauan Ruangan Laboratorium HVAC 6.2.2',
            'annex_number' => 22,
        ],
        'ahu-6-2-3' => [
            'name' => '6.2.3',
            'description' => 'Laporan Pemantauan Ruangan Laboratorium HVAC 6.2.3',
            'annex_number' => 23,
        ],
        'ahu-6-1-5' => [
            'name' => '6.1.5',
            'description' => 'Laporan Pemantauan Ruangan Produksi Injeksi HVAC 6.1.5',
            'annex_number' => 24,
        ],
        'ahu-6-1-2' => [
            'name' => '6.1.2',
            'description' => 'Laporan Pemantauan Ruangan Produksi Injeksi HVAC 6.1.2 Mingguan',
            'annex_number' => 34,
        ],
        'ahu-6-1-2' => [
            'name' => '6.1.2',
            'description' => 'Laporan Pemantauan Ruangan Produksi Injeksi HVAC 6.1.2 Bulanan',
            'annex_number' => 35,
        ],
        'ahu-6-1-3' => [
            'name' => '6.1.3',
            'description' => 'Laporan Pemantauan Ruangan Produksi Injeksi HVAC 6.1.3 Bulanan dan Mingguan',
            'annex_number' => 36,
        ],
        'ahu-6-1-3' => [
            'name' => '6.1.3',
            'description' => 'Laporan Pemantauan Ruangan Produksi Injeksi HVAC 6.1.3 Setiap 6 Bulan',
            'annex_number' => 37,
        ],
        'ahu-6-2-3' => [
            'name' => '6.2.3',
            'description' => 'Laporan Pemantauan Ruangan Laboratorium Mikrobiologi HVAC 6.2.3 Rutin',
            'annex_number' => 38,
        ],
        'ahu-6-2-1' => [
            'name' => '6.2.1',
            'description' => 'Laporan Pemantauan Ruangan Laboratorium Mikrobiologi HVAC 6.2.1 Bulanan',
            'annex_number' => 40,
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

        $report->forceFill([
            'printed_at' => now(),
            'printed_by' => Auth::id(),
        ])->save();

        $report->load([
            'reportType.sections.locations.room',
            'reportType.mediumTypes',
            'reportType.incubatorTypes',
            'environmentalEntries.envSectionInstance',
            'sectionColumnNames',
            'sectionNotes',
            'instrumentEntries',
            'mediumIdentities',
            'incubators.entries.incubatedBy',
            'incubators.entries.removedBy',
            'approvals.user',
        ]);
        $report->applyReportTypeSnapshot();

        $entryMap = [];
        foreach ($report->environmentalEntries as $entry) {
            $locationId = optional($entry->envSectionInstance)->location_id;
            if (! $locationId) {
                continue;
            }
            $entryMap[$locationId][$entry->period_number][$entry->shift] = $entry;
        }

        $sectionTypes = $report->reportType->sections
            ->map(fn ($section) => $section->measurement_key)
            ->unique();
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
            ->with(['reportType', 'printedByUser'])
            ->whereHas('approvals', function ($q) {
                $q->where('step', 3)->where('status', 'approved');
            });
    }
}
