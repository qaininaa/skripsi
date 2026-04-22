<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ReportLocation;
use App\Models\ReportSection;
use App\Models\ReportType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReportTypeManagementController extends Controller
{
    public function index(): View
    {
        $reportTypes = ReportType::withCount('sections')
            ->orderBy('annex_number')
            ->paginate(15);

        return view('pages.report-types.index', compact('reportTypes'));
    }

    public function create(): View
    {
        return view('pages.report-types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:100', 'unique:report_types,code'],
            'name' => ['required', 'string', 'max:255'],
            'annex_number' => ['required', 'string', 'max:50'],
            // Medium groups
            'medium_labels' => ['nullable', 'array'],
            'medium_labels.*' => ['nullable', 'string', 'max:255'],
            // Incubators
            'incubator_labels' => ['nullable', 'array'],
            'incubator_labels.*' => ['nullable', 'string', 'max:255'],
            'incubator_min_days' => ['nullable', 'array'],
            'incubator_min_days.*' => ['nullable', 'integer', 'min:1'],
        ]);

        $mediumGroups = $this->buildMediumGroups($request);
        $incubators = $this->buildIncubators($request);

        $reportType = ReportType::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'annex_number' => $validated['annex_number'],
            'is_active' => true,
            'medium_groups' => $mediumGroups ?: null,
            'incubators' => $incubators ?: null,
        ]);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'create_report_type',
            'description' => 'Membuat jenis laporan: '.$reportType->name.' ('.$reportType->annex_number.')',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Jenis laporan berhasil dibuat. Silakan tambahkan seksi dan lokasi.');
    }

    public function show(ReportType $reportType): View
    {
        $reportType->load(['sections.locations.room']);
        $locations = ReportLocation::with('room')->orderBy('id_room')->orderBy('location_number')->get();

        return view('pages.report-types.show', compact('reportType', 'locations'));
    }

    public function edit(ReportType $reportType): View
    {
        return view('pages.report-types.edit', compact('reportType'));
    }

    public function update(Request $request, ReportType $reportType): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:100', 'unique:report_types,code,'.$reportType->id],
            'name' => ['required', 'string', 'max:255'],
            'annex_number' => ['required', 'string', 'max:50'],
            'medium_labels' => ['nullable', 'array'],
            'medium_labels.*' => ['nullable', 'string', 'max:255'],
            'incubator_labels' => ['nullable', 'array'],
            'incubator_labels.*' => ['nullable', 'string', 'max:255'],
            'incubator_min_days' => ['nullable', 'array'],
            'incubator_min_days.*' => ['required', 'integer', 'min:1'],
        ]);

        $mediumGroups = $this->buildMediumGroups($request);
        $incubators = $this->buildIncubators($request);

        $reportType->update([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'annex_number' => $validated['annex_number'],
            'medium_groups' => $mediumGroups ?: null,
            'incubators' => $incubators ?: null,
        ]);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'update_report_type',
            'description' => 'Memperbarui jenis laporan: '.$reportType->name.' ('.$reportType->annex_number.')',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Jenis laporan berhasil diperbarui.');
    }

    public function destroy(ReportType $reportType): RedirectResponse
    {
        if ($reportType->reports()->exists()) {
            return back()->with('error', 'Jenis laporan tidak bisa dihapus karena masih memiliki laporan terkait.');
        }

        $name = $reportType->name;
        $annex = $reportType->annex_number;
        $reportType->delete();

        AuditLog::create([
            'user_id' => request()->user()?->id,
            'action' => 'delete_report_type',
            'description' => 'Menghapus jenis laporan: '.$name.' ('.$annex.')',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()
            ->route('report-types.index')
            ->with('success', 'Jenis laporan berhasil dihapus.');
    }

    // ── Section management ─────────────────────────────────

    public function storeSection(Request $request, ReportType $reportType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'measurement_unit' => ['required', 'string', 'max:50'],
            'measurement_type' => ['required', 'string', 'max:50'],
            'max_column' => ['required', 'integer', 'min:1', 'max:20'],
            'column_label' => ['required', 'string', 'max:50'],
            'time_slot_type' => ['required', 'string', 'in:none,single,per_location,dual_ab,swab'],
            'has_machine_setup' => ['boolean'],
            'has_shift_toggle' => ['boolean'],
        ]);

        $maxOrder = $reportType->sections()->max('order') ?? 0;
        $validated['report_type_id'] = $reportType->id;
        $validated['order'] = $maxOrder + 1;
        $validated['has_machine_setup'] = $request->boolean('has_machine_setup');
        $validated['has_shift_toggle'] = $request->boolean('has_shift_toggle');

        ReportSection::create($validated);

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Seksi berhasil ditambahkan.');
    }

    public function updateSection(Request $request, ReportType $reportType, ReportSection $section): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'measurement_unit' => ['required', 'string', 'max:50'],
            'measurement_type' => ['required', 'string', 'max:50'],
            'max_column' => ['required', 'integer', 'min:1', 'max:20'],
            'column_label' => ['required', 'string', 'max:50'],
            'time_slot_type' => ['required', 'string', 'in:none,single,per_location,dual_ab,swab'],
            'has_machine_setup' => ['boolean'],
            'has_shift_toggle' => ['boolean'],
            'order' => ['required', 'integer', 'min:0'],
        ]);

        $validated['has_machine_setup'] = $request->boolean('has_machine_setup');
        $validated['has_shift_toggle'] = $request->boolean('has_shift_toggle');

        $section->update($validated);

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Seksi berhasil diperbarui.');
    }

    public function destroySection(ReportType $reportType, ReportSection $section): RedirectResponse
    {
        $section->delete();

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Seksi berhasil dihapus.');
    }

    // ── Location management ────────────────────────────────

    public function storeLocation(Request $request, ReportType $reportType, ReportSection $section): RedirectResponse
    {
        $validated = $request->validate([
            'location_id' => ['required', 'exists:locations,id'],
        ]);

        if (! $section->locations()->where('id_location', $validated['location_id'])->exists()) {
            $section->locations()->attach($validated['location_id']);
        }

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Lokasi berhasil ditambahkan.');
    }

    public function destroyLocation(ReportType $reportType, ReportSection $section, ReportLocation $location): RedirectResponse
    {
        $section->locations()->detach($location->id);

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Lokasi berhasil dihapus.');
    }

    // ── Helpers ─────────────────────────────────────────────

    private function buildMediumGroups(Request $request): array
    {
        $labels = $request->input('medium_labels', []);
        $result = [];
        foreach ($labels as $label) {
            $label = trim($label);
            if ($label !== '') {
                $result[Str::snake($label)] = $label;
            }
        }

        return $result;
    }

    private function buildIncubators(Request $request): array
    {
        $labels = $request->input('incubator_labels', []);
        $minDays = $request->input('incubator_min_days', []);
        $result = [];
        foreach ($labels as $i => $label) {
            $label = trim($label);
            if ($label !== '') {
                $result[Str::snake($label)] = [
                    'label' => $label,
                    'min_days' => (int) ($minDays[$i] ?? 3),
                ];
            }
        }

        return $result;
    }
}
