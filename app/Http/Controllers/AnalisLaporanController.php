<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalisLaporanController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');
        $userId = Auth::id();

        $query = Report::with(['reportType', 'shift1Analis', 'shift2Analis'])
            ->where(fn($q) => $q->where('shift1_analis_id', $userId)
                                ->orWhere('shift2_analis_id', $userId))
            ->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $items = $query->paginate(15)->withQueryString();

        $counts = Report::where(fn($q) => $q->where('shift1_analis_id', $userId)
                                             ->orWhere('shift2_analis_id', $userId))
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('dashboard.laporan.index', compact('items', 'status', 'counts'));
    }

    public function isi(Report $report)
    {
        $userId = Auth::id();
        abort_if(
            $report->shift1_analis_id !== $userId && $report->shift2_analis_id !== $userId,
            403
        );

        $myShift    = $report->shift1_analis_id === $userId ? 1 : 2;
        $otherShift = $myShift === 1 ? 2 : 1;

        // Hanya shift 1 yang boleh mengubah status dari pending → in_progress
        if ($report->status === 'pending' && $myShift === 1) {
            $report->update(['status' => 'in_progress']);
        }

        $report->load(['reportType.sections.locations', 'entries', 'shift1Analis', 'shift2Analis']);

        // entryMap[$loc_id][$period_number][$shift] = entry
        $entryMap = [];
        foreach ($report->entries as $entry) {
            $entryMap[$entry->report_location_id][$entry->period_number][$entry->shift] = $entry;
        }

        $sectionTypes    = $report->reportType->sections->pluck('measurement_type')->unique();
        $needsAirSampler = $sectionTypes->contains('air_sampler');
        $needsInkubator  = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();
        $needsMedium     = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();

        $shift1HandedOver = !empty(($report->header_data ?? [])['shift1_handed_over']);
        $isEditable       = $report->status === 'in_progress'
                            && ($myShift === 1 || $shift1HandedOver);

        return view('dashboard.laporan.isi', compact(
            'report', 'myShift', 'otherShift', 'entryMap',
            'needsAirSampler', 'needsInkubator', 'needsMedium',
            'isEditable', 'shift1HandedOver'
        ));
    }

    public function save(Request $request, Report $report)
    {
        $userId = Auth::id();
        abort_if(
            $report->shift1_analis_id !== $userId && $report->shift2_analis_id !== $userId,
            403
        );
        abort_if(in_array($report->status, ['submitted', 'approved']), 403);

        $myShift = $report->shift1_analis_id === $userId ? 1 : 2;

        // Shift 2 cannot save until shift 1 has handed over
        $shift1HandedOver = !empty(($report->header_data ?? [])['shift1_handed_over']);
        if ($myShift === 2 && !$shift1HandedOver) {
            abort(403, 'Shift 1 belum meneruskan laporan.');
        }

        $this->processEntries($request, $report, $myShift);

        if ($request->input('action') === 'handover') {
            abort_if($myShift !== 1, 403);
            $report->refresh();
            $hd = $report->header_data ?? [];
            $hd['shift1_handed_over'] = true;
            $report->update(['header_data' => $hd]);
            return back()->with('success', 'Data Shift 1 berhasil disimpan dan diteruskan ke Shift 2.');
        }

        if ($request->input('action') === 'submit') {
            $report->update(['status' => 'submitted']);
            return redirect()->route('laporan.index')
                ->with('success', 'Laporan berhasil dikirim untuk review.');
        }

        return back()->with('success', 'Data berhasil disimpan sebagai draft.');
    }

    private function processEntries(Request $request, Report $report, int $myShift): void
    {
        // Save header_data (merge to preserve other fields)
        if ($request->has('header_data')) {
            $existing = $report->header_data ?? [];
            $merged   = array_replace_recursive($existing, $request->input('header_data'));
            $report->update(['header_data' => $merged]);
        }

        // Build location→measurement_type and location→section_id maps
        $locationSectionType = [];
        $locationSectionId   = [];
        foreach ($report->reportType->sections()->with('locations')->get() as $section) {
            foreach ($section->locations as $location) {
                $locationSectionType[$location->id] = $section->measurement_type;
                $locationSectionId[$location->id]   = $section->id;
            }
        }

        // Exposure-level times (jam_mulai/jam_selesai) for settle_plate sections
        $exposureTimes = $request->input('exposure_times', []);

        // Upsert entries
        foreach ($request->input('entries', []) as $locationId => $cols) {
            $sectionType = $locationSectionType[(int) $locationId] ?? null;
            if (! $sectionType) {
                continue;
            }

            $isShiftBased = in_array($sectionType, ['air_sampler', 'contact_plate']);
            $sectionId    = $locationSectionId[(int) $locationId] ?? null;

            foreach ($cols as $colIdx => $data) {
                if ($isShiftBased) {
                    // colIdx = shift; only save current user's column
                    if ((int) $colIdx !== $myShift) {
                        continue;
                    }
                    $periodNumber = 1;
                    $shift        = $myShift;
                } else {
                    // colIdx = period_number
                    $periodNumber = (int) $colIdx;
                    $shift        = $myShift;
                }

                $hasData = collect($data)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
                if (! $hasData) {
                    continue;
                }

                ReportEntry::updateOrCreate(
                    [
                        'report_id'          => $report->id,
                        'report_location_id' => (int) $locationId,
                        'period_number'      => $periodNumber,
                        'shift'              => $shift,
                    ],
                    [
                        'analis_id'    => Auth::id(),
                        'jam_mulai'    => $isShiftBased
                            ? ($data['jam_mulai'] ?: null)
                            : ($exposureTimes[$sectionId][$colIdx]['jam_mulai'] ?: null),
                        'jam_selesai'  => $isShiftBased
                            ? null
                            : ($exposureTimes[$sectionId][$colIdx]['jam_selesai'] ?: null),
                        'cfu_bacteria' => isset($data['cfu_bacteria']) && $data['cfu_bacteria'] !== ''
                            ? (int) $data['cfu_bacteria'] : null,
                        'cfu_fungi'    => isset($data['cfu_fungi']) && $data['cfu_fungi'] !== ''
                            ? (int) $data['cfu_fungi'] : null,
                    ]
                );
            }
        }
    }
}
