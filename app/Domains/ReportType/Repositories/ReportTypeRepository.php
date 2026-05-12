<?php

namespace App\Domains\ReportType\Repositories;

use App\Domains\Location\Models\Location;
use App\Domains\ReportType\Models\ReportType;
use App\Domains\ReportType\Models\IncubatorType;
use App\Domains\ReportType\Models\MediumType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Repository for report type aggregate persistence and query operations.
 */
class ReportTypeRepository
{
    /**
     * Get paginated report types for management page.
     *
     * @return LengthAwarePaginator<int, ReportType>
     */
    public function paginateForManagement(int $perPage = 10): LengthAwarePaginator
    {
        return ReportType::query()
            ->withCount('sections')
            ->orderBy('annex_number')
            ->paginate($perPage);
    }

    /**
     * Create a report type record.
     *
     * @param  array<string, mixed>  $payload
     */
    public function create(array $payload): ReportType
    {
        return ReportType::create($payload);
    }

    /**
     * Find duplicate report type by SOP code, SOP version, and annex number.
     *
     * @param  array<string, mixed>  $validated
     */
    public function findDuplicate(array $validated, ?string $ignoreReportTypeId = null): ?ReportType
    {
        return ReportType::query()
            ->when($ignoreReportTypeId, fn ($q) => $q->whereKeyNot($ignoreReportTypeId))
            ->where('sop_code', $validated['sop_code'])
            ->where('sop_version', $validated['sop_version'])
            ->where('annex_number', $validated['annex_number'])
            ->first();
    }

    /**
     * Update an existing report type record.
     *
     * @param  array<string, mixed>  $payload
     */
    public function update(ReportType $reportType, array $payload): ReportType
    {
        $reportType->update($payload);

        return $reportType;
    }

    /**
     * Delete a report type record.
     */
    public function delete(ReportType $reportType): void
    {
        $reportType->delete();
    }

    /**
     * Check whether report type already has report instances.
     */
    public function hasReports(ReportType $reportType): bool
    {
        return $reportType->reports()->exists();
    }

    /**
     * Remove all related medium and incubator type rows for a report type.
     */
    public function clearMediumAndIncubatorTypes(ReportType $reportType): void
    {
        $reportType->mediumTypes()->delete();
        $reportType->incubatorTypes()->delete();
    }

    /**
     * Add medium type row for a report type.
     */
    public function addMediumType(ReportType $reportType, string $label): void
    {
        MediumType::create([
            'report_type_id' => $reportType->id,
            'name' => $label,
        ]);
    }

    /**
     * Add incubator type row for a report type.
     */
    public function addIncubatorType(ReportType $reportType, string $label, int $minDay): void
    {
        IncubatorType::create([
            'report_type_id' => $reportType->id,
            'temperature_label' => $label,
            'min_day' => $minDay,
        ]);
    }

    /**
     * Fetch locations list used by report type show page.
     *
     * @return Collection<int, Location>
     */
    public function locationsForShow(): Collection
    {
        return Location::query()
            ->select('locations.*')
            ->join('rooms', 'rooms.id', '=', 'locations.room_id')
            ->orderBy('rooms.class')
            ->orderBy('rooms.room_name')
            ->orderBy('locations.location_number')
            ->with('room')
            ->get();
    }
}
