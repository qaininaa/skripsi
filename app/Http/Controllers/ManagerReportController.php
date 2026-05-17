<?php

namespace App\Http\Controllers;

use Domain\Report\Services\IncubatorEntryService;
use Domain\Report\Services\InstrumentIdentityEntryService;
use Domain\Report\Services\MediumEntryService;
use Domain\Report\Models\Report;
use Domain\Report\Models\ReportApproval;
use Domain\Report\Models\SectionSignature;
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

        return view('pages.manager.index', compact('pending', 'approved', 'returned'));
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

        return view('pages.manager.incoming-reports', compact('reports', 'counts', 'tab'));
    }

    public function ongoingReports(Request $request)
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

        return view('pages.manager.ongoing-reports', compact('reports', 'counts', 'status'));
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

        // Supervisor (step 2 user) for the return dropdown
        $supervisorApproval = $report->approvals->firstWhere('step', 2);
        $returnSupervisor = $supervisorApproval?->user;

        $sectionService = app(\App\Services\ReportSectionService::class);
        $entryMap = $sectionService->buildEntryMap($report);
        $sectionNeeds = $sectionService->computeSectionNeeds($report);

        $needsAirSampler = $sectionNeeds['needsAirSampler'];
        $needsInkubator = $sectionNeeds['needsInkubator'];
        $needsMedium = $sectionNeeds['needsMedium'];

        $reviewRole = 'manager';

        return view('pages.review.reports-show', compact(
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

        // Stamp per-section manager TTD ke tabel section_signatures.
        $report->loadMissing(['reportType.sections', 'sectionSignatures']);
        foreach ($report->reportType->sections as $sec) {
            $instanceNumbers = $report->sectionSignatures
                ->where('section_id', $sec->id)
                ->whereIn('role', ['monitoring', 'reading', 'supervisor'])
                ->pluck('instance_number')
                ->filter()
                ->map(fn ($num) => (int) $num)
                ->unique()
                ->values();

            if ($instanceNumbers->isEmpty()) {
                $instanceNumbers = collect([1]);
            }

            foreach ($instanceNumbers as $instanceNumber) {
                SectionSignature::updateOrCreate(
                    [
                        'report_id' => $report->id,
                        'section_id' => $sec->id,
                        'instance_number' => (int) $instanceNumber,
                        'user_id' => $userId,
                        'role' => 'manager',
                    ],
                    ['signed_at' => $signedAt]
                );
            }
        }

        $headerData = $report->header_data ?? [];

        // Snapshot report type structure so archived reports are immutable
        $headerData['_snapshot_section_ids'] = $report->reportType->sections->pluck('id')->toArray();
        $headerData['_snapshot_report_type'] = [
            'annex_number' => $report->reportType->annex_number,
            'name' => $report->reportType->name,
            'medium_groups' => $report->reportType->medium_groups,
        ];

        $report->update(['header_data' => $headerData]);

        $report->update(['status' => 'approved']);

        return redirect()->route('manager.incoming-reports')
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
            // Clear per-section TTDs so all roles re-sign from scratch.
            SectionSignature::where('report_id', $report->id)
                ->whereIn('role', ['monitoring', 'reading', 'supervisor', 'manager'])
                ->delete();

            $analystApproval = ReportApproval::where('report_id', $report->id)
                ->where('step', 1)
                ->first();
            if ($analystApproval) {
                $analystApproval->update(['status' => 'pending', 'signed_at' => null]);
            }
            if ($supervisorApproval) {
                $supervisorApproval->update(['status' => 'pending', 'signed_at' => null]);
            }

            $report->update(['status' => 'returned', 'locked_by' => null]);

            return redirect()->route('manager.incoming-reports')
                ->with('success', 'Laporan telah dikembalikan ke Analis.');
        }

        // Return to supervisor
        $supervisorApproval->update(['status' => 'pending', 'signed_at' => null]);
        $report->update(['status' => 'returned_to_supervisor']);

        return redirect()->route('manager.incoming-reports')
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
            'environmentalEntries.envSectionInstance',
            'approvals.user',
            'sectionColumnNames',
            'sectionNotes',
            'instrumentEntries',
            'mediumIdentities',
            'incubators.entries.incubatedBy',
            'incubators.entries.removedBy',
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

        return view('pages.supervisor.reports-print', compact(
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
