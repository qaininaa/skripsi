<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ManajerLaporanController extends Controller
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

        $report->load(['reportType.sections.locations.room', 'entries', 'approvals.user']);

        // Supervisor (step 2 user) for the return dropdown
        $supervisorApproval = $report->approvals->firstWhere('step', 2);
        $supervisor = $supervisorApproval?->user;

        $entryMap = [];
        foreach ($report->entries as $entry) {
            $entryMap[$entry->report_section_id][$entry->period_number][$entry->shift] = $entry;
        }

        $sectionTypes    = $report->reportType->sections->pluck('measurement_type')->unique();
        $needsAirSampler = $sectionTypes->contains('air_sampler');
        $needsInkubator  = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();
        $needsMedium     = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();

        return view('pages.manajer.laporan-show', compact(
            'report', 'approval', 'entryMap', 'supervisor',
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

        // Validate: must be the step-2 supervisor
        $supervisorApproval = ReportApproval::where('report_id', $report->id)
            ->where('step', 2)
            ->first();

        abort_unless(
            $supervisorApproval && $supervisorApproval->user_id === $returnedToUserId,
            422,
            'Penerima pengembalian tidak valid.'
        );

        $approval->update([
            'status'               => 'returned',
            'notes'                => $request->input('notes'),
            'returned_to_user_id'  => $returnedToUserId,
        ]);

        // Reset supervisor approval back to pending so they can re-review
        $supervisorApproval->update(['status' => 'pending', 'signed_at' => null]);

        $report->update(['status' => 'returned_to_supervisor']);

        return redirect()->route('manajer.laporan-masuk')
            ->with('success', 'Laporan telah dikembalikan ke Supervisor.');
    }

    public function cetak(Report $report)
    {
        $userId = Auth::id();
        ReportApproval::where('report_id', $report->id)
            ->where('step', 3)
            ->where('user_id', $userId)
            ->firstOrFail();

        $report->load(['reportType.sections.locations.room', 'entries', 'approvals.user']);
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
