<?php

namespace App\Services;

use Domain\Report\Models\EnvSectionInstance;
use Domain\Report\Models\Report;
use Domain\Location\Models\Location;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * SectionInstanceService
 *
 * Source of truth jumlah instance section adalah tabel env_section_instances.
 * - Instance original: parent_instance_id = null
 * - Instance duplikat: parent_instance_id = id original
 */
class SectionInstanceService
{
    /**
     * Pastikan setiap section pada report punya minimal 1 instance original.
     */
    public function ensureInstancesInitialized(Report $report): void
    {
        $report->loadMissing('reportType.sections');

        foreach ($report->reportType->sections as $section) {
            $currentCount = $this->resolveCurrentInstanceCount($report, (string) $section->id);
            $this->syncEnvInstancesForSection($report, (string) $section->id, max(1, $currentCount));
        }
    }

    /**
     * Tambah satu duplikasi untuk satu section.
     *
     * @return array{ok: bool, message: string}
     */
    public function duplicate(Report $report, string $sectionId): array
    {
        if (! $this->sectionBelongsToReport($report, $sectionId)) {
            return ['ok' => false, 'message' => 'Section tidak ditemukan.'];
        }

        // if ($this->getLocationIdsForSection($sectionId)->isEmpty()) {
        //     return ['ok' => false, 'message' => 'section belum memiliki lokasi. Tambahkan lokasi terlebih dahulu di master report type.'];
        // }

        $currentCount = $this->resolveCurrentInstanceCount($report, $sectionId);
        if ($currentCount <= 0) {
            $this->syncEnvInstancesForSection($report, $sectionId, 1);
            $currentCount = 1;
        }

        if ($currentCount >= 5) {
            return ['ok' => false, 'message' => 'Maksimum 5 instance per section.'];
        }

        $this->syncEnvInstancesForSection($report, $sectionId, $currentCount + 1);

        return ['ok' => true, 'message' => 'Section berhasil diduplikat.'];
    }

    /**
     * Hapus satu duplikasi section (minimum tersisa 1 original).
     *
     * @return array{ok: bool, message: string}
     */
    public function remove(Report $report, string $sectionId): array
    {
        if (! $this->sectionBelongsToReport($report, $sectionId)) {
            return ['ok' => false, 'message' => 'Section tidak ditemukan.'];
        }

        if ($this->getLocationIdsForSection($sectionId)->isEmpty()) {
            return ['ok' => false, 'message' => 'Section belum memiliki lokasi.'];
        }

        $currentCount = $this->resolveCurrentInstanceCount($report, $sectionId);
        if ($currentCount <= 1) {
            return ['ok' => false, 'message' => 'Tidak ada duplikasi untuk dihapus.'];
        }

        $this->syncEnvInstancesForSection($report, $sectionId, $currentCount - 1);

        return ['ok' => true, 'message' => 'Duplikasi section berhasil dihapus.'];
    }

    /**
     * Backward-compatible alias.
     */
    public function createMissingInstances(Report $report): void
    {
        $this->ensureInstancesInitialized($report);
    }

    private function sectionBelongsToReport(Report $report, string $sectionId): bool
    {
        $report->loadMissing('reportType.sections');

        return $report->reportType->sections
            ->contains(fn ($section) => (string) $section->id === (string) $sectionId);
    }

    private function resolveCurrentInstanceCount(Report $report, string $sectionId): int
    {
        $locationIds = $this->getLocationIdsForSection($sectionId);
        if ($locationIds->isEmpty()) {
            return 0;
        }

        $countsByLocation = EnvSectionInstance::query()
            ->where('report_id', $report->id)
            ->whereIn('location_id', $locationIds->all())
            ->selectRaw('location_id, COUNT(*) as total')
            ->groupBy('location_id')
            ->pluck('total', 'location_id');

        $current = 0;
        foreach ($locationIds as $locationId) {
            $current = max($current, (int) ($countsByLocation[$locationId] ?? 0));
        }

        return $current;
    }

    /**
     * Sinkronkan jumlah instance per lokasi pada satu section.
     */
    private function syncEnvInstancesForSection(Report $report, string $sectionId, int $targetCount): void
    {
        $targetCount = max(1, min(5, $targetCount));
        $locationIds = $this->getLocationIdsForSection($sectionId);

        if ($locationIds->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($report, $locationIds, $targetCount): void {
            foreach ($locationIds as $locationId) {
                $baseQuery = EnvSectionInstance::query()
                    ->where('report_id', $report->id)
                    ->where('location_id', $locationId);

                $originals = (clone $baseQuery)
                    ->whereNull('parent_instance_id')
                    ->orderBy('created_at')
                    ->orderBy('id')
                    ->get();

                $original = $originals->first();
                if (! $original) {
                    $original = EnvSectionInstance::create([
                        'report_id' => $report->id,
                        'location_id' => $locationId,
                        'parent_instance_id' => null,
                        'reason' => null,
                    ]);
                }

                // Jika ada lebih dari satu "original", sisanya jadikan duplikat.
                $originals->slice(1)->each(function (EnvSectionInstance $extra) use ($original): void {
                    $extra->parent_instance_id = $original->id;
                    $extra->save();
                });

                $duplicates = (clone $baseQuery)
                    ->whereNotNull('parent_instance_id')
                    ->orderBy('created_at')
                    ->orderBy('id')
                    ->get();

                $desiredDuplicates = $targetCount - 1;
                $currentDuplicates = $duplicates->count();

                if ($currentDuplicates < $desiredDuplicates) {
                    for ($i = 0; $i < ($desiredDuplicates - $currentDuplicates); $i++) {
                        EnvSectionInstance::create([
                            'report_id' => $report->id,
                            'location_id' => $locationId,
                            'parent_instance_id' => $original->id,
                            'reason' => null,
                        ]);
                    }
                    continue;
                }

                if ($currentDuplicates > $desiredDuplicates) {
                    $toDelete = $duplicates
                        ->reverse()
                        ->take($currentDuplicates - $desiredDuplicates);

                    foreach ($toDelete as $instance) {
                        $instance->delete();
                    }
                }
            }
        });
    }

    /**
     * @return Collection<int, string>
     */
    private function getLocationIdsForSection(string $sectionId): Collection
    {
        return Location::query()
            ->where('section_id', $sectionId)
            ->pluck('id');
    }
}