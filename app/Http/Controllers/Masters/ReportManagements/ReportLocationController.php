<?php

namespace App\Http\Controllers\Masters\ReportManagements;

use App\Http\Controllers\Controller;
use App\Models\ReportLocation;
use App\Models\ReportSection;
use App\Models\ReportType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReportLocationController extends Controller
{
    public function store(Request $request, ReportType $reportType, ReportSection $section): RedirectResponse
    {
        $validated = $request->validate([
            'location_id' => ['required', 'exists:locations,id'],
        ]);

        $location = ReportLocation::query()->findOrFail($validated['location_id']);

        if ($location->section_id && (string) $location->section_id !== (string) $section->id) {
            return redirect()
                ->route('report-types.show', $reportType)
                ->with('error', 'Lokasi sudah terhubung ke seksi lain. Lepaskan terlebih dahulu dari seksi asal.');
        }

        if ((string) $location->section_id !== (string) $section->id) {
            $location->update(['section_id' => $section->id]);
        }

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Lokasi berhasil ditambahkan.');
    }

    public function destroy(ReportType $reportType, ReportSection $section, ReportLocation $location): RedirectResponse
    {
        if ((string) $location->section_id === (string) $section->id) {
            $location->update(['section_id' => null]);
        }

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Lokasi berhasil dihapus.');
    }
}