<?php

namespace App\Domains\ReportType\Repositories;

use App\Domains\Location\Models\Location;
use App\Domains\ReportType\Models\ReportType;
use App\Models\IncubatorType;
use App\Models\MediumType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ReportTypeRepository
{
    public function paginateForManagement(int $perPage = 10): LengthAwarePaginator
    {
        return ReportType::query()
            ->withCount('sections')
            ->orderBy('annex_number')
            ->paginate($perPage);
    }

    public function create(array $payload): ReportType
    {
        return ReportType::create($payload);
    }

    public function update(ReportType $reportType, array $payload): ReportType
    {
        $reportType->update($payload);

        return $reportType;
    }

    public function delete(ReportType $reportType): void
    {
        $reportType->delete();
    }

    public function hasReports(ReportType $reportType): bool
    {
        return $reportType->reports()->exists();
    }

    public function clearMediumAndIncubatorTypes(ReportType $reportType): void
    {
        $reportType->mediumTypes()->delete();
        $reportType->incubatorTypes()->delete();
    }

    public function addMediumType(ReportType $reportType, string $label): void
    {
        MediumType::create([
            'report_type_id' => $reportType->id,
            'name' => $label,
        ]);
    }

    public function addIncubatorType(ReportType $reportType, string $label, int $minDay): void
    {
        IncubatorType::create([
            'report_type_id' => $reportType->id,
            'temperature_label' => $label,
            'min_day' => $minDay,
        ]);
    }

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
