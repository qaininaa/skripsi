<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportApproval;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SupervisorLaporanController extends Controller
{
    public function dashboard()
    {
        $userId = Auth::id();

        $pending  = $this->baseQuery($userId)->where('report_approvals.status', 'pending')->count();
        $approved = $this->baseQuery($userId)->where('report_approvals.status', 'approved')->count();
        $rejected = $this->baseQuery($userId)->where('report_approvals.status', 'rejected')->count();

        return view('pages.supervisor.index', compact('pending', 'approved', 'rejected'));
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

        $report->load(['reportType.sections.locations.room', 'entries', 'approvals.user']);

        // entryMap[$pivot_id][$period_number][$shift] = entry
        $entryMap = [];
        foreach ($report->entries as $entry) {
            $entryMap[$entry->report_section_id][$entry->period_number][$entry->shift] = $entry;
        }

        $sectionTypes    = $report->reportType->sections->pluck('measurement_type')->unique();
        $needsAirSampler = $sectionTypes->contains('air_sampler');
        $needsInkubator  = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();
        $needsMedium     = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();

        return view('pages.supervisor.laporan-show', compact(
            'report', 'approval', 'entryMap',
            'needsAirSampler', 'needsInkubator', 'needsMedium'
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

        $approval->update([
            'status'    => 'approved',
            'signed_at' => now(),
        ]);

        // Create step 3 approval for manajer
        $manager = User::where('role', 'manajer')->first();
        if ($manager) {
            ReportApproval::firstOrCreate(
                ['report_id' => $report->id, 'step' => 3],
                ['role_label' => 'manajer', 'user_id' => $manager->id, 'status' => 'pending']
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

        // Reset signature timestamps
        $hd = $report->header_data ?? [];
        unset($hd['ttd_monitoring_signed_at'], $hd['ttd_dibaca_signed_at']);
        $report->update(['status' => 'returned', 'locked_by' => null, 'header_data' => $hd]);

        return redirect()->route('supervisor.laporan-masuk')
            ->with('success', 'Laporan telah dikembalikan ke analis.');
    }

    public function cetak(Report $report)
    {
        $userId = Auth::id();
        ReportApproval::where('report_id', $report->id)
            ->where('step', 2)
            ->where('user_id', $userId)
            ->firstOrFail();

        $report->load(['reportType.sections.locations.room', 'entries', 'approvals.user']);

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
