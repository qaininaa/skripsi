<?php

namespace App\Domains\Report\Services;

use App\Domains\Report\Repositories\ReportEntryRepository;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * PersonnelEntryService
 *
 * Fokus ke persistence data entry untuk:
 * - label kolom section (column_names)
 * - catatan/kesimpulan per section (section_notes)
 * - pemantauan personel (page_notes, personnel rows, sampling entries)
 */
class PersonnelEntryService
{
    public function __construct(private ReportEntryRepository $repository) {}

    public function saveSectionColumnNames(Request $request, Report $report): void
    {
        $columnNames = $request->input('column_names', []);
        if (! is_array($columnNames) || empty($columnNames)) {
            return;
        }

        foreach ($columnNames as $sectionId => $instanceData) {
            if (! is_array($instanceData) || empty($instanceData)) {
                continue;
            }

            $firstKey = array_key_first($instanceData);
            $isFlatColumns = $firstKey !== null && ! is_array($instanceData[$firstKey]);
            if ($isFlatColumns) {
                $instanceData = [1 => $instanceData];
            }

            foreach ($instanceData as $instanceNum => $columns) {
                if (! is_array($columns) || empty($columns)) {
                    continue;
                }

                $instanceNumber = max(1, (int) $instanceNum);

                foreach ($columns as $period => $label) {
                    $periodNumber = (int) $period;
                    if ($periodNumber < 1) {
                        continue;
                    }

                    $value = is_string($label) ? trim($label) : null;

                    $this->repository->upsertSectionColumn(
                        (string) $report->id,
                        (string) $sectionId,
                        $instanceNumber,
                        $periodNumber,
                        $value !== '' ? $value : null
                    );
                }
            }
        }
    }

    public function saveSectionNotes(Request $request, Report $report): void
    {
        $sectionNotes = $request->input('section_notes', []);
        if (! is_array($sectionNotes) || empty($sectionNotes)) {
            return;
        }

        foreach ($sectionNotes as $sectionId => $instanceData) {
            if (! is_array($instanceData) || empty($instanceData)) {
                continue;
            }

            $firstKey = array_key_first($instanceData);
            $isFlatNote = $firstKey !== null && ! is_array($instanceData[$firstKey]);
            if ($isFlatNote) {
                $instanceData = [1 => $instanceData];
            }

            foreach ($instanceData as $instanceNum => $noteData) {
                if (! is_array($noteData)) {
                    continue;
                }

                $instanceNumber = max(1, (int) $instanceNum);
                $notes = is_string($noteData['notes'] ?? null) ? trim($noteData['notes']) : null;
                $conclusionRaw = is_string($noteData['conclusion'] ?? null)
                    ? strtoupper(trim($noteData['conclusion']))
                    : null;
                $conclusion = in_array($conclusionRaw, ['MS', 'TMS'], true) ? $conclusionRaw : null;

                $this->repository->upsertSectionNote(
                    (string) $report->id,
                    (string) $sectionId,
                    $instanceNumber,
                    $notes !== '' ? $notes : null,
                    $conclusion
                );
            }
        }
    }

    public function savePersonnel(Request $request, Report $report): bool
    {
        $hasPersonnelData = false;

        if ($request->has('page_notes')) {
            $report->loadMissing('reportType.personnelMethods');
            $methodIds = $report->reportType->personnelMethods->pluck('id')->all();

            foreach ($request->input('page_notes', []) as $pageNum => $noteData) {
                $page = (int) $pageNum;
                if ($page < 1) {
                    continue;
                }

                $note = trim($noteData['note'] ?? '');
                $deviation = trim($noteData['deviation'] ?? '');

                foreach ($methodIds as $methodId) {
                    $instance = $this->repository->firstOrCreatePersonnelInstance(
                        (string) $report->id,
                        (string) $methodId,
                        $page
                    );

                    $instance->note = $note !== '' ? $note : null;
                    $instance->deviation = $deviation !== '' ? $deviation : null;
                    $instance->save();
                }
            }
        }

        foreach ($request->input('personnel', []) as $instId => $instData) {
            if (str_starts_with((string) $instId, '_new_')) {
                if (! preg_match('/_new_p(\d+)_m([0-9a-f\-]+)/i', (string) $instId, $m)) {
                    continue;
                }
                $instance = $this->repository->firstOrCreatePersonnelInstance(
                    (string) $report->id,
                    (string) $m[2],
                    (int) $m[1]
                );
            } else {
                $instance = $this->repository->findPersonnelInstance((string) $report->id, (string) $instId);
                if (! $instance) {
                    continue;
                }
            }

            foreach ($instData['row'] ?? [] as $rowOrder => $rowData) {
                if (! is_array($rowData)) {
                    continue;
                }

                $nameInputProvided = array_key_exists('name', $rowData);
                $timeInputProvided = array_key_exists('time', $rowData);
                $classInputProvided = array_key_exists('class', $rowData);
                $activitiesInputProvided = array_key_exists('activities', $rowData);

                $name = $nameInputProvided ? trim((string) ($rowData['name'] ?? '')) : '';
                $time = $timeInputProvided ? ($rowData['time'] ?? '') : '';
                $cls = $classInputProvided ? ($rowData['class'] ?? null) : null;

                if ($name === '' && $time === '' && empty($rowData['cfu'] ?? [])) {
                    continue;
                }

                $hasPersonnelData = true;

                $row = $this->repository->firstOrNewPersonnelRow((string) $instance->id, (int) $rowOrder);

                if ($nameInputProvided) {
                    $row->personnel_name = $name ?: null;
                }
                if ($timeInputProvided) {
                    $row->monitoring_time = $time ?: null;
                }
                if ($classInputProvided) {
                    $row->class = $cls ?: null;
                }
                if ($activitiesInputProvided) {
                    $row->activities = array_values($rowData['activities'] ?? []);
                }

                $row->filled_by = Auth::id();
                $row->save();

                foreach ($rowData['cfu'] ?? [] as $pointId => $cfuData) {
                    $b = self::normalizeCfu($cfuData['b'] ?? null);
                    $f = self::normalizeCfu($cfuData['f'] ?? null);
                    $t = self::normalizeCfu($cfuData['t'] ?? null);
                    $k = in_array($cfuData['kesimpulan'] ?? '', ['MS', 'TMS'], true)
                        ? $cfuData['kesimpulan']
                        : null;

                    if ($b === null && $f === null && $t === null && $k === null) {
                        continue;
                    }

                    $this->repository->upsertPersonnelSamplingEntry(
                        (string) $row->id,
                        (string) $pointId,
                        [
                            'cfu_bacteria' => $b,
                            'cfu_fungi' => $f,
                            'cfu_total' => $t,
                            'kesimpulan' => $k,
                        ]
                    );
                }
            }
        }

        return $hasPersonnelData;
    }

    private static function normalizeCfu(mixed $raw): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        $v = trim((string) $raw);
        if ($v === '') {
            return null;
        }
        if ($v === '<1') {
            return '<1';
        }
        if (strtoupper($v) === 'TNTC') {
            return 'TNTC';
        }
        if (preg_match('/^[1-9][0-9]*$/', $v)) {
            return $v;
        }

        return null;
    }
}
