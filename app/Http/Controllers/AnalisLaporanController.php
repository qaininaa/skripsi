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
        $userId = auth()->id();

        // Capture returned approval BEFORE status changes (for the revision banner)
        $returnedApproval = null;
        if ($report->status === 'returned') {
            $returnedApproval = \App\Models\ReportApproval::where('report_id', $report->id)
                ->where('status', 'returned')
                ->with('user')
                ->first();
            // Only the intended recipient can claim and edit
            if ($returnedApproval
                && $returnedApproval->returned_to_user_id !== null
                && $returnedApproval->returned_to_user_id !== $userId) {
                return redirect()->route('laporan.index')
                    ->with('error', 'Laporan ini dikembalikan ke analis lain dan tidak dapat Anda akses.');
            }
        }

        // Claim an unclaimed / returned report
        if (in_array($report->status, ['pending', 'returned'])
            || ($report->status === 'monitoring' && $report->locked_by === null)) {
            $report->update(['status' => 'monitoring', 'locked_by' => $userId]);
        } elseif ($report->status === 'reading' && $report->locked_by === null) {
            // Add current user to analyst_reading if not already present
            $readingIds = $report->analyst_reading ?? [];
            $updates = ['locked_by' => auth()->id()];
            if (!in_array(auth()->id(), $readingIds)) {
                $readingIds[] = auth()->id();
                $updates['analyst_reading'] = $readingIds;
            }
            $report->update($updates);
            // Stamp per-analyst reading timestamp
            $hd = $report->fresh()->header_data ?? [];
            $hd['ttd_reading_timestamps'][(string) auth()->id()] = now()->toDateTimeString();
            $report->update(['header_data' => $hd]);
        }
        $report->refresh();
        $this->migrateFieldOwners($report);

        $report->load(['reportType.sections.locations.room', 'entries', 'approvals.user', 'lockedByUser']);

        // entryMap[$pivot_id][$instance][$period_number][$shift] = entry
        $entryMap = [];
        foreach ($report->entries as $entry) {
            $entryMap[$entry->report_section_id][$entry->instance_number ?? 1][$entry->period_number][$entry->shift] = $entry;
        }

        $sectionTypes    = $report->reportType->sections->pluck('measurement_type')->unique();
        $needsAirSampler = $sectionTypes->contains('air_sampler');
        $needsInkubator  = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();
        $needsMedium     = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();

        $isEditable      = in_array($report->status, ['monitoring', 'reading'])
                           && $report->locked_by === auth()->id();
        $isMonitoringPhase = $report->status === 'monitoring';
        $myShift         = 1;

        // Build sectionInstances: expand sections with duplicate counts
        $sectionCounts    = $report->header_data['_section_counts'] ?? [];
        $sectionInstances = [];
        foreach ($report->reportType->sections as $section) {
            $count = (int) ($sectionCounts[$section->id] ?? 1);
            for ($i = 1; $i <= $count; $i++) {
                $sectionInstances[] = ['section' => $section, 'instance' => $i, 'totalInstances' => $count];
            }
        }

        $analis = User::where('role', 'analis')->orderBy('name')->get();
        // Analysts that can receive a handover (all analysts except current user)
        $otherAnalis = $analis->where('id', '!=', auth()->id())->values();

        return view('pages.laporan.isi', compact(
            'report', 'myShift', 'entryMap',
            'needsAirSampler', 'needsInkubator', 'needsMedium',
            'isEditable', 'isMonitoringPhase', 'analis', 'otherAnalis',
            'sectionInstances', 'returnedApproval'
        ));
    }

    public function lihat(Report $report)
    {
        $report->load(['reportType.sections.locations.room', 'entries', 'approvals.user', 'lockedByUser']);

        $entryMap = [];
        foreach ($report->entries as $entry) {
            $entryMap[$entry->report_section_id][$entry->instance_number ?? 1][$entry->period_number][$entry->shift] = $entry;
        }

        $sectionTypes    = $report->reportType->sections->pluck('measurement_type')->unique();
        $needsAirSampler = $sectionTypes->contains('air_sampler');
        $needsInkubator  = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();
        $needsMedium     = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();

        $isEditable        = false;
        $isMonitoringPhase = $report->status === 'monitoring';
        $myShift           = 1;

        $sectionCounts    = $report->header_data['_section_counts'] ?? [];
        $sectionInstances = [];
        foreach ($report->reportType->sections as $section) {
            $count = (int) ($sectionCounts[$section->id] ?? 1);
            for ($i = 1; $i <= $count; $i++) {
                $sectionInstances[] = ['section' => $section, 'instance' => $i, 'totalInstances' => $count];
            }
        }

        $analis      = User::where('role', 'analis')->orderBy('name')->get();
        $otherAnalis = $analis->where('id', '!=', auth()->id())->values();
        $isAdminPreview = auth()->user()->role === 'admin';

        return view('pages.laporan.isi', compact(
            'report', 'myShift', 'entryMap',
            'needsAirSampler', 'needsInkubator', 'needsMedium',
            'isEditable', 'isMonitoringPhase', 'analis', 'otherAnalis',
            'sectionInstances', 'isAdminPreview'
        ));
    }

    public function save(Request $request, Report $report)
    {
        abort_if(in_array($report->status, ['submitted', 'approved']), 403);
        abort_if($report->locked_by !== auth()->id(), 403);

        // Validate CFU values before processing — reject invalid inputs instead of silently discarding
        $entries = $request->input('entries', []);
        $cfuPattern = '/^(<1|TNTC|[1-9][0-9]*)$/i';
        $invalidFields = [];
        foreach ($entries as $sectionKey => $instanceMap) {
            if (!is_array($instanceMap)) continue;
            foreach ($instanceMap as $instanceKey => $periodMap) {
                if (!is_array($periodMap)) continue;
                foreach ($periodMap as $periodKey => $shiftMap) {
                    if (!is_array($shiftMap)) continue;
                    foreach ($shiftMap as $shiftKey => $data) {
                        if (!is_array($data)) continue;
                        foreach (['cfu_bacteria', 'cfu_fungi'] as $field) {
                            $v = trim((string) ($data[$field] ?? ''));
                            if ($v !== '' && !preg_match($cfuPattern, $v)) {
                                $invalidFields[] = "entries.{$sectionKey}.{$instanceKey}.{$periodKey}.{$shiftKey}.{$field}";
                            }
                        }
                    }
                }
            }
        }
        if (!empty($invalidFields)) {
            return back()
                ->withInput()
                ->withErrors(['cfu' => 'Terdapat ' . count($invalidFields) . ' nilai CFU tidak valid. Nilai yang diperbolehkan: bilangan bulat positif (misal: 1, 250), <1, atau TNTC. Nilai nol, desimal, dan negatif tidak diperbolehkan.']);
        }

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
        $savedSectionIds = [];

        $settleTimes = $request->input('settle_times', []);
        if (!empty($settleTimes)) {
            foreach ($settleTimes as $secId => $data) {
                $ownerKey = "settle_times_{$secId}";
                if (isset($owners[$ownerKey]) && (int) $owners[$ownerKey] !== Auth::id()) {
                    continue;
                }
                $hasVal = collect($data)->flatten()->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
                if ($hasVal) { $owners[$ownerKey] = Auth::id(); $savedSectionIds[(string) $secId] = true; }
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
                if ($hasVal) { $owners[$ownerKey] = Auth::id(); $savedSectionIds[(string) $secId] = true; }
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
                if ($hasVal) { $owners[$ownerKey] = Auth::id(); $savedSectionIds[(string) $secId] = true; }
                $hd['exposure_times'][$secId] = array_replace_recursive($hd['exposure_times'][$secId] ?? [], $data);
            }
            $hd['_field_owners'] = $owners;
        }
        if ($request->has('header_data') || !empty($shiftAssignment) || !empty($settleTimes) || !empty($swabTimes) || !empty($exposureTimes)) {
            $report->update(['header_data' => $hd]);
        }

        // Stamp per-analyst save timestamp (always — so each save records when this analyst worked)
        $freshHd = $report->fresh()->header_data ?? [];
        $tsKey = $report->status === 'reading' ? 'ttd_reading_timestamps' : 'ttd_monitoring_timestamps';
        $freshHd[$tsKey][(string) Auth::id()] = now()->toDateTimeString();
        $report->update(['header_data' => $freshHd]);

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
            ->map(fn ($e) => "{$e->report_section_id}-{$e->instance_number}-{$e->period_number}-{$e->shift}")
            ->toArray();

        // Upsert entries
        foreach ($request->input('entries', []) as $pivotId => $instances) {
            $sectionType = $pivotSectionType[(int) $pivotId] ?? null;
            if (! $sectionType) {
                continue;
            }

            $timeSlotType = $pivotSectionTimeSlot[(int) $pivotId] ?? 'none';
            $sectionId    = $pivotSectionId[(int) $pivotId] ?? null;

            foreach ($instances as $instanceNum => $cols) {
                $instanceNumber = max(1, (int) $instanceNum);

                foreach ($cols as $colIdx => $data) {
                    $periodNumber = (int) $colIdx;
                    $shift        = $myShift;

                    $hasData = collect($data)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
                    if (! $hasData) {
                        continue;
                    }

                    // Skip entries owned by another analyst
                    $entryKey = ((int) $pivotId) . "-{$instanceNumber}-{$periodNumber}-{$shift}";
                    if (in_array($entryKey, $lockedEntryKeys)) {
                        continue;
                    }

                    ReportEntry::updateOrCreate(
                        [
                            'report_id'          => $report->id,
                            'report_section_id'  => (int) $pivotId,
                            'instance_number'    => $instanceNumber,
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
                        'cfu_bacteria' => self::normalizeCfu($data['cfu_bacteria'] ?? null),
                        'cfu_fungi'    => self::normalizeCfu($data['cfu_fungi'] ?? null),
                    ]
                );
                    if ($sectionId) $savedSectionIds[(string) $sectionId] = true;
            }
        }
        }

        // Stamp per-section timestamps for every section this analyst saved entries in
        if (!empty($savedSectionIds)) {
            $sectHd   = $report->fresh()->header_data ?? [];
            $secTsKey = $report->status === 'reading' ? 'section_ttd_reading' : 'section_ttd_monitoring';
            foreach (array_keys($savedSectionIds) as $_sid) {
                $sectHd[$secTsKey][$_sid][(string) Auth::id()] = now()->toDateTimeString();
            }
            $report->update(['header_data' => $sectHd]);
        }
    }

    /**
     * Normalize a raw CFU input value.
     * Valid: '<1', 'TNTC', non-negative integer string.
     * Returns null if empty or invalid.
     */
    private static function normalizeCfu(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') return null;
        $v = trim((string) $raw);
        if ($v === '') return null;
        // Allow <1 and TNTC (case-insensitive)
        if ($v === '<1') return '<1';
        if (strtoupper($v) === 'TNTC') return 'TNTC';
        // Allow non-negative integers
        if (preg_match('/^[1-9][0-9]*$/', $v)) return $v;
        return null; // invalid — discard
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
