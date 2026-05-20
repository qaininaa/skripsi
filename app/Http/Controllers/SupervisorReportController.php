<?php

namespace App\Http\Controllers;

use App\Services\Reports\ReportWorkflowService;
use Domain\Report\Services\IncubatorEntryService;
use Domain\Report\Services\InstrumentIdentityEntryService;
use Domain\Report\Services\MediumEntryService;
use Domain\Report\Models\Report;
use Domain\Report\Models\ReportApproval;
use Domain\User\Models\User;
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

    public function incomingReports(Request $request)
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

        return view('pages.supervisor.incoming-reports', compact('reports', 'counts', 'tab'));
    }

    public function ongoingReports(Request $request)
    {
        $status = $request->query('status', 'all');
        $validStatuses = ['all', 'pending', 'monitoring', 'reading', 'review_supervisor', 'waiting_manager', 'returned'];
        if (! in_array($status, $validStatuses, true)) {
            $status = 'all';
        }

        $countKeys = ['all', 'pending', 'monitoring', 'reading', 'review_supervisor', 'waiting_manager', 'returned'];
        $counts = [];
        foreach ($countKeys as $key) {
            $countQuery = $this->progressBaseQuery();
            $this->applyProgressStatusFilter($countQuery, $key);
            $counts[$key] = $countQuery->count();
        }

        $reportsQuery = $this->progressBaseQuery()
            ->with(['reportType', 'lockedByUser', 'analysts.user', 'approvals.user', 'approvals.returnedTo']);
        $this->applyProgressStatusFilter($reportsQuery, $status);

        $reports = $reportsQuery
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('pages.supervisor.ongoing-reports', compact('reports', 'counts', 'status'));
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
            'reportType.mediumTypes',
            'environmentalEntries',
            'sectionColumnNames',
            'sectionNotes',
            'approvals.user',
            'analysts.user',
            'signatures',
            'mediumIdentities',
            'instrumentEntries',
            'incubators',
        ]);

        $sectionService = app(\App\Services\ReportSectionService::class);
        $entryMap = $sectionService->buildEntryMap($report);
        $sectionInstances = $sectionService->buildSectionInstances($report);
        $sectionNeeds = $sectionService->computeSectionNeeds($report);

        $needsAirSampler = $sectionNeeds['needsAirSampler'];
        $needsInkubator = $sectionNeeds['needsInkubator'];
        $needsMedium = $sectionNeeds['needsMedium'];

        $reviewRole = 'supervisor';
        $returnSupervisor = null;

        return view('pages.review.reports-show', compact(
            'report', 'approval', 'entryMap', 'sectionInstances',
            'needsAirSampler', 'needsInkubator', 'needsMedium',
            'reviewRole', 'returnSupervisor'
        ));
    }

    public function approve(Request $request, Report $report, ReportWorkflowService $workflowService)
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

        $workflowService->stampSupervisorSignaturesForFilledSections($report, (string) $userId, $signedAt);

        // Create or reset step 3 approval for manager
        $manager = User::where('role', 'manager')->first();
        if ($manager) {
            ReportApproval::updateOrCreate(
                ['report_id' => $report->id, 'step' => 3],
                [
                    'role' => 'manager',
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

        return redirect()->route('supervisor.incoming-reports')
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

        $report->update(['status' => 'returned', 'locked_by' => null]);

        return redirect()->route('supervisor.incoming-reports')
            ->with('success', 'Laporan telah dikembalikan ke analis.');
    }

    public function save(Request $request, Report $report)
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
        ReportApproval::where('report_id', $report->id)
            ->where('step', 2)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->firstOrFail();

        $instrumentIdentityEntryService = app(InstrumentIdentityEntryService::class);
        $incubatorEntryService = app(IncubatorEntryService::class);
        $mediumEntryService = app(MediumEntryService::class);
        $entryService = app(\Domain\Report\Services\ReportEntryService::class);

        // Identitas instrumen (Air Sampler) → instrument_entries
        $instrumentIdentityEntryService->saveFromRequest($request, $report);

        // Identitas medium agar → medium_identities
        $mediumEntryService->saveFromRequest($request, $report);

        // Data inkubator info → incubators + incubator_entries
        $incubatorEntryService->saveFromRequest($request, $report);

        // Waktu paparan → report_environmental_entries (start_time/end_time)
        $entryService->saveReviewTimesToEntries(
            $request->input('settle_times',   []),
            $request->input('swab_times',     []),
            $request->input('exposure_times', []),
            $report
        );

        // Catatan & kesimpulan per section -> report_section_notes
        $entryService->saveSectionNotes($request, $report);

        return back()->with('success', 'Data berhasil disimpan.');
    }

    public function print(Report $report)
    {
        $userId = Auth::id();
        ReportApproval::where('report_id', $report->id)
            ->where('step', 2)
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
            'environmentalEntries.envSectionInstance',
            'approvals.user',
            'analysts.user',
            'sectionColumnNames',
            'sectionNotes',
            'instrumentEntries',
            'mediumIdentities',
            'incubators.entries.incubatedBy',
            'incubators.entries.removedBy',
        ]);
        $report->applyReportTypeSnapshot();

        $sectionService = app(\App\Services\ReportSectionService::class);
        // entryMap[location_id][instance][period_number][shift] = entry
        $entryMap = $sectionService->buildEntryMap($report);
        // sectionInstances: loop expanded with duplicates per section
        $sectionInstances = $sectionService->buildSectionInstances($report);

        $sectionTypes = $report->reportType->sections
            ->map(fn ($section) => $section->measurement_key)
            ->unique();
        $needsAirSampler = $sectionTypes->contains('air_sampler');
        $needsInkubator = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();
        $needsMedium = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();

        return view('pages.supervisor.reports-print', compact(
            'report', 'entryMap', 'sectionInstances',
            'needsAirSampler', 'needsInkubator', 'needsMedium'
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
                    })
                    ->orWhereIn('status', ['returned', 'returned_to_supervisor'])
                    ->orWhereHas('approvals', function ($approvalQuery) {
                        $approvalQuery->whereIn('step', [2, 3])->where('status', 'returned');
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

        if ($status === 'returned') {
            $query->where(function ($nested) {
                $nested->whereIn('status', ['returned', 'returned_to_supervisor'])
                    ->orWhereHas('approvals', function ($approvalQuery) {
                        $approvalQuery->whereIn('step', [2, 3])->where('status', 'returned');
                    });
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
