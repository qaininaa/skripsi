<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportApproval;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SupervisorReportController extends Controller
{
    public function dashboard()
    {
        $userId = Auth::id();

        $pending  = $this->baseQuery($userId)->where('report_approvals.status', 'pending')->count();
        $approved = $this->baseQuery($userId)->where('report_approvals.status', 'approved')->count();
        $rejected = $this->baseQuery($userId)->where('report_approvals.status', 'rejected')->count();

        $counts = Report::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $pendingReports    = Report::with('reportType', 'lockedByUser')
            ->where('status', 'pending')
            ->latest()->take(5)->get();
        $monitoringReports = Report::with('reportType', 'lockedByUser')
            ->where('status', 'monitoring')
            ->latest()->take(5)->get();
        $readingReports    = Report::with('reportType', 'lockedByUser')
            ->where('status', 'reading')
            ->latest()->take(5)->get();

        return view('pages.supervisor.index', compact(
            'pending', 'approved', 'rejected',
            'counts', 'pendingReports', 'monitoringReports', 'readingReports'
        ));
    }

    public function laporanMasuk(Request $request)
    {
        $userId = Auth::id();
        $tab    = $request->query('tab', 'pending');

        $counts = [
            'pending'  => $this->baseQuery($userId)->where('report_approvals.status', 'pending')->count(),
            'approved' => $this->baseQuery($userId)->where('report_approvals.status', 'approved')->count(),
            'rejected' => $this->baseQuery($userId)->where('report_approvals.status', 'rejected')->count(),
        ];

        $reports = Report::with(['reportType', 'approvals'])
            ->join('report_approvals', 'reports.id', '=', 'report_approvals.report_id')
            ->where('report_approvals.step', 2)
            ->where('report_approvals.user_id', $userId)
            ->where('report_approvals.status', $tab)
            ->select('reports.*', 'report_approvals.status as approval_status', 'report_approvals.id as approval_id')
            ->orderByDesc('reports.created_at')
            ->paginate(15)
            ->withQueryString();

        return view('pages.supervisor.laporan-masuk', compact('reports', 'counts', 'tab'));
    }

    public function show(Report $report)
    {
        $userId = Auth::id();
        $approval = ReportApproval::where('report_id', $report->id)
            ->where('step', 2)
            ->where('user_id', $userId)
            ->firstOrFail();

        $report->load(['reportType.sections.locations.room', 'reportType.sections.locations.frequency', 'entries', 'approvals.user']);

        // entryMap[$pivot_id][$period_number][$shift] = entry
        $entryMap = [];
        foreach ($report->entries as $entry) {
            $entryMap[$entry->report_section_id][$entry->period_number][$entry->shift] = $entry;
        }

        $sectionTypes    = $report->reportType->sections->pluck('measurement_type')->unique();
        $needsAirSampler = $sectionTypes->contains('air_sampler');
        $needsInkubator  = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();
        $needsMedium     = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();

        $reviewRole      = 'supervisor';
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
        if ($user->username !== $request->username || !Hash::check($request->password, $user->password)) {
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
            'status'    => 'approved',
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
                    'role_label'          => 'manajer',
                    'user_id'             => $manager->id,
                    'status'              => 'pending',
                    'signed_at'           => null,
                    'notes'               => null,
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
        ]);

        $user = Auth::user();
        if ($user->username !== $request->username || !Hash::check($request->password, $user->password)) {
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

        $returnedToUserId = (int) $request->input('returned_to_user_id');
        $allowedUsers = array_merge(
            $report->analyst_monitoring ?? [],
            $report->analyst_reading ?? []
        );
        abort_unless(
            in_array($returnedToUserId, $allowedUsers),
            422,
            'Analis tujuan tidak valid.'
        );

        $approval->update([
            'status'               => 'returned',
            'notes'                => $request->input('notes'),
            'returned_to_user_id'  => $returnedToUserId,
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

        $report->loadMissing('reportType');
        $incoming = $request->input('header_data', []);
        $hd = $report->header_data ?? [];

        // Only update allowed keys: sections 2 (air_sampler), 3 (medium groups), 4 (inkubator)
        $allowedKeys = ['air_sampler', 'inkubator_20_25', 'inkubator_30_35'];
        foreach ($report->reportType->medium_groups ?? [] as $key => $_) {
            $allowedKeys[] = $key;
        }

        foreach ($allowedKeys as $key) {
            if (array_key_exists($key, $incoming)) {
                $hd[$key] = array_merge($hd[$key] ?? [], $incoming[$key]);
            }
        }

        // Time fields: exposure_times, settle_times, swab_times (merged deeply per section)
        foreach (['exposure_times', 'settle_times', 'swab_times'] as $timeKey) {
            $incomingTime = $request->input($timeKey);
            if (is_array($incomingTime)) {
                foreach ($incomingTime as $secId => $colData) {
                    foreach ($colData as $colKey => $slotData) {
                        if ($timeKey === 'settle_times') {
                            // settle_times[secId][col][a/b][start_time/end_time]
                            foreach ($slotData as $ab => $times) {
                                $hd[$timeKey][$secId][$colKey][$ab] = array_merge(
                                    $hd[$timeKey][$secId][$colKey][$ab] ?? [],
                                    $times
                                );
                            }
                        } elseif ($timeKey === 'swab_times') {
                            // swab_times[secId][col][s1/s1_2/s1_3][mulai/selesai]
                            foreach ($slotData as $swabKey => $times) {
                                $hd[$timeKey][$secId][$colKey][$swabKey] = array_merge(
                                    $hd[$timeKey][$secId][$colKey][$swabKey] ?? [],
                                    $times
                                );
                            }
                        } else {
                            // exposure_times[secId][col][start_time/end_time]
                            $hd[$timeKey][$secId][$colKey] = array_merge(
                                $hd[$timeKey][$secId][$colKey] ?? [],
                                $slotData
                            );
                        }
                    }
                }
            }
        }

        $report->update(['header_data' => $hd]);

        return back()->with('success', 'Data berhasil disimpan.');
    }

    public function cetak(Report $report)
    {
        $userId = Auth::id();
        ReportApproval::where('report_id', $report->id)
            ->where('step', 2)
            ->where('user_id', $userId)
            ->firstOrFail();

        $report->load(['reportType.sections.locations.room', 'reportType.sections.locations.frequency', 'entries', 'approvals.user']);
        $report->applyReportTypeSnapshot();

        // entryMap[$pivot_id][$period_number][$shift] = entry
        $entryMap = [];
        foreach ($report->entries as $entry) {
            $entryMap[$entry->report_section_id][$entry->period_number][$entry->shift] = $entry;
        }

        $sectionTypes    = $report->reportType->sections->pluck('measurement_type')->unique();
        $needsAirSampler = $sectionTypes->contains('air_sampler');
        $needsInkubator  = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();
        $needsMedium     = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();

        return view('pages.supervisor.laporan-cetak', compact(
            'report', 'entryMap', 'needsAirSampler', 'needsInkubator', 'needsMedium'
        ));
    }

    private function baseQuery(int $userId)
    {
        return Report::join('report_approvals', 'reports.id', '=', 'report_approvals.report_id')
            ->where('report_approvals.step', 2)
            ->where('report_approvals.user_id', $userId);
    }
}
