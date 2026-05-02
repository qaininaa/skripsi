<?php

namespace App\Http\Controllers;

use App\Models\PersonnelInstance;
use App\Models\PersonnelRow;
use App\Models\Report;
use App\Models\ReportApproval;
use App\Models\User;
use App\Services\Reports\ReportEntryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SupervisorReportController extends Controller
{
    public function dashboard()
    {
        $userId = Auth::id();

        $pending = $this->baseQuery($userId)->where('report_approvals.status', 'pending')->count();
        $approved = $this->baseQuery($userId)->where('report_approvals.status', 'approved')->count();
        $returned = $this->baseQuery($userId)->whereIn('report_approvals.status', ['returned', 'rejected'])->count();

        $counts = Report::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $pendingReports = Report::with('reportType', 'lockedByUser')
            ->where('status', 'pending')
            ->latest()->take(5)->get();
        $monitoringReports = Report::with('reportType', 'lockedByUser')
            ->where('status', 'monitoring')
            ->latest()->take(5)->get();
        $readingReports = Report::with('reportType', 'lockedByUser')
            ->where('status', 'reading')
            ->latest()->take(5)->get();

        return view('pages.supervisor.index', compact(
            'pending', 'approved', 'returned',
            'counts', 'pendingReports', 'monitoringReports', 'readingReports'
        ));
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
            ->where('report_approvals.step', 2)
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

        return view('pages.supervisor.laporan-masuk', compact('reports', 'counts', 'tab'));
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

        return view('pages.supervisor.laporan-sedang-dikerjakan', compact('reports', 'counts', 'status'));
    }

    public function show(Report $report)
    {
        $userId = Auth::id();
        $approval = ReportApproval::where('report_id', $report->id)
            ->where('step', 2)
            ->where('user_id', $userId)
            ->firstOrFail();

        $report->load([
            'reportType.sections.locations.room',
            'reportType.media',
            'reportType.personnelMethods.activities',
            'reportType.personnelMethods.samplingPoints',
            'reportType.personnelMethods.limits',
            'environmentalEntries',
            'sectionColumnNames',
            'approvals.user',
            'analysts.user',
            'signatures',
            'mediumIdentities',
            'instrumentIdentities',
            'incubators',
            'personnelInstances.rows.samplingEntries',
            'personnelSignatures.user',
        ]);

        $sectionService = app(\App\Services\ReportSectionService::class);
        $entryMap = $sectionService->buildEntryMap($report);
        $sectionNeeds = $sectionService->computeSectionNeeds($report);

        $needsAirSampler = $sectionNeeds['needsAirSampler'];
        $needsInkubator = $sectionNeeds['needsInkubator'];
        $needsMedium = $sectionNeeds['needsMedium'];

        $reviewRole = 'supervisor';
        $returnSupervisor = null;

        return view('pages.review.laporan-show', compact(
            'report', 'approval', 'entryMap',
            'needsAirSampler', 'needsInkubator', 'needsMedium',
            'reviewRole', 'returnSupervisor'
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
            ->where('step', 2)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->firstOrFail();

        $signedAt = now();
        $approval->update([
            'status' => 'approved',
            'signed_at' => $signedAt,
        ]);

        // Stamp per-section supervisor TTD
        $headerData = $report->header_data ?? [];
        $signedAtStr = $signedAt->toDateTimeString();
        $report->loadMissing('reportType.sections');
        foreach ($report->reportType->sections as $sec) {
            $headerData['section_ttd_supervisor'][(string) $sec->id][(string) $userId] = $signedAtStr;
        }
        $report->update(['header_data' => $headerData]);

        // Create or reset step 3 approval for manajer
        $manager = User::where('role', 'manajer')->first();
        if ($manager) {
            ReportApproval::updateOrCreate(
                ['report_id' => $report->id, 'step' => 3],
                [
                    'role' => 'manajer',
                    'user_id' => $manager->id,
                    'status' => 'pending',
                    'signed_at' => null,
                    'notes' => null,
                    'returned_to_user_id' => null,
                ]
            );
            $report->update(['status' => 'pending_manager']);
        } else {
            $report->update(['status' => 'approved']);
        }

        return redirect()->route('supervisor.laporan-masuk')
            ->with('success', 'Laporan berhasil disetujui dan dikirim ke Manajer.');
    }

    public function returnReport(Request $request, Report $report)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'returned_to_user_id' => 'required|uuid|exists:users,id',
        ]);

        $user = Auth::user();
        if ($user->username !== $request->username || ! Hash::check($request->password, $user->password)) {
            return back()
                ->withErrors(['auth_error' => 'Username atau password tidak valid.'])
                ->withInput($request->except('password'));
        }

        $userId = Auth::id();
        $approval = ReportApproval::where('report_id', $report->id)
            ->where('step', 2)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->firstOrFail();

        $returnedToUserId = (string) $request->input('returned_to_user_id');
        $allowedUsers = $report->analysts()
            ->pluck('user_id')
            ->map(fn ($id) => (string) $id)
            ->toArray();
        abort_unless(
            in_array($returnedToUserId, $allowedUsers, true),
            422,
            'Analis tujuan tidak valid.'
        );

        $approval->update([
            'status' => 'returned',
            'notes' => $request->input('notes'),
            'returned_to_user_id' => $returnedToUserId,
        ]);

        // Reset signature timestamps — clear all per-section and legacy TTD keys
        $hd = $report->header_data ?? [];
        unset(
            $hd['section_ttd_monitoring'],
            $hd['section_ttd_reading'],
            $hd['section_ttd_supervisor'],
            $hd['ttd_monitoring_signed_at'],
            $hd['ttd_dibaca_signed_at']
        );
        $report->update(['status' => 'returned', 'locked_by' => null, 'header_data' => $hd]);

        return redirect()->route('supervisor.laporan-masuk')
            ->with('success', 'Laporan telah dikembalikan ke analis.');
    }

    public function save(Request $request, Report $report)
    {
        $userId = Auth::id();
        ReportApproval::where('report_id', $report->id)
            ->where('step', 2)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->firstOrFail();

        // Handle personnel page actions (add/remove)
        $personnelAction = $request->input('_personnel_action');
        if ($personnelAction === 'add_page' || str_starts_with((string) $personnelAction, 'remove_page_')) {
            $svc = app(\App\Services\Reports\Sections\PersonnelInstanceService::class);
            if ($personnelAction === 'add_page') {
                $result = $svc->addPage($report);
            } else {
                $pageNum = (int) str_replace('remove_page_', '', $personnelAction);
                $result = $svc->removePage($report, $pageNum);
            }
            return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
        }

        $entryService = app(ReportEntryService::class);

        // Identitas instrumen (Air Sampler) → instrument_identities
        $asData = $request->input('header_data.air_sampler');
        if (is_array($asData)) {
            $entryService->saveInstrumentFromArray($asData, $report);
        }

        // Identitas medium agar → medium_identities
        if ($request->has('medium')) {
            $report->load('reportType.media');
            foreach ($request->input('medium', []) as $medName => $data) {
                $medium = $report->reportType->media->firstWhere('name', $medName);
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

        // Data inkubator → incubators (form: incubator[<report_type_incubator_id>][field])
        $report->loadMissing('reportType.incubatorConfigs');
        foreach ($request->input('incubator', []) as $rtiId => $inkData) {
            $rti = $report->reportType->incubatorConfigs->firstWhere('id', $rtiId);
            if ($rti) {
                $report->incubators()->updateOrCreate(
                    ['report_type_incubator_id' => $rti->id],
                    [
                        'no_id'                => $inkData['no_id']            ?? null ?: null,
                        'calibration_date'     => $inkData['calibration_date'] ?? null ?: null,
                        'due_date_calibration' => $inkData['due_date']         ?? null ?: null,
                        'incubated_by'         => $inkData['incubated_by']     ?? null ?: null,
                        'date_in'              => $inkData['date_in']          ?? null ?: null,
                        'time_in'              => $inkData['time_in']          ?? null ?: null,
                        'removed_by'           => $inkData['removed_by']       ?? null ?: null,
                        'date_out'             => $inkData['date_out']         ?? null ?: null,
                        'time_out'             => $inkData['time_out']         ?? null ?: null,
                    ]
                );
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
            ->where('step', 2)
            ->where('user_id', $userId)
            ->firstOrFail();

        $report->load(['reportType.sections.locations.room', 'environmentalEntries.envSectionInstance', 'approvals.user', 'sectionColumnNames']);
        $report->applyReportTypeSnapshot();

        // Build instance ordering: instance_id → {location_id}
        // entryMap[$location_id][$period_number][$shift] = entry
        $entryMap = [];
        foreach ($report->environmentalEntries as $entry) {
            $locationId = optional($entry->envSectionInstance)->location_id;
            if (! $locationId) {
                continue;
            }
            $entryMap[$locationId][$entry->period_number][$entry->shift] = $entry;
        }

        $sectionTypes = $report->reportType->sections->pluck('measurement_type')->unique();
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
            ->where('report_approvals.step', 2)
            ->where('report_approvals.user_id', $userId);
    }
}
