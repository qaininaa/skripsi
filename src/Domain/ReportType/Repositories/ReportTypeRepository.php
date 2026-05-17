<?php

namespace Domain\ReportType\Repositories;

use Domain\Location\Models\Location;
use Domain\ReportType\Interfaces\ReportTypeRepositoryInterface;
use Domain\ReportType\Models\IncubatorType;
use Domain\ReportType\Models\MediumType;
use Domain\ReportType\Models\ReportType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Eloquent implementation of ReportTypeRepositoryInterface.
 */
class ReportTypeRepository implements ReportTypeRepositoryInterface
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
     * Find duplicate report type by SOP code, SOP version, and annex number.
     */
    public function findDuplicate(string $sopCode, string $sopVersion, int $annexNumber, ?string $ignoreReportTypeId = null): ?ReportType
    {
        return ReportType::query()
            ->when($ignoreReportTypeId, fn ($q) => $q->whereKeyNot($ignoreReportTypeId))
            ->where('sop_code', $sopCode)
            ->where('sop_version', $sopVersion)
            ->where('annex_number', $annexNumber)
            ->first();
    }

    /**
     * Persist a new report type record.
     *
     * @param  array{sop_code: string, sop_version: string, name: string, annex_number: int}  $payload
     */
    public function create(array $payload): ReportType
    {
        return ReportType::create($payload);
    }

    /**
     * Update an existing report type record.
     *
     * @param  array{sop_code: string, sop_version: string, name: string, annex_number: int}  $payload
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
     * Remove related medium and incubator type rows for a report type.
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
