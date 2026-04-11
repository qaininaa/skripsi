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

        $rawCounts = Report::get(['status'])->groupBy('status')->map->count();

        $counts = collect([
            'pending'    => $rawCounts['pending']    ?? 0,
            'monitoring' => $rawCounts['monitoring'] ?? 0,
            'reading'    => $rawCounts['reading']    ?? 0,
            'submitted'  => $rawCounts['submitted']  ?? 0,
            'returned'   => $rawCounts['returned']   ?? 0,
            'approved'   => $rawCounts['approved']   ?? 0,
        ]);

        $query = Report::with(['reportType', 'approvals.user', 'lockedByUser'])
            ->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $items = $query->paginate(15)->withQueryString();

        return view('pages.laporan.index', compact('items', 'status', 'counts'));
    }

    public function isi(Report $report)
    {
        // Claim an unclaimed / returned report
        if (in_array($report->status, ['pending', 'returned'])
            || ($report->status === 'monitoring' && $report->locked_by === null)) {
            $report->update(['status' => 'monitoring', 'locked_by' => auth()->id()]);
        } elseif ($report->status === 'reading' && $report->locked_by === null) {
            $report->update(['locked_by' => auth()->id()]);
        }
        $report->refresh();
        $this->migrateFieldOwners($report);

        $report->load(['reportType.sections.locations.room', 'entries', 'approvals.user', 'lockedByUser']);

        // entryMap[$pivot_id][$period_number][$shift] = entry
        $entryMap = [];
        foreach ($report->entries as $entry) {
            $entryMap[$entry->report_section_id][$entry->period_number][$entry->shift] = $entry;
        }

        $sectionTypes    = $report->reportType->sections->pluck('measurement_type')->unique();
        $needsAirSampler = $sectionTypes->contains('air_sampler');
        $needsInkubator  = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();
        $needsMedium     = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();

        $isEditable      = in_array($report->status, ['monitoring', 'reading'])
                           && $report->locked_by === auth()->id();
        $isMonitoringPhase = $report->status === 'monitoring';
        $myShift         = 1;

        $analis = User::where('role', 'analis')->orderBy('name')->get();
        // Analysts that can receive a handover (all analysts except current user)
        $otherAnalis = $analis->where('id', '!=', auth()->id())->values();

        return view('pages.laporan.isi', compact(
            'report', 'myShift', 'entryMap',
            'needsAirSampler', 'needsInkubator', 'needsMedium',
            'isEditable', 'isMonitoringPhase', 'analis', 'otherAnalis'
        ));
    }

    public function lihat(Report $report)
    {
        $report->load(['reportType.sections.locations.room', 'entries', 'approvals.user', 'lockedByUser']);

        $entryMap = [];
        foreach ($report->entries as $entry) {
            $entryMap[$entry->report_section_id][$entry->period_number][$entry->shift] = $entry;
        }

        $sectionTypes    = $report->reportType->sections->pluck('measurement_type')->unique();
        $needsAirSampler = $sectionTypes->contains('air_sampler');
        $needsInkubator  = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();
        $needsMedium     = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();

        $isEditable        = false;
        $isMonitoringPhase = $report->status === 'monitoring';
        $myShift           = 1;

        $analis      = User::where('role', 'analis')->orderBy('name')->get();
        $otherAnalis = $analis->where('id', '!=', auth()->id())->values();

        return view('pages.laporan.isi', compact(
            'report', 'myShift', 'entryMap',
            'needsAirSampler', 'needsInkubator', 'needsMedium',
            'isEditable', 'isMonitoringPhase', 'analis', 'otherAnalis'
        ));
    }

    public function save(Request $request, Report $report)
    {
        abort_if(in_array($report->status, ['submitted', 'approved']), 403);
        abort_if($report->locked_by !== auth()->id(), 403);

        $this->processEntries($request, $report);
        $action = $request->input('action', 'save');

        if ($action === 'submit') {
            abort_unless($report->status === 'reading', 403);
            $supervisorId = (int) $request->input('supervisor_id');
            abort_if($supervisorId === 0, 422, 'Pilih supervisor terlebih dahulu.');
            abort_unless(
                User::where('id', $supervisorId)->where('role', 'supervisor')->exists(),
                422,
                'Supervisor tidak valid.'
            );

            $freshHd = $this->markAnalystSignaturesAsSigned($report->fresh()->header_data ?? [], $report);
            $report->update(['header_data' => $freshHd, 'status' => 'submitted', 'locked_by' => null]);

            \App\Models\ReportApproval::updateOrCreate(
                ['report_id' => $report->id, 'step' => 2],
                ['role_label' => 'Supervisor', 'user_id' => $supervisorId, 'status' => 'pending',
                 'signed_at' => null, 'notes' => null, 'returned_to_user_id' => null]
            );

            return redirect()->route('laporan.index')
                ->with('success', 'Laporan berhasil dikirim ke supervisor.');
        }

        if ($action === 'finish_monitoring') {
            abort_unless($report->status === 'monitoring', 403);
            Report::where('id', $report->id)->update(['status' => 'reading', 'locked_by' => null]);
            return redirect()->route('laporan.index')
                ->with('success', 'Monitoring selesai. Laporan masuk ke tahap pembacaan.');
        }

        if ($action === 'handover') {
            // Release the lock — any analyst can pick it up next
            Report::where('id', $report->id)->update(['locked_by' => null]);
            return redirect()->route('laporan.index')
                ->with('success', 'Draft tersimpan. Laporan bisa dilanjutkan oleh analis lain.');
        }

        // default: save draft — keep locked_by
        return back()->with('success', 'Data berhasil disimpan sebagai draft.');
    }

    private function migrateFieldOwners(Report $report): void
    {
        $hd = $report->header_data ?? [];
        $owners = $hd['_field_owners'] ?? [];
        if (empty($owners)) return;
        $changed = false;
        foreach (array_keys($owners) as $k) {
            if (!str_contains((string) $k, '.')) {
                $sectionData = $hd[$k] ?? [];
                if (is_array($sectionData)) {
                    foreach (array_keys($sectionData) as $fk) {
                        if (!isset($owners["{$k}.{$fk}"])) {
                            $owners["{$k}.{$fk}"] = $owners[$k];
                            $changed = true;
                        }
                    }
                }
                unset($owners[$k]);
                $changed = true;
            }
        }
        if ($changed) {
            $hd['_field_owners'] = $owners;
            $report->header_data = $hd;
            $report->saveQuietly();
        }
    }

    private function processEntries(Request $request, Report $report): void
    {
        $myShift = 1;

        // Save header_data (analyst assignments + timing data)
        $hd = $report->header_data ?? [];
        $owners = $hd['_field_owners'] ?? [];
        // Migrate any old per-section ownership keys to per-field format
        foreach (array_keys($owners) as $k) {
            if (!str_contains((string) $k, '.')) {
                $sectionData = $hd[$k] ?? [];
                if (is_array($sectionData)) {
                    foreach (array_keys($sectionData) as $fk) {
                        if (!isset($owners["{$k}.{$fk}"])) {
                            $owners["{$k}.{$fk}"] = $owners[$k];
                        }
                    }
                }
                unset($owners[$k]);
            }
        }
        if ($request->has('header_data')) {
            $incoming = $request->input('header_data');
            // Remove ownership meta from incoming data
            unset($incoming['_field_owners']);
            foreach ($incoming as $sectionKey => $sectionData) {
                if (!is_array($sectionData)) {
                    $ownerKey = $sectionKey;
                    if (isset($owners[$ownerKey]) && (int) $owners[$ownerKey] !== Auth::id()) continue;
                    if ($sectionData !== null && $sectionData !== '') $owners[$ownerKey] = Auth::id();
                    $hd[$sectionKey] = $sectionData;
                    continue;
                }
                foreach ($sectionData as $fieldKey => $fieldValue) {
                    $ownerKey = "{$sectionKey}.{$fieldKey}";
                    if (isset($owners[$ownerKey]) && (int) $owners[$ownerKey] !== Auth::id()) continue;
                    if ($fieldValue !== null && $fieldValue !== '') $owners[$ownerKey] = Auth::id();
                    $hd[$sectionKey][$fieldKey] = $fieldValue;
                }
            }
            $hd['_field_owners'] = $owners;
        }

        // Save analyst_monitoring and analyst_reading to the report
        if ($request->has('analyst_monitoring')) {
            $report->analyst_monitoring = array_values(array_filter(array_map('intval', (array) $request->input('analyst_monitoring'))));
        }
        if ($request->has('analyst_reading')) {
            $report->analyst_reading = array_values(array_filter(array_map('intval', (array) $request->input('analyst_reading'))));
        }
        if ($request->has('analyst_monitoring') || $request->has('analyst_reading')) {
            $report->save();
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
            foreach ($settleTimes as $secId => $data) {
                $ownerKey = "settle_times_{$secId}";
                if (isset($owners[$ownerKey]) && (int) $owners[$ownerKey] !== Auth::id()) {
                    continue;
                }
                $hasVal = collect($data)->flatten()->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
                if ($hasVal) { $owners[$ownerKey] = Auth::id(); }
                $hd['settle_times'][$secId] = array_replace_recursive($hd['settle_times'][$secId] ?? [], $data);
            }
            $hd['_field_owners'] = $owners;
        }
        $swabTimes = $request->input('swab_times', []);
        if (!empty($swabTimes)) {
            foreach ($swabTimes as $secId => $data) {
                $ownerKey = "swab_times_{$secId}";
                if (isset($owners[$ownerKey]) && (int) $owners[$ownerKey] !== Auth::id()) {
                    continue;
                }
                $hasVal = collect($data)->flatten()->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
                if ($hasVal) { $owners[$ownerKey] = Auth::id(); }
                $hd['swab_times'][$secId] = array_replace_recursive($hd['swab_times'][$secId] ?? [], $data);
            }
            $hd['_field_owners'] = $owners;
        }
        $exposureTimes = $request->input('exposure_times', []);
        if (!empty($exposureTimes)) {
            foreach ($exposureTimes as $secId => $data) {
                $ownerKey = "exposure_times_{$secId}";
                if (isset($owners[$ownerKey]) && (int) $owners[$ownerKey] !== Auth::id()) {
                    continue;
                }
                $hasVal = collect($data)->flatten()->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
                if ($hasVal) { $owners[$ownerKey] = Auth::id(); }
                $hd['exposure_times'][$secId] = array_replace_recursive($hd['exposure_times'][$secId] ?? [], $data);
            }
            $hd['_field_owners'] = $owners;
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

        // Pre-load entries owned by other analysts — these must not be overwritten
        $lockedEntryKeys = ReportEntry::where('report_id', $report->id)
            ->where('analyst_id', '!=', Auth::id())
            ->whereNotNull('analyst_id')
            ->where(function ($q) {
                $q->whereNotNull('cfu_bacteria')->orWhereNotNull('cfu_fungi');
            })
            ->get()
            ->map(fn ($e) => "{$e->report_section_id}-{$e->period_number}-{$e->shift}")
            ->toArray();

        // Upsert entries
        foreach ($request->input('entries', []) as $pivotId => $cols) {
            $sectionType = $pivotSectionType[(int) $pivotId] ?? null;
            if (! $sectionType) {
                continue;
            }

            $timeSlotType = $pivotSectionTimeSlot[(int) $pivotId] ?? 'none';
            $sectionId    = $pivotSectionId[(int) $pivotId] ?? null;

            foreach ($cols as $colIdx => $data) {
                $periodNumber = (int) $colIdx;
                $shift        = $myShift;

                $hasData = collect($data)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
                if (! $hasData) {
                    continue;
                }

                // Skip entries owned by another analyst
                $entryKey = ((int) $pivotId) . "-{$periodNumber}-{$shift}";
                if (in_array($entryKey, $lockedEntryKeys)) {
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
                        'start_time'   => ($timeSlotType === 'per_location')
                            ? ($data['start_time'] ?? null ?: null)
                            : ($exposureTimes[$sectionId][$colIdx]['start_time'] ?? null ?: null),
                        'end_time'     => ($timeSlotType === 'per_location')
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

    private function markAnalystSignaturesAsSigned(array $headerData, Report $report): array
    {
        $monitoringIds = $report->analyst_monitoring ?? [];
        $readingIds    = $report->analyst_reading    ?? [];

        $headerData['ttd_monitoring_id'] = (int) ($monitoringIds[0] ?? 0) ?: null;
        $headerData['ttd_dibaca_id']     = (int) ($readingIds[0]    ?? 0) ?: null;

        $signedAt = now()->toDateTimeString();
        $headerData['ttd_monitoring_signed_at'] = $signedAt;
        $headerData['ttd_dibaca_signed_at']     = $signedAt;

        return $headerData;
    }
}
