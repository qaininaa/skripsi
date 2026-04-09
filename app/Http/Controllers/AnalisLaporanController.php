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

        $mine = fn($q) => $q->where('shift1_analyst_id', $userId)
                             ->orWhere('shift2_analyst_id', $userId);

        $rawCounts = Report::where($mine)->get(['status'])->groupBy('status')->map->count();

        $counts = collect([
            'pending'     => $rawCounts['pending']     ?? 0,
            'in_progress' => $rawCounts['in_progress'] ?? 0,
            'submitted'   => $rawCounts['submitted']   ?? 0,
            'returned'    => $rawCounts['returned']    ?? 0,
            'approved'    => $rawCounts['approved']    ?? 0,
            'rejected'    => $rawCounts['rejected']    ?? 0,
        ]);

        $query = Report::with(['reportType', 'shift1Analis', 'shift2Analis', 'approvals.user'])
            ->where($mine)
            ->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $items = $query->paginate(15)->withQueryString();

        return view('pages.laporan.index', compact('items', 'status', 'counts'));
    }

    public function isi(Report $report)
    {
        $userId = Auth::id();
        abort_if(
            $report->shift1_analyst_id !== $userId && $report->shift2_analyst_id !== $userId,
            403
        );

        $myShift    = $report->shift1_analyst_id === $userId ? 1 : 2;
        $otherShift = $myShift === 1 ? 2 : 1;

        // Hanya shift 1 yang boleh mengubah status dari pending → in_progress
        if ($report->status === 'pending' && $myShift === 1) {
            $report->update(['status' => 'in_progress']);
        }

        // Jika laporan dikembalikan, ubah ke in_progress agar bisa diedit
        if ($report->status === 'returned') {
            $report->update(['status' => 'in_progress']);
        }

        $report->load(['reportType.sections.locations.room', 'entries', 'shift1Analis', 'shift2Analis', 'approvals.user']);

        // entryMap[$pivot_id][$period_number][$shift] = entry
        $entryMap = [];
        foreach ($report->entries as $entry) {
            $entryMap[$entry->report_section_id][$entry->period_number][$entry->shift] = $entry;
        }

        $sectionTypes    = $report->reportType->sections->pluck('measurement_type')->unique();
        $needsAirSampler = $sectionTypes->contains('air_sampler');
        $needsInkubator  = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();
        $needsMedium     = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();

        $shift1HandedOver = !empty(($report->header_data ?? [])['shift1_handed_over']);

        // Edge case: shift 1 sudah handed over tapi shift 2 tidak ada (mis. dihapus admin)
        // → reset flag agar shift 1 bisa melanjutkan
        if ($shift1HandedOver && is_null($report->shift2_analyst_id)) {
            $hd = $report->header_data ?? [];
            unset($hd['shift1_handed_over']);
            $report->update(['header_data' => $hd]);
            $shift1HandedOver = false;
        }

        $isEditable       = $report->status === 'in_progress'
                            && ($myShift === 1 ? !$shift1HandedOver : $shift1HandedOver);

        return view('pages.laporan.isi', compact(
            'report', 'myShift', 'otherShift', 'entryMap',
            'needsAirSampler', 'needsInkubator', 'needsMedium',
            'isEditable', 'shift1HandedOver'
        ));
    }

    public function save(Request $request, Report $report)
    {
        $userId = Auth::id();
        abort_if(
            $report->shift1_analyst_id !== $userId && $report->shift2_analyst_id !== $userId,
            403
        );
        abort_if(in_array($report->status, ['submitted', 'approved']), 403);

        $myShift = $report->shift1_analyst_id === $userId ? 1 : 2;

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
            abort_if($myShift !== 1 || ! $report->shift2_analyst_id, 403);
            $report->refresh();
            $hd = $this->syncAnalystSignatureAssignments($report->header_data ?? [], $report);
            $hd['shift1_handed_over'] = true;
            $hd['ttd_monitoring_signed_at'] = now()->toDateTimeString();
            $report->update(['header_data' => $hd]);
            return back()->with('success', 'Data Shift 1 berhasil disimpan dan diteruskan ke Shift 2.');
        }

        if ($request->input('action') === 'submit') {
            abort_if($report->shift2_analyst_id && $myShift !== 2, 403, 'Shift 2 yang harus mengirim laporan.');
            $supervisorId = (int) $request->input('supervisor_id');
            abort_if($supervisorId === 0, 422, 'Pilih supervisor terlebih dahulu.');
            abort_unless(
                User::where('id', $supervisorId)->where('role', 'supervisor')->exists(),
                422,
                'Supervisor tidak valid.'
            );

            $freshHd = $this->markAnalystSignaturesAsSigned($report->fresh()->header_data ?? [], $report);
            $report->update(['header_data' => $freshHd]);

            $report->update(['status' => 'submitted']);

            // Reset approval if it was previously returned
            \App\Models\ReportApproval::updateOrCreate(
                ['report_id' => $report->id, 'step' => 2],
                ['role_label' => 'Supervisor', 'user_id' => $supervisorId, 'status' => 'pending', 'signed_at' => null, 'notes' => null, 'returned_to_user_id' => null]
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
        $hd = $this->syncAnalystSignatureAssignments($hd, $report);
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
        $exposureTimes = $request->input('exposure_times', []);
        if (!empty($exposureTimes)) {
            $hd['exposure_times'] = array_replace_recursive($hd['exposure_times'] ?? [], $exposureTimes);
        }
        if ($request->has('header_data') || !empty($shiftAssignment) || !empty($settleTimes) || !empty($swabTimes) || !empty($exposureTimes)) {
            $report->update(['header_data' => $hd]);
        }

        // Build pivot_row → measurement_type and pivot_row → section_id maps
        $pivotSectionType     = [];
        $pivotSectionId       = [];
        $pivotSectionTimeSlot = [];
        foreach ($report->reportType->sections()->with('locations')->get() as $section) {
            foreach ($section->locations as $location) {
                $pivotSectionType[$location->pivot->id]     = $section->measurement_type;
                $pivotSectionId[$location->pivot->id]       = $section->id;
                $pivotSectionTimeSlot[$location->pivot->id] = $section->time_slot_type;
            }
        }

        // Upsert entries
        foreach ($request->input('entries', []) as $pivotId => $cols) {
            $sectionType = $pivotSectionType[(int) $pivotId] ?? null;
            if (! $sectionType) {
                continue;
            }

            $isShiftBased = in_array($sectionType, ['air_sampler', 'contact_plate', 'swab']);
            $timeSlotType = $pivotSectionTimeSlot[(int) $pivotId] ?? 'none';
            $sectionId    = $pivotSectionId[(int) $pivotId] ?? null;

            foreach ($cols as $colIdx => $data) {
                if ($isShiftBased) {
                    // only save columns assigned to the current user's shift
                    $assignedShift = (int) ($hd['shift_assignments'][$sectionId][$colIdx] ?? 1);
                    if ($assignedShift !== $myShift) {
                        continue;
                    }
                    $periodNumber = (int) $colIdx;
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
                        'report_section_id'  => (int) $pivotId,
                        'period_number'      => $periodNumber,
                        'shift'              => $shift,
                    ],
                    [
                        'analyst_id'   => Auth::id(),
                        'start_time'   => ($isShiftBased || $timeSlotType === 'per_location')
                            ? ($data['start_time'] ?? null ?: null)
                            : ($exposureTimes[$sectionId][$colIdx]['start_time'] ?? null ?: null),
                        'end_time'     => ($isShiftBased || $timeSlotType === 'per_location')
                            ? null
                            : ($exposureTimes[$sectionId][$colIdx]['end_time'] ?? null ?: null),
                        'cfu_bacteria' => isset($data['cfu_bacteria']) && $data['cfu_bacteria'] !== ''
                            ? (float) $data['cfu_bacteria'] : null,
                        'cfu_fungi'    => isset($data['cfu_fungi']) && $data['cfu_fungi'] !== ''
                            ? (float) $data['cfu_fungi'] : null,
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

    private function syncAnalystSignatureAssignments(array $headerData, Report $report): array
    {
        $headerData['ttd_monitoring_id'] = (int) $report->shift1_analyst_id;
        $headerData['ttd_dibaca_id'] = (int) ($report->shift2_analyst_id ?: $report->shift1_analyst_id);

        return $headerData;
    }

    private function markAnalystSignaturesAsSigned(array $headerData, Report $report): array
    {
        $headerData = $this->syncAnalystSignatureAssignments($headerData, $report);
        $signedAt = now()->toDateTimeString();

        if ($report->shift2_analyst_id) {
            $headerData['ttd_monitoring_signed_at'] = $headerData['ttd_monitoring_signed_at'] ?? $signedAt;
            $headerData['ttd_dibaca_signed_at'] = $signedAt;
        } else {
            $headerData['ttd_monitoring_signed_at'] = $signedAt;
            $headerData['ttd_dibaca_signed_at'] = $signedAt;
        }

        return $headerData;
    }
}
