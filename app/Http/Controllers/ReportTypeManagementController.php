<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ReportLocation;
use App\Models\ReportSection;
use App\Models\ReportType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportTypeManagementController extends Controller
{
    public function index(): View
    {
        $reportTypes = ReportType::withCount('sections')
            ->orderBy('annex_number')
            ->paginate(15);

        return view('dashboard.report-types.index', compact('reportTypes'));
    }

    public function create(): View
    {
        return view('dashboard.report-types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code'         => ['required', 'string', 'max:100', 'unique:report_types,code'],
            'name'         => ['required', 'string', 'max:255'],
            'annex_number' => ['required', 'string', 'max:50'],
            'instrument'   => ['required', 'string', 'max:50'],
            'frequency'    => ['nullable', 'string', 'max:50'],
            'description'  => ['nullable', 'string'],
            'is_active'    => ['boolean'],
            // Medium groups
            'medium_keys'   => ['nullable', 'array'],
            'medium_keys.*' => ['required', 'string', 'max:100'],
            'medium_labels'   => ['nullable', 'array'],
            'medium_labels.*' => ['required', 'string', 'max:255'],
            // Incubators
            'incubator_keys'      => ['nullable', 'array'],
            'incubator_keys.*'    => ['required', 'string', 'max:100'],
            'incubator_labels'    => ['nullable', 'array'],
            'incubator_labels.*'  => ['required', 'string', 'max:255'],
            'incubator_min_days'  => ['nullable', 'array'],
            'incubator_min_days.*' => ['required', 'integer', 'min:1'],
        ]);

        $mediumGroups = $this->buildMediumGroups($request);
        $incubators = $this->buildIncubators($request);

        $reportType = ReportType::create([
            'code'          => $validated['code'],
            'name'          => $validated['name'],
            'annex_number'  => $validated['annex_number'],
            'instrument'    => $validated['instrument'],
            'frequency'     => $validated['frequency'] ?? null,
            'description'   => $validated['description'] ?? null,
            'is_active'     => $request->boolean('is_active', true),
            'medium_groups' => $mediumGroups ?: null,
            'incubators'    => $incubators ?: null,
        ]);

        AuditLog::create([
            'user_id'     => $request->user()?->id,
            'action'      => 'create_report_type',
            'description' => 'Membuat jenis laporan: ' . $reportType->name . ' (' . $reportType->annex_number . ')',
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
        ]);

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Jenis laporan berhasil dibuat. Silakan tambahkan seksi dan lokasi.');
    }

    public function show(ReportType $reportType): View
    {
        $reportType->load(['sections.locations']);

        return view('dashboard.report-types.show', compact('reportType'));
    }

    public function edit(ReportType $reportType): View
    {
        return view('dashboard.report-types.edit', compact('reportType'));
    }

    public function update(Request $request, ReportType $reportType): RedirectResponse
    {
        $validated = $request->validate([
            'code'         => ['required', 'string', 'max:100', 'unique:report_types,code,' . $reportType->id],
            'name'         => ['required', 'string', 'max:255'],
            'annex_number' => ['required', 'string', 'max:50'],
            'instrument'   => ['required', 'string', 'max:50'],
            'frequency'    => ['nullable', 'string', 'max:50'],
            'description'  => ['nullable', 'string'],
            'is_active'    => ['boolean'],
            'medium_keys'   => ['nullable', 'array'],
            'medium_keys.*' => ['required', 'string', 'max:100'],
            'medium_labels'   => ['nullable', 'array'],
            'medium_labels.*' => ['required', 'string', 'max:255'],
            'incubator_keys'      => ['nullable', 'array'],
            'incubator_keys.*'    => ['required', 'string', 'max:100'],
            'incubator_labels'    => ['nullable', 'array'],
            'incubator_labels.*'  => ['required', 'string', 'max:255'],
            'incubator_min_days'  => ['nullable', 'array'],
            'incubator_min_days.*' => ['required', 'integer', 'min:1'],
        ]);

        $mediumGroups = $this->buildMediumGroups($request);
        $incubators = $this->buildIncubators($request);

        $reportType->update([
            'code'          => $validated['code'],
            'name'          => $validated['name'],
            'annex_number'  => $validated['annex_number'],
            'instrument'    => $validated['instrument'],
            'frequency'     => $validated['frequency'] ?? null,
            'description'   => $validated['description'] ?? null,
            'is_active'     => $request->boolean('is_active', true),
            'medium_groups' => $mediumGroups ?: null,
            'incubators'    => $incubators ?: null,
        ]);

        AuditLog::create([
            'user_id'     => $request->user()?->id,
            'action'      => 'update_report_type',
            'description' => 'Memperbarui jenis laporan: ' . $reportType->name . ' (' . $reportType->annex_number . ')',
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
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
            'user_id'     => request()->user()?->id,
            'action'      => 'delete_report_type',
            'description' => 'Menghapus jenis laporan: ' . $name . ' (' . $annex . ')',
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);

        return redirect()
            ->route('report-types.index')
            ->with('success', 'Jenis laporan berhasil dihapus.');
    }

    // ── Section management ─────────────────────────────────

    public function storeSection(Request $request, ReportType $reportType): RedirectResponse
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'slug'             => ['required', 'string', 'max:100'],
            'measurement_unit' => ['required', 'string', 'max:50'],
            'measurement_type' => ['required', 'string', 'max:50'],
            'max_exposures'    => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $maxOrder = $reportType->sections()->max('order') ?? 0;
        $validated['report_type_id'] = $reportType->id;
        $validated['order'] = $maxOrder + 1;

        ReportSection::create($validated);

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Seksi berhasil ditambahkan.');
    }

    public function updateSection(Request $request, ReportType $reportType, ReportSection $section): RedirectResponse
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'slug'             => ['required', 'string', 'max:100'],
            'measurement_unit' => ['required', 'string', 'max:50'],
            'measurement_type' => ['required', 'string', 'max:50'],
            'max_exposures'    => ['required', 'integer', 'min:1', 'max:20'],
            'order'            => ['required', 'integer', 'min:0'],
        ]);

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
            'room_name'              => ['required', 'string', 'max:255'],
            's_no'                   => ['required', 'integer', 'min:1'],
            'class'                  => ['required', 'string', 'max:20'],
            'room_number'            => ['nullable', 'string', 'max:50'],
            'location_number'        => ['nullable', 'string', 'max:50'],
            'alert_limit_bacteria'   => ['nullable', 'integer', 'min:0'],
            'action_limit_bacteria'  => ['nullable', 'integer', 'min:0'],
            'alert_limit_fungi'      => ['nullable', 'integer', 'min:0'],
            'action_limit_fungi'     => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['report_section_id'] = $section->id;

        ReportLocation::create($validated);

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Lokasi berhasil ditambahkan.');
    }

    public function destroyLocation(ReportType $reportType, ReportSection $section, ReportLocation $location): RedirectResponse
    {
        $location->delete();

        return redirect()
            ->route('report-types.show', $reportType)
            ->with('success', 'Lokasi berhasil dihapus.');
    }

    // ── Helpers ─────────────────────────────────────────────

    private function buildMediumGroups(Request $request): array
    {
        $keys = $request->input('medium_keys', []);
        $labels = $request->input('medium_labels', []);
        $result = [];
        foreach ($keys as $i => $key) {
            if (!empty($key) && !empty($labels[$i] ?? '')) {
                $result[$key] = $labels[$i];
            }
        }
        return $result;
    }

    private function buildIncubators(Request $request): array
    {
        $keys = $request->input('incubator_keys', []);
        $labels = $request->input('incubator_labels', []);
        $minDays = $request->input('incubator_min_days', []);
        $result = [];
        foreach ($keys as $i => $key) {
            if (!empty($key) && !empty($labels[$i] ?? '')) {
                $result[$key] = [
                    'label'    => $labels[$i],
                    'min_days' => (int) ($minDays[$i] ?? 1),
                ];
            }
        }
        return $result;
    }
}
