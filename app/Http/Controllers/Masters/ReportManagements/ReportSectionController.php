<?php

namespace App\Http\Controllers\Masters\ReportManagements;

use App\Http\Controllers\Controller;
use App\Models\ReportSection;
use App\Models\ReportType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReportSectionController extends Controller
{
    public function store(Request $request, ReportType $reportType): RedirectResponse
    {
        $validated = $request->validate([
            'measurement_unit'  => ['required', 'string', 'max:50'],
            'measurement_type'  => ['required', 'string', 'max:50'],
            'max_column'        => ['required', 'integer', 'min:1', 'max:20'],
            'column_label'      => ['nullable', 'string', 'max:50'],
            'time_slot_type'    => ['required', 'string', 'in:none,single,per_location,dual_ab,swab'],
            'has_machine_setup' => ['boolean'],
        ]);

        $validated['report_type_id']    = $reportType->id;
        $validated['order']             = ($reportType->sections()->max('order') ?? 0) + 1;
        $validated['has_machine_setup'] = $request->boolean('has_machine_setup');

        ReportSection::create($validated);

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Seksi berhasil ditambahkan.');
    }

    public function update(Request $request, ReportType $reportType, ReportSection $section): RedirectResponse
    {
        $validated = $request->validate([
            'measurement_unit'  => ['required', 'string', 'max:50'],
            'measurement_type'  => ['required', 'string', 'max:50'],
            'max_column'        => ['required', 'integer', 'min:1', 'max:20'],
            'column_label'      => ['nullable', 'string', 'max:50'],
            'time_slot_type'    => ['required', 'string', 'in:none,single,per_location,dual_ab,swab'],
            'has_machine_setup' => ['boolean'],
            'order'             => ['required', 'integer', 'min:0'],
        ]);

        $validated['has_machine_setup'] = $request->boolean('has_machine_setup');

        $section->update($validated);

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Seksi berhasil diperbarui.');
    }

    public function destroy(ReportType $reportType, ReportSection $section): RedirectResponse
    {
        $section->delete();

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Seksi berhasil dihapus.');
    }
}