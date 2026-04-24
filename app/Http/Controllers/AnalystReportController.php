<?php

namespace App\Http\Controllers;

use App\Models\Analyst;
use App\Models\Report;
use App\Models\ReportEnvironmentalEntry;
use App\Models\User;
use App\Services\ReportSectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AnalystReportController extends Controller
{
    public function __construct(private ReportSectionService $sectionService) {}

    public function index(Request $request)
    {
        $status = $request->query('status', 'all');

        $rawCounts = Report::get(['status'])->groupBy('status')->map->count();

        $counts = collect([
            'pending' => $rawCounts['pending'] ?? 0,
            'monitoring' => $rawCounts['monitoring'] ?? 0,
            'reading' => $rawCounts['reading'] ?? 0,
            'submitted' => $rawCounts['submitted'] ?? 0,
            'returned' => $rawCounts['returned'] ?? 0,
            'approved' => $rawCounts['approved'] ?? 0,
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
            $report->update(['locked_by' => auth()->id()]);
            // Tambah ke tabel analysts jika belum ada
            Analyst::updateOrCreate([
                'report_id' => $report->id,
                'user_id' => auth()->id(),
                'type' => 'reading',
            ]);
            // Stamp per-analyst reading timestamp
            $hd = $report->fresh()->header_data ?? [];
            $hd['ttd_reading_timestamps'][(string) auth()->id()] = now()->toDateTimeString();
            $report->update(['header_data' => $hd]);
        }
        $report->refresh();
        $this->migrateFieldOwners($report);

        $report->load([
            'reportType.sections.locations.room',
            'reportType.sections.locations.frequency',
            'reportType.incubatorConfigs',
            'reportType.media',
            'environmentalEntries',
            'approvals.user',
            'lockedByUser',
            'instrumentIdentities',
            'mediumIdentities',
            'incubators.incubatedBy',
            'incubators.removedBy',
            'analysts.user',
            'signatures.user',
        ]);

        $entryMap        = $this->sectionService->buildEntryMap($report);
        $sectionNeeds    = $this->sectionService->computeSectionNeeds($report);
        $sectionInstances = $this->sectionService->buildSectionInstances($report);

        $needsAirSampler   = $sectionNeeds['needsAirSampler'];
        $needsInkubator    = $sectionNeeds['needsInkubator'];
        $needsMedium       = $sectionNeeds['needsMedium'];

        $isEditable        = in_array($report->status, ['monitoring', 'reading'])
                             && $report->locked_by === auth()->id();
        $isMonitoringPhase = $report->status === 'monitoring';
        $myShift           = 1;

        $instrument = $report->instrumentIdentities->first();
        $incubators = $report->incubators->keyBy('report_type_incubator_id');
        $incubatorConfigs = $report->reportType->incubatorConfigs;
        $mediums = $report->mediumIdentities->keyBy('name');
        $monitoringAnalysts = $report->analysts->where('type', 'monitoring');
        $readingAnalysts = $report->analysts->where('type', 'reading');

        // Group signatures by "section_id|instance_number" for fast lookup in the view
        $sectionSignatures = $report->signatures->groupBy(fn ($s) => "{$s->section_id}|{$s->instance_number}");

        $analis = User::where('role', 'analis')->orderBy('name')->get();
        // Analysts that can receive a handover (all analysts except current user)
        $otherAnalis = $analis->where('id', '!=', auth()->id())->values();

        // True when the report already went through step-2 (revision scenario)
        $isRevision = \App\Models\ReportApproval::where('report_id', $report->id)
            ->where('step', 2)
            ->exists();

        return view('pages.laporan.isi', compact(
            'report', 'myShift', 'entryMap',
            'needsAirSampler', 'needsInkubator', 'needsMedium',
            'isEditable', 'isMonitoringPhase', 'analis', 'otherAnalis',
            'sectionInstances', 'returnedApproval', 'isRevision',
            'instrument', 'incubators', 'incubatorConfigs', 'mediums',
            'monitoringAnalysts', 'readingAnalysts', 'sectionSignatures'
        ));
    }

    public function lihat(Report $report)
    {
        $report->load([
            'reportType.sections.locations.room',
            'reportType.sections.locations.frequency',
            'reportType.incubatorConfigs',
            'reportType.media',
            'environmentalEntries',
            'approvals.user',
            'lockedByUser',
            'instrumentIdentities',
            'mediumIdentities',
            'incubators.incubatedBy',
            'incubators.removedBy',
            'analysts.user',
            'signatures.user',
        ]);

        $entryMap         = $this->sectionService->buildEntryMap($report);
        $sectionNeeds     = $this->sectionService->computeSectionNeeds($report);
        $sectionInstances = $this->sectionService->buildSectionInstances($report);

        $needsAirSampler   = $sectionNeeds['needsAirSampler'];
        $needsInkubator    = $sectionNeeds['needsInkubator'];
        $needsMedium       = $sectionNeeds['needsMedium'];

        $isEditable        = false;
        $isMonitoringPhase = $report->status === 'monitoring';
        $myShift           = 1;

        $instrument = $report->instrumentIdentities->first();
        $incubators = $report->incubators->keyBy('report_type_incubator_id');
        $incubatorConfigs = $report->reportType->incubatorConfigs;
        $mediums = $report->mediumIdentities->keyBy('name');
        $monitoringAnalysts = $report->analysts->where('type', 'monitoring');
        $readingAnalysts = $report->analysts->where('type', 'reading');

        // Group signatures by "section_id|instance_number" for fast lookup in the view
        $sectionSignatures = $report->signatures->groupBy(fn ($s) => "{$s->section_id}|{$s->instance_number}");

        $analis = User::where('role', 'analis')->orderBy('name')->get();
        $otherAnalis = $analis->where('id', '!=', auth()->id())->values();
        $isAdminPreview = auth()->user()->role === 'admin';

        return view('pages.laporan.isi', compact(
            'report', 'myShift', 'entryMap',
            'needsAirSampler', 'needsInkubator', 'needsMedium',
            'isEditable', 'isMonitoringPhase', 'analis', 'otherAnalis',
            'sectionInstances', 'isAdminPreview',
            'instrument', 'incubators', 'incubatorConfigs', 'mediums',
            'monitoringAnalysts', 'readingAnalysts', 'sectionSignatures'
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
            if (! is_array($instanceMap)) {
                continue;
            }
            foreach ($instanceMap as $instanceKey => $periodMap) {
                if (! is_array($periodMap)) {
                    continue;
                }
                foreach ($periodMap as $periodKey => $shiftMap) {
                    if (! is_array($shiftMap)) {
                        continue;
                    }
                    foreach ($shiftMap as $shiftKey => $data) {
                        if (! is_array($data)) {
                            continue;
                        }
                        foreach (['cfu_bacteria', 'cfu_fungi'] as $field) {
                            $v = trim((string) ($data[$field] ?? ''));
                            if ($v !== '' && ! preg_match($cfuPattern, $v)) {
                                $invalidFields[] = "entries.{$sectionKey}.{$instanceKey}.{$periodKey}.{$shiftKey}.{$field}";
                            }
                        }
                    }
                }
            }
        }
        if (! empty($invalidFields)) {
            return back()
                ->withInput()
                ->withErrors(['cfu' => 'Terdapat '.count($invalidFields).' nilai CFU tidak valid. Nilai yang diperbolehkan: bilangan bulat positif (misal: 1, 250), <1, atau TNTC. Nilai nol, desimal, dan negatif tidak diperbolehkan.']);
        }

        $savedSectionIds = $this->processEntries($request, $report);
        $action = $request->input('action', 'save');

        // Record analyst participation in the analysts table (idempotent)
        $analystType = $report->status === 'reading' ? 'reading' : 'monitoring';
        Analyst::updateOrCreate([
            'report_id' => $report->id,
            'user_id'   => Auth::id(),
            'type'      => $analystType,
        ]);

        // Stamp per-section per-instance signatures only on final actions (not on plain draft saves)
        if ($action !== 'save' && ! empty($savedSectionIds)) {
            $role = $report->status === 'reading' ? 'reading' : 'monitoring';
            foreach (array_keys($savedSectionIds) as $_sidInst) {
                [$_secId, $_instNum] = explode('|', $_sidInst, 2);
                \App\Models\ReportSignature::updateOrCreate(
                    [
                        'report_id'       => $report->id,
                        'section_id'      => $_secId,
                        'instance_number' => (int) $_instNum,
                        'user_id'         => Auth::id(),
                        'role'            => $role,
                    ],
                    ['signed_at' => now()]
                );
            }
        }

        if ($action === 'submit') {
            abort_unless($report->status === 'reading', 403);
            $supervisorId = $request->input('supervisor_id');
            abort_if(empty($supervisorId), 422, 'Pilih supervisor terlebih dahulu.');
            abort_unless(
                User::where('id', $supervisorId)->where('role', 'supervisor')->exists(),
                422,
                'Supervisor tidak valid.'
            );

            $freshHd = $this->markAnalystSignaturesAsSigned($report->fresh()->header_data ?? [], $report);
            $report->update(['header_data' => $freshHd, 'status' => 'submitted', 'locked_by' => null]);

            \App\Models\ReportApproval::updateOrCreate(
                ['report_id' => $report->id, 'step' => 2],
                ['role' => 'Supervisor', 'user_id' => $supervisorId, 'status' => 'pending',
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

        if ($action === 'submit_revision') {
            abort_unless($report->status === 'monitoring', 403);
            // Must be a revision — step-2 approval must already exist
            $existingStep2 = \App\Models\ReportApproval::where('report_id', $report->id)
                ->where('step', 2)
                ->firstOrFail();

            $freshHd = $this->markAnalystSignaturesAsSigned($report->fresh()->header_data ?? [], $report);
            $report->update(['header_data' => $freshHd, 'status' => 'submitted', 'locked_by' => null]);

            $existingStep2->update([
                'status' => 'pending',
                'signed_at' => null,
                'notes' => null,
                'returned_to_user_id' => null,
            ]);

            return redirect()->route('laporan.index')
                ->with('success', 'Revisi berhasil dikirim ke supervisor.');
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

    public function duplicateSection(Report $report, $sectionId)
    {
        abort_unless(
            in_array($report->status, ['monitoring', 'reading']) && $report->locked_by === Auth::id(),
            403
        );
        abort_unless(
            $report->reportType->sections->contains('id', (int) $sectionId),
            404
        );

        $hd = $report->header_data ?? [];
        $counts = $hd['_section_counts'] ?? [];
        $current = (int) ($counts[$sectionId] ?? 1);

        if ($current >= 5) {
            return request()->wantsJson()
                ? response()->json(['ok' => false, 'message' => 'Maksimum 5 instance per seksi.'], 422)
                : back()->with('error', 'Maksimum 5 instance per seksi.');
        }

        $counts[(int) $sectionId] = $current + 1;
        $hd['_section_counts'] = $counts;
        $report->update(['header_data' => $hd]);

        return request()->wantsJson()
            ? response()->json(['ok' => true])
            : back()->with('success', 'Seksi berhasil diduplikat.');
    }

    public function removeSection(Report $report, $sectionId)
    {
        abort_unless(
            in_array($report->status, ['monitoring', 'reading']) && $report->locked_by === Auth::id(),
            403
        );
        abort_unless(
            $report->reportType->sections->contains('id', (int) $sectionId),
            404
        );

        $hd = $report->header_data ?? [];
        $counts = $hd['_section_counts'] ?? [];
        $current = (int) ($counts[$sectionId] ?? 1);

        if ($current <= 2) {
            unset($counts[(int) $sectionId]);
        } else {
            $counts[(int) $sectionId] = $current - 1;
        }

        if (empty($counts)) {
            unset($hd['_section_counts']);
        } else {
            $hd['_section_counts'] = $counts;
        }

        $report->update(['header_data' => empty($hd) ? null : $hd]);

        return request()->wantsJson()
            ? response()->json(['ok' => true])
            : back()->with('success', 'Duplikasi seksi berhasil dihapus.');
    }

    private function migrateFieldOwners(Report $report): void
    {
        $hd = $report->header_data ?? [];
        $owners = $hd['_field_owners'] ?? [];
        if (empty($owners)) {
            return;
        }
        $changed = false;
        foreach (array_keys($owners) as $k) {
            if (! str_contains((string) $k, '.')) {
                // Only migrate keys that are actual section-level arrays in $hd (old format).
                // Keys like incubator_{uuid}_in / settle_times_{uuid}_{n} are NOT in $hd, skip them.
                $sectionData = $hd[$k] ?? null;
                if (is_array($sectionData) && ! empty($sectionData)) {
                    foreach (array_keys($sectionData) as $fk) {
                        if (! isset($owners["{$k}.{$fk}"])) {
                            $owners["{$k}.{$fk}"] = $owners[$k];
                            $changed = true;
                        }
                    }
                    unset($owners[$k]);
                    $changed = true;
                }
            }
        }
        if ($changed) {
            $hd['_field_owners'] = $owners;
            $report->header_data = $hd;
            $report->saveQuietly();
        }
    }

    private function processEntries(Request $request, Report $report): array
    {
        $myShift = 1;

        // Save instrument identity (Air Sampler) ke tabel instrument_identities
        if ($request->has('air_sampler')) {
            $asData = $request->input('air_sampler', []);
            $report->instrumentIdentities()->updateOrCreate(
                ['tool_name' => $asData['tool_name'] ?? 'Air Sampler'],
                [
                    'no_id' => $asData['no_id'] ?? null ?: null,
                    'calibration_date' => $asData['calibration_date'] ?? null ?: null,
                    'due_date' => $asData['due_date'] ?? null ?: null,
                ]
            );
        }

        // Save medium identities ke tabel medium_identities
        if ($request->has('medium')) {
            $report->load('reportType.media');
            foreach ($request->input('medium', []) as $medKey => $data) {
                // Find the medium by name to get its ID
                $medium = $report->reportType->media->firstWhere('name', $medKey);
                if ($medium) {
                    $report->mediumIdentities()->updateOrCreate(
                        ['name' => $medKey],
                        [
                            'medium_id' => $medium->id,
                            'batch_number' => $data['batch_number'] ?? null ?: null,
                            'gpt_number' => $data['gpt_number'] ?? null ?: null,
                            'expiration_date' => $data['expiration_date'] ?? null ?: null,
                        ]
                    );
                }
            }
        }

        // Save incubators ke tabel incubators
        if ($request->has('incubator')) {
            $freshHdInk = $report->header_data ?? [];
            $inkOwners  = $freshHdInk['_field_owners'] ?? [];

            foreach ($request->input('incubator', []) as $tempKey => $data) {
                // "info" group: instrument identity (no_id, calibration, due date)
                $infoFields = array_filter([
                    'no_id'                => $data['no_id']                ?? null ?: null,
                    'calibration_date'     => $data['calibration_date']     ?? null ?: null,
                    'due_date_calibration' => $data['due_date_calibration'] ?? null ?: null,
                ]);
                // "in" group: incubation process (who put it in and when)
                $inFields  = array_filter([
                    'incubated_by' => $data['incubated_by'] ?? null ?: null,
                    'date_in'      => $data['date_in']      ?? null ?: null,
                    'time_in'      => $data['time_in']      ?? null ?: null,
                ]);
                $outFields = array_filter([
                    'removed_by' => $data['removed_by'] ?? null ?: null,
                    'date_out'   => $data['date_out']   ?? null ?: null,
                    'time_out'   => $data['time_out']   ?? null ?: null,
                ]);

                $ownerKeyInfo = "incubator_{$tempKey}_info";
                $ownerKeyIn   = "incubator_{$tempKey}_in";
                $ownerKeyOut  = "incubator_{$tempKey}_out";

                // "Info" group: skip if owned by someone else
                $infoLocked = isset($inkOwners[$ownerKeyInfo]) && (string) $inkOwners[$ownerKeyInfo] !== (string) Auth::id();
                if (! $infoLocked) {
                    if (! empty($infoFields)) {
                        $inkOwners[$ownerKeyInfo] = (string) Auth::id();
                    }
                } else {
                    $infoFields = [];
                }

                // "In" group: skip if owned by someone else
                $inLocked = isset($inkOwners[$ownerKeyIn]) && (string) $inkOwners[$ownerKeyIn] !== (string) Auth::id();
                if (! $inLocked) {
                    if (! empty($inFields)) {
                        $inkOwners[$ownerKeyIn] = (string) Auth::id();
                    }
                } else {
                    // Mask in-group fields so they don't overwrite owner's data
                    $inFields = [];
                }

                // "Out" group: skip if owned by someone else
                $outLocked = isset($inkOwners[$ownerKeyOut]) && (string) $inkOwners[$ownerKeyOut] !== (string) Auth::id();
                if (! $outLocked) {
                    if (! empty($outFields)) {
                        $inkOwners[$ownerKeyOut] = (string) Auth::id();
                    }
                } else {
                    $outFields = [];
                }

                $mergedData = array_merge($infoFields, $inFields, $outFields);
                if (! empty($mergedData)) {
                    $report->incubators()->updateOrCreate(
                        ['report_type_incubator_id' => $tempKey],
                        $mergedData
                    );
                }
            }

            // Persist updated owners back
            $freshHdInk['_field_owners'] = $inkOwners;
            $report->update(['header_data' => $freshHdInk]);
        }

        // Save header_data (analyst assignments + timing data)
        $hd = $report->header_data ?? [];
        $owners = $hd['_field_owners'] ?? [];
        // Migrate any old per-section ownership keys to per-field format.
        // Only migrate keys that actually exist as non-empty arrays in $hd (old format).
        // Keys like incubator_{uuid}_in or settle_times_{uuid}_{n} are NOT in $hd — skip them.
        foreach (array_keys($owners) as $k) {
            if (! str_contains((string) $k, '.')) {
                $sectionData = $hd[$k] ?? null;
                if (is_array($sectionData) && ! empty($sectionData)) {
                    foreach (array_keys($sectionData) as $fk) {
                        if (! isset($owners["{$k}.{$fk}"])) {
                            $owners["{$k}.{$fk}"] = $owners[$k];
                        }
                    }
                    unset($owners[$k]);
                }
            }
        }
        if ($request->has('header_data')) {
            $incoming = $request->input('header_data');
            // Remove ownership meta from incoming data
            unset($incoming['_field_owners']);
            foreach ($incoming as $sectionKey => $sectionData) {
                if (! is_array($sectionData)) {
                    $ownerKey = $sectionKey;
                    if (isset($owners[$ownerKey]) && (string) $owners[$ownerKey] !== (string) Auth::id()) {
                        continue;
                    }
                    if ($sectionData !== null && $sectionData !== '') {
                        $owners[$ownerKey] = (string) Auth::id();
                    }
                    $hd[$sectionKey] = $sectionData;

                    continue;
                }
                foreach ($sectionData as $fieldKey => $fieldValue) {
                    $ownerKey = "{$sectionKey}.{$fieldKey}";
                    if (isset($owners[$ownerKey]) && (string) $owners[$ownerKey] !== (string) Auth::id()) {
                        continue;
                    }
                    if ($fieldValue !== null && $fieldValue !== '') {
                        $owners[$ownerKey] = (string) Auth::id();
                    }
                    $hd[$sectionKey][$fieldKey] = $fieldValue;
                }
            }
            $hd['_field_owners'] = $owners;
        }

        // Save analysts ke tabel analysts (bukan lagi ke kolom JSON)
        if ($request->has('analyst_monitoring')) {
            $ids = array_values(array_filter((array) $request->input('analyst_monitoring')));
            foreach ($ids as $uid) {
                Analyst::updateOrCreate(
                    ['report_id' => $report->id, 'user_id' => $uid, 'type' => 'monitoring']
                );
            }
        }
        if ($request->has('analyst_reading')) {
            $ids = array_values(array_filter((array) $request->input('analyst_reading')));
            foreach ($ids as $uid) {
                Analyst::updateOrCreate(
                    ['report_id' => $report->id, 'user_id' => $uid, 'type' => 'reading']
                );
            }
        }

        $shiftAssignment = $request->input('shift_assignment', []);
        if (! empty($shiftAssignment)) {
            $existing = $hd['shift_assignments'] ?? [];
            foreach ($shiftAssignment as $secId => $cols) {
                $existing[$secId] = array_map('intval', $cols);
            }
            $hd['shift_assignments'] = $existing;
        }
        $savedSectionIds = [];

        // Build section → locations map EARLY so time fan-out can write to entries below.
        // Also builds the pivot-level maps reused in the entry upsert loop.
        $sectionLocations     = []; // sectionId (UUID) → [['pivot_id', 'class', 'location_number'], ...]
        $pivotSectionType     = [];
        $pivotSectionId       = [];
        $pivotSectionTimeSlot = [];
        $pivotLocationClass   = [];
        $pivotLocationNumber  = [];
        foreach ($report->reportType->sections()->with('locations.room')->get() as $_sec) {
            $sectionLocations[$_sec->id] = [];
            foreach ($_sec->locations as $_loc) {
                $pid = $_loc->pivot->id;
                $cls = strtolower($_loc->room->class ?? '');
                $num = $_loc->location_number ?? '';
                $sectionLocations[$_sec->id][] = ['pivot_id' => $pid, 'class' => $cls, 'location_number' => $num];
                $pivotSectionType[$pid]     = $_sec->measurement_type;
                $pivotSectionId[$pid]       = $_sec->id;
                $pivotSectionTimeSlot[$pid] = $_sec->time_slot_type;
                $pivotLocationClass[$pid]   = $cls;
                $pivotLocationNumber[$pid]  = $num;
            }
        }

        $settleTimes = $request->input('settle_times', []);
        if (! empty($settleTimes)) {
            foreach ($settleTimes as $secId => $instanceData) {
                if (! is_array($instanceData)) {
                    continue;
                }
                foreach ($instanceData as $instNum => $data) {
                    if (! is_array($data)) {
                        continue;
                    }
                    foreach ($data as $col => $abData) {
                        if (! is_array($abData)) {
                            continue;
                        }
                        $ownerKey = "settle_times_{$secId}_{$instNum}_{$col}";
                        if (isset($owners[$ownerKey]) && (string) $owners[$ownerKey] !== (string) Auth::id()) {
                            continue;
                        }
                        $hasVal = collect($abData)->flatten()->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
                        if ($hasVal) {
                            $owners[$ownerKey] = (string) Auth::id();
                            $savedSectionIds["{$secId}|{$instNum}"] = true;
                            // Fan out: each location gets the A or B slot matching its room class
                            foreach ($sectionLocations[$secId] ?? [] as $locInfo) {
                                $ab = $locInfo['class']; // 'a' or 'b'
                                $st = $abData[$ab] ?? [];
                                $startTime = ($st['start_time'] ?? '') ?: null;
                                $endTime   = ($st['end_time']   ?? '') ?: null;
                                if ($startTime === null && $endTime === null) {
                                    continue;
                                }
                                ReportEnvironmentalEntry::updateOrCreate(
                                    ['report_id' => $report->id, 'report_section_id' => $locInfo['pivot_id'], 'instance_number' => (int) $instNum, 'period_number' => (int) $col, 'shift' => $myShift],
                                    ['analyst_id' => Auth::id(), 'start_time' => $startTime, 'end_time' => $endTime]
                                );
                            }
                        }
                        $hd['settle_times'][$secId][$instNum][$col] = array_replace_recursive($hd['settle_times'][$secId][$instNum][$col] ?? [], $abData);
                    }
                }
            }
            $hd['_field_owners'] = $owners;
        }
        $swabTimes = $request->input('swab_times', []);
        if (! empty($swabTimes)) {
            foreach ($swabTimes as $secId => $instanceData) {
                if (! is_array($instanceData)) {
                    continue;
                }
                foreach ($instanceData as $instNum => $data) {
                    if (! is_array($data)) {
                        continue;
                    }
                    foreach ($data as $col => $slotData) {
                        if (! is_array($slotData)) {
                            continue;
                        }
                        $ownerKey = "swab_times_{$secId}_{$instNum}_{$col}";
                        if (isset($owners[$ownerKey]) && (string) $owners[$ownerKey] !== (string) Auth::id()) {
                            continue;
                        }
                        $hasVal = collect($slotData)->flatten()->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
                        if ($hasVal) {
                            $owners[$ownerKey] = (string) Auth::id();
                            $savedSectionIds["{$secId}|{$instNum}"] = true;
                            // Fan out: pick S1 / S1-2 / S1-3 slot by location_number
                            foreach ($sectionLocations[$secId] ?? [] as $locInfo) {
                                $locNum = $locInfo['location_number'];
                                if (stripos($locNum, 'S1-3') !== false) {
                                    $swabKey = 's1_3';
                                } elseif (stripos($locNum, 'S1-2') !== false) {
                                    $swabKey = 's1_2';
                                } else {
                                    $swabKey = 's1';
                                }
                                $st = $slotData[$swabKey] ?? [];
                                $startTime = ($st['mulai']   ?? '') ?: null;
                                $endTime   = ($st['selesai'] ?? '') ?: null;
                                if ($startTime === null && $endTime === null) {
                                    continue;
                                }
                                ReportEnvironmentalEntry::updateOrCreate(
                                    ['report_id' => $report->id, 'report_section_id' => $locInfo['pivot_id'], 'instance_number' => (int) $instNum, 'period_number' => (int) $col, 'shift' => $myShift],
                                    ['analyst_id' => Auth::id(), 'start_time' => $startTime, 'end_time' => $endTime]
                                );
                            }
                        }
                        $hd['swab_times'][$secId][$instNum][$col] = array_replace_recursive($hd['swab_times'][$secId][$instNum][$col] ?? [], $slotData);
                    }
                }
            }
            $hd['_field_owners'] = $owners;
        }
        $exposureTimes = $request->input('exposure_times', []);
        if (! empty($exposureTimes)) {
            foreach ($exposureTimes as $secId => $instanceData) {
                if (! is_array($instanceData)) {
                    continue;
                }
                foreach ($instanceData as $instNum => $data) {
                    if (! is_array($data)) {
                        continue;
                    }
                    foreach ($data as $col => $times) {
                        if (! is_array($times)) {
                            continue;
                        }
                        $ownerKey = "exposure_times_{$secId}_{$instNum}_{$col}";
                        if (isset($owners[$ownerKey]) && (string) $owners[$ownerKey] !== (string) Auth::id()) {
                            continue;
                        }
                        $hasVal = collect($times)->flatten()->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
                        if ($hasVal) {
                            $owners[$ownerKey] = (string) Auth::id();
                            $savedSectionIds["{$secId}|{$instNum}"] = true;
                            // Fan out: same start/end for ALL locations in this section for this column
                            foreach ($sectionLocations[$secId] ?? [] as $locInfo) {
                                $startTime = ($times['start_time'] ?? '') ?: null;
                                $endTime   = ($times['end_time']   ?? '') ?: null;
                                if ($startTime === null && $endTime === null) {
                                    continue;
                                }
                                ReportEnvironmentalEntry::updateOrCreate(
                                    ['report_id' => $report->id, 'report_section_id' => $locInfo['pivot_id'], 'instance_number' => (int) $instNum, 'period_number' => (int) $col, 'shift' => $myShift],
                                    ['analyst_id' => Auth::id(), 'start_time' => $startTime, 'end_time' => $endTime]
                                );
                            }
                        }
                        $hd['exposure_times'][$secId][$instNum][$col] = array_replace_recursive($hd['exposure_times'][$secId][$instNum][$col] ?? [], $times);
                    }
                }
            }
            $hd['_field_owners'] = $owners;
        }
        if ($request->has('header_data') || ! empty($shiftAssignment) || ! empty($settleTimes) || ! empty($swabTimes) || ! empty($exposureTimes)) {
            $report->update(['header_data' => $hd]);
        }

        // Stamp per-analyst save timestamp (always — so each save records when this analyst worked)
        $freshHd = $report->fresh()->header_data ?? [];
        $tsKey = $report->status === 'reading' ? 'ttd_reading_timestamps' : 'ttd_monitoring_timestamps';
        $freshHd[$tsKey][(string) Auth::id()] = now()->toDateTimeString();
        $report->update(['header_data' => $freshHd]);

        // Pre-load entries owned by other analysts — CFU must not be overwritten
        $lockedEntryKeys = ReportEnvironmentalEntry::where('report_id', $report->id)
            ->where('analyst_id', '!=', Auth::id())
            ->whereNotNull('analyst_id')
            ->where(function ($q) {
                $q->whereNotNull('cfu_bacteria')->orWhereNotNull('cfu_fungi');
            })
            ->get()
            ->map(fn ($e) => "{$e->report_section_id}-{$e->instance_number}-{$e->period_number}-{$e->shift}")
            ->toArray();

        // Upsert entries with CFU (and per_location time — other types already written by fan-out above)
        foreach ($request->input('entries', []) as $pivotId => $instances) {
            $sectionType = $pivotSectionType[(string) $pivotId] ?? null;
            if (! $sectionType) {
                continue;
            }

            $timeSlotType = $pivotSectionTimeSlot[(string) $pivotId] ?? 'none';
            $sectionId    = $pivotSectionId[(string) $pivotId] ?? null;

            foreach ($instances as $instanceNum => $cols) {
                $instanceNumber = max(1, (int) $instanceNum);

                foreach ($cols as $colIdx => $data) {
                    $periodNumber = (int) $colIdx;
                    $shift        = $myShift;

                    $hasCfuData = (($data['cfu_bacteria'] ?? '') !== '' && ($data['cfu_bacteria'] ?? null) !== null)
                               || (($data['cfu_fungi']    ?? '') !== '' && ($data['cfu_fungi']    ?? null) !== null);

                    // For per_location, also save when there is a start_time (even without CFU)
                    $hasPerLocTime = ($timeSlotType === 'per_location') && (($data['start_time'] ?? '') !== '');

                    if (! $hasCfuData && ! $hasPerLocTime) {
                        continue;
                    }

                    // CFU protection: skip if another analyst already owns this entry's data
                    $entryKey = ((string) $pivotId)."-{$instanceNumber}-{$periodNumber}-{$shift}";
                    if ($hasCfuData && in_array($entryKey, $lockedEntryKeys)) {
                        continue;
                    }

                    $updateValues = [];
                    if ($hasCfuData) {
                        $updateValues['analyst_id']   = Auth::id();
                        $updateValues['cfu_bacteria'] = self::normalizeCfu($data['cfu_bacteria'] ?? null);
                        $updateValues['cfu_fungi']    = self::normalizeCfu($data['cfu_fungi']    ?? null);
                    }
                    // per_location: time comes directly from the row input
                    if ($hasPerLocTime) {
                        $updateValues['start_time'] = $data['start_time'];
                        $updateValues['end_time']   = null;
                        // analyst_id required on insert; set if not already set by CFU block
                        if (!isset($updateValues['analyst_id'])) {
                            $updateValues['analyst_id'] = Auth::id();
                        }
                    }

                    if (empty($updateValues)) {
                        continue;
                    }

                    ReportEnvironmentalEntry::updateOrCreate(
                        [
                            'report_id'         => $report->id,
                            'report_section_id' => (string) $pivotId,
                            'instance_number'   => $instanceNumber,
                            'period_number'     => $periodNumber,
                            'shift'             => $shift,
                        ],
                        $updateValues
                    );
                    if ($sectionId) {
                        $savedSectionIds["{$sectionId}|{$instanceNumber}"] = true;
                    }
                }
            }
        }

        return $savedSectionIds;
    }

    /**
     * Normalize a raw CFU input value.
     * Valid: '<1', 'TNTC', non-negative integer string.
     * Returns null if empty or invalid.
     */
    private static function normalizeCfu(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        $v = trim((string) $raw);
        if ($v === '') {
            return null;
        }
        // Allow <1 and TNTC (case-insensitive)
        if ($v === '<1') {
            return '<1';
        }
        if (strtoupper($v) === 'TNTC') {
            return 'TNTC';
        }
        // Allow non-negative integers
        if (preg_match('/^[1-9][0-9]*$/', $v)) {
            return $v;
        }

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
        if ($user->username !== $request->username || ! Hash::check($request->password, $user->password)) {
            return response()->json(['ok' => false, 'message' => 'Username atau password salah.'], 422);
        }

        return response()->json(['ok' => true]);
    }

    private function markAnalystSignaturesAsSigned(array $headerData, Report $report): array
    {
        $monitoringUser = Analyst::where('report_id', $report->id)->where('type', 'monitoring')->first();
        $readingUser = Analyst::where('report_id', $report->id)->where('type', 'reading')->first();

        $headerData['ttd_monitoring_id'] = $monitoringUser?->user_id;
        $headerData['ttd_dibaca_id'] = $readingUser?->user_id;

        $signedAt = now()->toDateTimeString();
        $headerData['ttd_monitoring_signed_at'] = $signedAt;
        $headerData['ttd_dibaca_signed_at'] = $signedAt;

        return $headerData;
    }
}
