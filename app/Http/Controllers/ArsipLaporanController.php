<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;

class ArsipLaporanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $reports = Report::with(['reportType'])
            ->whereHas('approvals', function ($q) {
                $q->where('step', 3)->where('status', 'approved');
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('product_name', 'like', "%{$search}%")
                       ->orWhere('batch_number', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('pages.arsip.index', compact('reports', 'search'));
    }

    public function show(Report $report)
    {
        // Ensure the report is approved by manager
        $report->loadMissing('approvals');
        $managerApproval = $report->approvals->where('step', 3)->where('status', 'approved')->first();
        abort_unless($managerApproval, 404);

        $report->load(['reportType.sections.locations.room', 'entries']);

        $entryMap = [];
        foreach ($report->entries as $entry) {
            $entryMap[$entry->report_section_id][$entry->period_number][$entry->shift] = $entry;
        }

        $sectionTypes    = $report->reportType->sections->pluck('measurement_type')->unique();
        $needsAirSampler = $sectionTypes->contains('air_sampler');
        $needsInkubator  = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();
        $needsMedium     = $sectionTypes->intersect(['settle_plate', 'contact_plate', 'swab'])->isNotEmpty();

        return view('pages.arsip.show', compact(
            'report', 'entryMap', 'needsAirSampler', 'needsInkubator', 'needsMedium'
        ));
    }
}
