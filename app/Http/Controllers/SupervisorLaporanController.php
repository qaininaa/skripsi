<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupervisorLaporanController extends Controller
{
    public function dashboard()
    {
        $userId = Auth::id();

        $pending  = $this->baseQuery($userId)->where('report_approvals.status', 'pending')->count();
        $approved = $this->baseQuery($userId)->where('report_approvals.status', 'approved')->count();
        $rejected = $this->baseQuery($userId)->where('report_approvals.status', 'rejected')->count();

        return view('dashboard.supervisor.index', compact('pending', 'approved', 'rejected'));
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

        $reports = Report::with(['reportType', 'shift1Analis', 'shift2Analis', 'approvals'])
            ->join('report_approvals', 'reports.id', '=', 'report_approvals.report_id')
            ->where('report_approvals.step', 2)
            ->where('report_approvals.user_id', $userId)
            ->where('report_approvals.status', $tab)
            ->select('reports.*', 'report_approvals.status as approval_status', 'report_approvals.id as approval_id')
            ->orderByDesc('reports.created_at')
            ->paginate(15)
            ->withQueryString();

        return view('dashboard.supervisor.laporan-masuk', compact('reports', 'counts', 'tab'));
    }

    public function show(Report $report)
    {
        $userId = Auth::id();
        $approval = ReportApproval::where('report_id', $report->id)
            ->where('step', 2)
            ->where('user_id', $userId)
            ->firstOrFail();

        $report->load(['reportType.sections.locations', 'entries', 'shift1Analis', 'shift2Analis', 'approvals']);

        // Build same entryMap as analis controller
        $entryMap = [];
        foreach ($report->entries as $entry) {
            $entryMap[$entry->report_location_id][$entry->period_number][$entry->shift] = $entry;
        }

        $sectionTypes    = $report->reportType->sections->pluck('measurement_type')->unique();
        $needsAirSampler = $sectionTypes->contains('air_sampler');
        $needsInkubator  = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();
        $needsMedium     = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();

        return view('dashboard.supervisor.laporan-show', compact(
            'report', 'approval', 'entryMap',
            'needsAirSampler', 'needsInkubator', 'needsMedium'
        ));
    }

    public function approve(Request $request, Report $report)
    {
        $userId = Auth::id();
        $approval = ReportApproval::where('report_id', $report->id)
            ->where('step', 2)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->firstOrFail();

        $approval->update([
            'status'    => 'approved',
            'signed_at' => now(),
            'catatan'   => $request->input('catatan'),
        ]);

        $report->update(['status' => 'approved']);

        return redirect()->route('supervisor.laporan-masuk')
            ->with('success', 'Laporan berhasil disetujui.');
    }

    public function reject(Request $request, Report $report)
    {
        $userId = Auth::id();
        $approval = ReportApproval::where('report_id', $report->id)
            ->where('step', 2)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->firstOrFail();

        $approval->update([
            'status'  => 'rejected',
            'catatan' => $request->input('catatan'),
        ]);

        $report->update(['status' => 'rejected']);

        return redirect()->route('supervisor.laporan-masuk')
            ->with('success', 'Laporan telah ditolak.');
    }

    public function cetak(Report $report)
    {
        $userId = Auth::id();
        ReportApproval::where('report_id', $report->id)
            ->where('step', 2)
            ->where('user_id', $userId)
            ->firstOrFail();

        $report->load(['reportType.sections.locations', 'entries', 'shift1Analis', 'shift2Analis']);

        $entryMap = [];
        foreach ($report->entries as $entry) {
            $entryMap[$entry->report_location_id][$entry->period_number][$entry->shift] = $entry;
        }

        $sectionTypes    = $report->reportType->sections->pluck('measurement_type')->unique();
        $needsAirSampler = $sectionTypes->contains('air_sampler');
        $needsInkubator  = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();
        $needsMedium     = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();

        return view('dashboard.supervisor.laporan-cetak', compact(
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
