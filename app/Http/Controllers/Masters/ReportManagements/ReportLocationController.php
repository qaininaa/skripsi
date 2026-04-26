<?php

namespace App\Http\Controllers\Masters\ReportManagements;

use App\Http\Controllers\Controller;
use App\Models\ReportLocation;
use App\Models\ReportSection;
use App\Models\ReportType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReportLocationController extends Controller
{
    public function store(Request $request, ReportType $reportType, ReportSection $section): RedirectResponse
    {
        $validated = $request->validate([
            'location_id' => ['required', 'exists:locations,id'],
        ]);

        if (! $section->locations()->where('location_id', $validated['location_id'])->exists()) {
            $section->locations()->attach($validated['location_id'], ['id' => (string) Str::uuid()]);
        }

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Lokasi berhasil ditambahkan.');
    }

    public function destroy(ReportType $reportType, ReportSection $section, ReportLocation $location): RedirectResponse
    {
        $section->locations()->detach($location->id);

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Lokasi berhasil dihapus.');
    }
}