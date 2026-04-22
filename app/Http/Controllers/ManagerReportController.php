<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ManagerReportController extends Controller
{
    public function dashboard()
    {
        $userId = Auth::id();

        $pending  = $this->baseQuery($userId)->where('report_approvals.status', 'pending')->count();
        $approved = $this->baseQuery($userId)->where('report_approvals.status', 'approved')->count();
        $rejected = $this->baseQuery($userId)->where('report_approvals.status', 'rejected')->count();

        return view('pages.manajer.index', compact('pending', 'approved', 'rejected'));
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
            ->where('report_approvals.step', 3)
            ->where('report_approvals.user_id', $userId)
            ->where('report_approvals.status', $tab)
            ->select('reports.*', 'report_approvals.status as approval_status', 'report_approvals.id as approval_id')
            ->orderByDesc('reports.created_at')
            ->paginate(15)
            ->withQueryString();

        return view('pages.manajer.laporan-masuk', compact('reports', 'counts', 'tab'));
    }

    public function show(Report $report)
    {
        $userId = Auth::id();
        $approval = ReportApproval::where('report_id', $report->id)
            ->where('step', 3)
            ->where('user_id', $userId)
            ->firstOrFail();

        $report->load(['reportType.sections.locations.room', 'reportType.sections.locations.frequency', 'entries', 'approvals.user']);

        // Supervisor (step 2 user) for the return dropdown
        $supervisorApproval = $report->approvals->firstWhere('step', 2);
        $returnSupervisor   = $supervisorApproval?->user;

        $entryMap = [];
        foreach ($report->entries as $entry) {
            $entryMap[$entry->report_section_id][$entry->period_number][$entry->shift] = $entry;
        }

        $sectionTypes    = $report->reportType->sections->pluck('measurement_type')->unique();
        $needsAirSampler = $sectionTypes->contains('air_sampler');
        $needsInkubator  = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();
        $needsMedium     = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();

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
        if ($user->username !== $request->username || !Hash::check($request->password, $user->password)) {
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
            'status'    => 'approved',
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
            'annex_number'  => $report->reportType->annex_number,
            'name'          => $report->reportType->name,
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
        if ($user->username !== $request->username || !Hash::check($request->password, $user->password)) {
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

        $returnedToUserId = (int) $request->input('returned_to_user_id');

        $supervisorApproval = ReportApproval::where('report_id', $report->id)
            ->where('step', 2)
            ->first();

        $allowedAnalysts = array_merge(
            $report->analyst_monitoring ?? [],
            $report->analyst_reading ?? []
        );

        $isToSupervisor = $supervisorApproval && $supervisorApproval->user_id === $returnedToUserId;
        $isToAnalyst    = in_array($returnedToUserId, $allowedAnalysts);

        abort_unless($isToSupervisor || $isToAnalyst, 422, 'Penerima pengembalian tidak valid.');

        $approval->update([
            'status'               => 'returned',
            'notes'                => $request->input('notes'),
            'returned_to_user_id'  => $returnedToUserId,
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

        $report->loadMissing('reportType');
        $incoming = $request->input('header_data', []);
        $hd = $report->header_data ?? [];

        $allowedKeys = ['air_sampler', 'inkubator_20_25', 'inkubator_30_35'];
        foreach ($report->reportType->medium_groups ?? [] as $key => $_) {
            $allowedKeys[] = $key;
        }

        foreach ($allowedKeys as $key) {
            if (array_key_exists($key, $incoming)) {
                $hd[$key] = array_merge($hd[$key] ?? [], $incoming[$key]);
            }
        }

        foreach (['exposure_times', 'settle_times', 'swab_times'] as $timeKey) {
            $incomingTime = $request->input($timeKey);
            if (is_array($incomingTime)) {
                foreach ($incomingTime as $secId => $colData) {
                    foreach ($colData as $colKey => $slotData) {
                        if ($timeKey === 'settle_times') {
                            foreach ($slotData as $ab => $times) {
                                $hd[$timeKey][$secId][$colKey][$ab] = array_merge(
                                    $hd[$timeKey][$secId][$colKey][$ab] ?? [],
                                    $times
                                );
                            }
                        } elseif ($timeKey === 'swab_times') {
                            foreach ($slotData as $swabKey => $times) {
                                $hd[$timeKey][$secId][$colKey][$swabKey] = array_merge(
                                    $hd[$timeKey][$secId][$colKey][$swabKey] ?? [],
                                    $times
                                );
                            }
                        } else {
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
            ->where('step', 3)
            ->where('user_id', $userId)
            ->firstOrFail();

        $report->load(['reportType.sections.locations.room', 'reportType.sections.locations.frequency', 'entries', 'approvals.user']);
        $report->applyReportTypeSnapshot();
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
            ->where('report_approvals.step', 3)
            ->where('report_approvals.user_id', $userId);
    }
}
