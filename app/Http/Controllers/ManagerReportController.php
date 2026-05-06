<?php

namespace App\Http\Controllers;

use App\Models\PersonnelInstance;
use App\Models\PersonnelRow;
use App\Models\Report;
use App\Models\ReportApproval;
use App\Services\Reports\ReportEntryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ManagerReportController extends Controller
{
    public function dashboard()
    {
        $userId = Auth::id();

        $pending = $this->baseQuery($userId)->where('report_approvals.status', 'pending')->count();
        $approved = $this->baseQuery($userId)->where('report_approvals.status', 'approved')->count();
        $returned = $this->baseQuery($userId)->whereIn('report_approvals.status', ['returned', 'rejected'])->count();

        return view('pages.manajer.index', compact('pending', 'approved', 'returned'));
    }

    public function laporanMasuk(Request $request)
    {
        $userId = Auth::id();
        $tab = $request->query('tab', 'pending');
        if ($tab === 'rejected') {
            $tab = 'returned';
        }

        $counts = [
            'pending' => $this->baseQuery($userId)->where('report_approvals.status', 'pending')->count(),
            'approved' => $this->baseQuery($userId)->where('report_approvals.status', 'approved')->count(),
            'returned' => $this->baseQuery($userId)->whereIn('report_approvals.status', ['returned', 'rejected'])->count(),
        ];

        $reports = Report::with(['reportType', 'approvals.returnedTo', 'analysts.user'])
            ->join('report_approvals', 'reports.id', '=', 'report_approvals.report_id')
            ->where('report_approvals.step', 3)
            ->where('report_approvals.user_id', $userId)
            ->when(
                $tab === 'returned',
                fn ($query) => $query->whereIn('report_approvals.status', ['returned', 'rejected']),
                fn ($query) => $query->where('report_approvals.status', $tab)
            )
            ->select('reports.*', 'report_approvals.status as approval_status', 'report_approvals.id as approval_id')
            ->orderByDesc('reports.created_at')
            ->paginate(15)
            ->withQueryString();

        return view('pages.manajer.laporan-masuk', compact('reports', 'counts', 'tab'));
    }

    public function laporanSedangDikerjakan(Request $request)
    {
        $status = $request->query('status', 'all');
        $validStatuses = ['all', 'pending', 'monitoring', 'reading', 'review_supervisor', 'waiting_manager'];
        if (! in_array($status, $validStatuses, true)) {
            $status = 'all';
        }

        $countKeys = ['all', 'pending', 'monitoring', 'reading', 'review_supervisor', 'waiting_manager'];
        $counts = [];
        foreach ($countKeys as $key) {
            $countQuery = $this->progressBaseQuery();
            $this->applyProgressStatusFilter($countQuery, $key);
            $counts[$key] = $countQuery->count();
        }

        $reportsQuery = $this->progressBaseQuery()
            ->with(['reportType', 'lockedByUser', 'analysts.user', 'approvals.user']);
        $this->applyProgressStatusFilter($reportsQuery, $status);

        $reports = $reportsQuery
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('pages.manajer.laporan-sedang-dikerjakan', compact('reports', 'counts', 'status'));
    }

    public function show(Report $report)
    {
        $userId = Auth::id();
        $approval = ReportApproval::where('report_id', $report->id)
            ->where('step', 3)
            ->where('user_id', $userId)
            ->firstOrFail();

        $report->load([
            'reportType.sections.locations.room',
            'reportType.mediumTypes',
            'reportType.personnelMethods.activities',
            'reportType.personnelMethods.samplingPoints',
            'reportType.personnelMethods.limits',
            'environmentalEntries',
            'sectionColumnNames',
            'approvals.user',
            'analysts.user',
            'signatures',
            'mediumIdentities',
            'instrumentEntries',
            'incubators',
            'personnelInstances.rows.samplingEntries',
            'personnelSignatures.user',
        ]);

        // Supervisor (step 2 user) for the return dropdown
        $supervisorApproval = $report->approvals->firstWhere('step', 2);
        $returnSupervisor = $supervisorApproval?->user;

        $sectionService = app(\App\Services\ReportSectionService::class);
        $entryMap = $sectionService->buildEntryMap($report);
        $sectionNeeds = $sectionService->computeSectionNeeds($report);

        $needsAirSampler = $sectionNeeds['needsAirSampler'];
        $needsInkubator = $sectionNeeds['needsInkubator'];
        $needsMedium = $sectionNeeds['needsMedium'];

        $reviewRole = 'manajer';

        return view('pages.review.laporan-show', compact(
            'report', 'approval', 'entryMap', 'returnSupervisor',
            'needsAirSampler', 'needsInkubator', 'needsMedium',
            'reviewRole'
        ));
    }

    public function approve(Request $request, Report $report)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = Auth::user();
        if ($user->username !== $request->username || ! Hash::check($request->password, $user->password)) {
            return back()
                ->withErrors(['auth_error' => 'Username atau password tidak valid.'])
                ->withInput($request->except('password'));
        }

        $userId = Auth::id();
        $approval = ReportApproval::where('report_id', $report->id)
            ->where('step', 3)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->firstOrFail();

        $signedAt = now();
        $approval->update([
            'status' => 'approved',
            'signed_at' => $signedAt,
        ]);

        // Stamp per-section manager TTD
        $headerData = $report->header_data ?? [];
        $signedAtStr = $signedAt->toDateTimeString();
        $report->loadMissing('reportType.sections');
        foreach ($report->reportType->sections as $sec) {
            $headerData['section_ttd_manager'][(string) $sec->id][(string) $userId] = $signedAtStr;
        }

        // Snapshot report type structure so archived reports are immutable
        $headerData['_snapshot_section_ids'] = $report->reportType->sections->pluck('id')->toArray();
        $headerData['_snapshot_report_type'] = [
            'annex_number' => $report->reportType->annex_number,
            'name' => $report->reportType->name,
            'medium_groups' => $report->reportType->medium_groups,
        ];

        $report->update(['header_data' => $headerData]);

        $report->update(['status' => 'approved']);

        return redirect()->route('manajer.laporan-masuk')
            ->with('success', 'Laporan berhasil disetujui.');
    }

    public function returnReport(Request $request, Report $report)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = Auth::user();
        if ($user->username !== $request->username || ! Hash::check($request->password, $user->password)) {
            return back()
                ->withErrors(['auth_error' => 'Username atau password tidak valid.'])
                ->withInput($request->except('password'));
        }

        $userId = Auth::id();
        $approval = ReportApproval::where('report_id', $report->id)
            ->where('step', 3)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->firstOrFail();

        $returnedToUserId = $request->input('returned_to_user_id');

        $supervisorApproval = ReportApproval::where('report_id', $report->id)
            ->where('step', 2)
            ->first();

        $allowedAnalysts = array_merge(
            $report->analyst_monitoring ?? [],
            $report->analyst_reading ?? []
        );

        $isToSupervisor = $supervisorApproval && $supervisorApproval->user_id === $returnedToUserId;
        $isToAnalyst = in_array($returnedToUserId, $allowedAnalysts);

        abort_unless($isToSupervisor || $isToAnalyst, 422, 'Penerima pengembalian tidak valid.');

        $approval->update([
            'status' => 'returned',
            'notes' => $request->input('notes'),
            'returned_to_user_id' => $returnedToUserId,
        ]);

        if ($isToAnalyst) {
            // Clear per-section TTDs so all roles re-sign from scratch
            $hd = $report->header_data ?? [];
            unset(
                $hd['section_ttd_monitoring'],
                $hd['section_ttd_reading'],
                $hd['section_ttd_supervisor'],
                $hd['section_ttd_manager'],
                $hd['ttd_monitoring_signed_at'],
                $hd['ttd_dibaca_signed_at']
            );

            $analystApproval = ReportApproval::where('report_id', $report->id)
                ->where('step', 1)
                ->first();
            if ($analystApproval) {
                $analystApproval->update(['status' => 'pending', 'signed_at' => null]);
            }
            if ($supervisorApproval) {
                $supervisorApproval->update(['status' => 'pending', 'signed_at' => null]);
            }

            $report->update(['status' => 'returned', 'locked_by' => null, 'header_data' => $hd]);

            return redirect()->route('manajer.laporan-masuk')
                ->with('success', 'Laporan telah dikembalikan ke Analis.');
        }

        // Return to supervisor
        $supervisorApproval->update(['status' => 'pending', 'signed_at' => null]);
        $report->update(['status' => 'returned_to_supervisor']);

        return redirect()->route('manajer.laporan-masuk')
            ->with('success', 'Laporan telah dikembalikan ke Supervisor.');
    }

    public function save(Request $request, Report $report)
    {
        $userId = Auth::id();
        ReportApproval::where('report_id', $report->id)
            ->where('step', 3)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->firstOrFail();

        // Handle personnel page actions (add/remove)
        $personnelAction = $request->input('_personnel_action');
        if ($personnelAction === 'add_page' || str_starts_with((string) $personnelAction, 'remove_page_')) {
            $svc = app(\App\Services\PersonnelInstanceService::class);
            if ($personnelAction === 'add_page') {
                $result = $svc->addPage($report);
            } else {
                $pageNum = (int) str_replace('remove_page_', '', $personnelAction);
                $result = $svc->removePage($report, $pageNum);
            }
            return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
        }

        $entryService = app(ReportEntryService::class);

        // Identitas instrumen (Air Sampler) → instrument_entries
        $asData = $request->input('header_data.air_sampler');
        if (is_array($asData)) {
            $entryService->saveInstrumentFromArray($asData, $report);
        }

        // Identitas medium agar → medium_identities
        if ($request->has('medium')) {
            $report->loadMissing('reportType.mediumTypes');
            foreach ($request->input('medium', []) as $medName => $data) {
                $medium = $report->reportType->mediumTypes->firstWhere('name', $medName);
                if ($medium) {
                    $report->mediumIdentities()->updateOrCreate(
                        ['name' => $medName],
                        [
                            'medium_id'       => $medium->id,
                            'batch_number'    => $data['batch_number']    ?? null ?: null,
                            'gpt_number'      => $data['gpt_number']      ?? null ?: null,
                            'expiration_date' => $data['expiration_date'] ?? null ?: null,
                        ]
                    );
                }
            }
        }

        // Data inkubator info → incubators (form: incubator[<report_type_incubator_id>][field])
        // Data in/out medium disimpan di incubator_entries.
        $report->loadMissing('reportType.incubatorTypes');
        foreach ($request->input('incubator', []) as $rtiId => $inkData) {
            $rti = $report->reportType->incubatorTypes->firstWhere('id', $rtiId);
            if ($rti && is_array($inkData)) {
                $incubator = $report->incubators()->updateOrCreate(
                    ['report_type_incubator_id' => $rti->id],
                    [
                        'no_id'                => $inkData['no_id'] ?? null ?: null,
                        'calibration_date'     => $inkData['calibration_date'] ?? null ?: null,
                        'due_date_calibration' => $inkData['due_date_calibration'] ?? ($inkData['due_date'] ?? null) ?: null,
                    ]
                );

                $entryPayloads = [];
                foreach ($inkData as $mediumType => $entryData) {
                    if (is_array($entryData) && in_array((string) $mediumType, ['monitoring', 'swab'], true)) {
                        $entryPayloads[$mediumType] = $entryData;
                    }
                }
                if (empty($entryPayloads) && (
                    isset($inkData['incubated_by']) || isset($inkData['date_in']) || isset($inkData['time_in']) ||
                    isset($inkData['removed_by']) || isset($inkData['date_out']) || isset($inkData['time_out'])
                )) {
                    $entryPayloads['monitoring'] = [
                        'incubated_by' => $inkData['incubated_by'] ?? null,
                        'date_in'      => $inkData['date_in'] ?? null,
                        'time_in'      => $inkData['time_in'] ?? null,
                        'removed_by'   => $inkData['removed_by'] ?? null,
                        'date_out'     => $inkData['date_out'] ?? null,
                        'time_out'     => $inkData['time_out'] ?? null,
                    ];
                }

                foreach ($entryPayloads as $mediumType => $entryData) {
                    $incubator->entries()->updateOrCreate(
                        ['medium_type' => $mediumType],
                        [
                            'incubated_by' => $entryData['incubated_by'] ?? null ?: null,
                            'date_in'      => $entryData['date_in'] ?? null ?: null,
                            'time_in'      => $entryData['time_in'] ?? null ?: null,
                            'removed_by'   => $entryData['removed_by'] ?? null ?: null,
                            'date_out'     => $entryData['date_out'] ?? null ?: null,
                            'time_out'     => $entryData['time_out'] ?? null ?: null,
                        ]
                    );
                }
            }
        }

        // Waktu paparan → report_environmental_entries (start_time/end_time)
        $entryService->saveReviewTimesToEntries(
            $request->input('settle_times',   []),
            $request->input('swab_times',     []),
            $request->input('exposure_times', []),
            $report
        );

        // Waktu monitoring personel → personnel_rows
        $this->savePersonnelMonitoringTimes($request, $report);

        return back()->with('success', 'Data berhasil disimpan.');
    }

    private function savePersonnelMonitoringTimes(Request $request, Report $report): void
    {
        $personnelPayload = $request->input('personnel', []);
        if (! is_array($personnelPayload) || empty($personnelPayload)) {
            return;
        }

        foreach ($personnelPayload as $instanceId => $instanceData) {
            if (! is_array($instanceData) || str_starts_with((string) $instanceId, '_new_')) {
                continue;
            }

            $instance = PersonnelInstance::where('id', $instanceId)
                ->where('report_id', $report->id)
                ->first();

            if (! $instance) {
                continue;
            }

            foreach (($instanceData['row'] ?? []) as $rowOrder => $rowData) {
                if (! is_array($rowData) || ! array_key_exists('time', $rowData)) {
                    continue;
                }

                $row = PersonnelRow::where('personnel_instance_id', $instance->id)
                    ->where('row_order', (int) $rowOrder)
                    ->first();

                if (! $row) {
                    continue;
                }

                $time = trim((string) ($rowData['time'] ?? ''));
                $row->monitoring_time = $time !== '' ? $time : null;
                $row->save();
            }
        }
    }

    public function cetak(Report $report)
    {
        $userId = Auth::id();
        ReportApproval::where('report_id', $report->id)
            ->where('step', 3)
            ->where('user_id', $userId)
            ->firstOrFail();

        $report->forceFill([
            'printed_at' => now(),
            'printed_by' => $userId,
        ])->save();

        $report->load([
            'reportType.sections.locations.room',
            'reportType.mediumTypes',
            'reportType.incubatorTypes',
            'reportType.personnelMethods.activities',
            'reportType.personnelMethods.samplingPoints',
            'reportType.personnelMethods.limits',
            'environmentalEntries.envSectionInstance',
            'approvals.user',
            'sectionColumnNames',
            'instrumentEntries',
            'mediumIdentities',
            'incubators.entries.incubatedBy',
            'incubators.entries.removedBy',
            'personnelInstances.rows.samplingEntries',
            'personnelSignatures.user',
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

        return view('pages.supervisor.laporan-cetak', compact(
            'report', 'entryMap', 'needsAirSampler', 'needsInkubator', 'needsMedium'
        ));
    }

    private function progressBaseQuery()
    {
        return Report::query()
            ->whereDoesntHave('approvals', function ($query) {
                $query->where('step', 3)->where('status', 'approved');
            });
    }

    private function applyProgressStatusFilter($query, string $status): void
    {
        if ($status === 'all') {
            $query->where(function ($nested) {
                $nested->whereIn('status', ['pending', 'monitoring', 'reading'])
                    ->orWhereHas('approvals', function ($approvalQuery) {
                        $approvalQuery->where('step', 2)->where('status', 'pending');
                    })
                    ->orWhereHas('approvals', function ($approvalQuery) {
                        $approvalQuery->where('step', 3)->where('status', 'pending');
                    });
            });
            return;
        }

        if (in_array($status, ['pending', 'monitoring', 'reading'], true)) {
            $query->where('status', $status);
            return;
        }

        if ($status === 'review_supervisor') {
            $query->whereHas('approvals', function ($approvalQuery) {
                $approvalQuery->where('step', 2)->where('status', 'pending');
            });
            return;
        }

        if ($status === 'waiting_manager') {
            $query->whereHas('approvals', function ($approvalQuery) {
                $approvalQuery->where('step', 3)->where('status', 'pending');
            });
            return;
        }

        $query->whereRaw('1 = 0');
    }

    private function baseQuery(string $userId)
    {
        return Report::join('report_approvals', 'reports.id', '=', 'report_approvals.report_id')
            ->where('report_approvals.step', 3)
            ->where('report_approvals.user_id', $userId);
    }
}
