<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AnalisLaporanController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');
        $userId = Auth::id();

        $mine = fn($q) => $q->where('shift1_analis_id', $userId)
                             ->orWhere('shift2_analis_id', $userId);

        // Hitung count di PHP agar bisa pisahkan status virtual "handed_over"
        $allForCount = Report::where($mine)->get(['status', 'header_data', 'shift1_analis_id']);

        $handedOverCount = $allForCount->filter(fn($r) =>
            $r->status === 'in_progress' &&
            $r->shift1_analis_id === $userId &&
            !empty(($r->header_data ?? [])['shift1_handed_over'])
        )->count();

        $rawCounts = $allForCount->groupBy('status')->map->count();

        $counts = collect([
            'pending'     => $rawCounts['pending']     ?? 0,
            'in_progress' => max(0, ($rawCounts['in_progress'] ?? 0) - $handedOverCount),
            'handed_over' => $handedOverCount,
            'submitted'   => $rawCounts['submitted']   ?? 0,
            'approved'    => $rawCounts['approved']    ?? 0,
            'rejected'    => $rawCounts['rejected']    ?? 0,
        ]);

        // Build main query
        $query = Report::with(['reportType', 'shift1Analis', 'shift2Analis'])
            ->where($mine)
            ->orderByDesc('created_at');

        if ($status === 'handed_over') {
            $query->where('status', 'in_progress')
                  ->where('shift1_analis_id', $userId)
                  ->whereRaw("COALESCE(JSON_UNQUOTE(JSON_EXTRACT(header_data, '$.shift1_handed_over')), 'false') = 'true'");
        } elseif ($status === 'in_progress') {
            $query->where('status', 'in_progress')
                  ->whereRaw("NOT (shift1_analis_id = ? AND COALESCE(JSON_UNQUOTE(JSON_EXTRACT(header_data, '$.shift1_handed_over')), 'false') = 'true')", [$userId]);
        } elseif ($status !== 'all') {
            $query->where('status', $status);
        }

        $items = $query->paginate(15)->withQueryString();

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
                            && ($myShift === 1 ? !$shift1HandedOver : $shift1HandedOver);

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

        // Shift 1 is read-only after handover
        if ($myShift === 1 && $shift1HandedOver) {
            abort(403, 'Data Shift 1 sudah dikunci setelah estafet.');
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
            $supervisorId = (int) $request->input('supervisor_id');
            abort_if($supervisorId === 0, 422, 'Pilih supervisor terlebih dahulu.');
            abort_unless(
                \App\Models\User::where('id', $supervisorId)->where('role', 'supervisor')->exists(),
                422,
                'Supervisor tidak valid.'
            );

            $report->update(['status' => 'submitted']);

            \App\Models\ReportApproval::updateOrCreate(
                ['report_id' => $report->id, 'step' => 2],
                ['role_label' => 'Supervisor', 'user_id' => $supervisorId, 'status' => 'pending']
            );

            return redirect()->route('laporan.index')
                ->with('success', 'Laporan berhasil dikirim ke supervisor.');
        }

        return back()->with('success', 'Data berhasil disimpan sebagai draft.');
    }

    private function processEntries(Request $request, Report $report, int $myShift): void
    {
        // Save header_data and shift assignments together
        $hd = $report->header_data ?? [];
        if ($request->has('header_data')) {
            $hd = array_replace_recursive($hd, $request->input('header_data'));
        }
        $shiftAssignment = $request->input('shift_assignment', []);
        if (!empty($shiftAssignment)) {
            $existing = $hd['shift_assignments'] ?? [];
            foreach ($shiftAssignment as $secId => $cols) {
                $existing[$secId] = array_map('intval', $cols);
            }
            $hd['shift_assignments'] = $existing;
        }
        $settleTimes = $request->input('settle_times', []);
        if (!empty($settleTimes)) {
            $hd['settle_times'] = array_replace_recursive($hd['settle_times'] ?? [], $settleTimes);
        }
        $swabTimes = $request->input('swab_times', []);
        if (!empty($swabTimes)) {
            $hd['swab_times'] = array_replace_recursive($hd['swab_times'] ?? [], $swabTimes);
        }
        if ($request->has('header_data') || !empty($shiftAssignment) || !empty($settleTimes) || !empty($swabTimes)) {
            $report->update(['header_data' => $hd]);
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

            $isShiftBased = in_array($sectionType, ['air_sampler', 'contact_plate', 'swab']);
            $sectionId    = $locationSectionId[(int) $locationId] ?? null;

            foreach ($cols as $colIdx => $data) {
                if ($isShiftBased) {
                    // only save columns assigned to the current user's shift
                    $assignedShift = (int) ($hd['shift_assignments'][$sectionId][$colIdx] ?? 1);
                    if ($assignedShift !== $myShift) {
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
                            ? ($data['jam_mulai'] ?? null ?: null)
                            : ($exposureTimes[$sectionId][$colIdx]['jam_mulai'] ?? null ?: null),
                        'jam_selesai'  => $isShiftBased
                            ? null
                            : ($exposureTimes[$sectionId][$colIdx]['jam_selesai'] ?? null ?: null),
                        'cfu_bacteria' => isset($data['cfu_bacteria']) && $data['cfu_bacteria'] !== ''
                            ? (int) $data['cfu_bacteria'] : null,
                        'cfu_fungi'    => isset($data['cfu_fungi']) && $data['cfu_fungi'] !== ''
                            ? (int) $data['cfu_fungi'] : null,
                    ]
                );
            }
        }
    }

    public function verifyPassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = Auth::user();

        // Must match the currently logged-in user
        if ($user->username !== $request->username || !Hash::check($request->password, $user->password)) {
            return response()->json(['ok' => false, 'message' => 'Username atau password salah.'], 422);
        }

        return response()->json(['ok' => true]);
    }
}
