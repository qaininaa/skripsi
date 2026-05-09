<?php

namespace App\Domains\ReportEntry\IncubatorEntry\Services;

use App\Domains\ReportEntry\FieldLock\Repositories\FieldLockRepository;
use App\Domains\ReportEntry\IncubatorEntry\Repositories\IncubatorEntryRepository;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Service for incubator and incubator entries persistence.
 */
class IncubatorEntryService
{
    private const LOCK_TABLE_INCUBATORS = 'incubators';
    private const LOCK_TABLE_INCUBATOR_ENTRIES = 'incubator_entries';

    /** @var array<int, string> */
    private const LOCKABLE_INCUBATOR_FIELDS = ['no_id', 'calibration_date', 'due_date_calibration'];

    /** @var array<int, string> */
    private const LOCKABLE_INCUBATOR_ENTRY_FIELDS = [
        'incubated_by',
        'date_in',
        'time_in',
        'removed_by',
        'date_out',
        'time_out',
    ];

    public function __construct(
        private IncubatorEntryRepository $repository,
        private FieldLockRepository $fieldLockRepository,
    ) {}

    public function saveFromRequest(Request $request, Report $report): void
    {
        if (! $request->has('incubator')) {
            return;
        }

        $this->saveFromArray((array) $request->input('incubator', []), $report);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function saveFromArray(array $payload, Report $report): void
    {
        if ($payload === []) {
            return;
        }

        $report->loadMissing('reportType.incubatorTypes');

        foreach ($payload as $reportTypeIncubatorId => $data) {
            if (! is_array($data)) {
                continue;
            }

            $incubatorType = $report->reportType->incubatorTypes->firstWhere('id', $reportTypeIncubatorId);
            if (! $incubatorType) {
                continue;
            }

            $incubator = $this->repository->findOrCreateIncubatorForReportType($report, (string) $incubatorType->id);

            $incomingIncubator = [
                'report_type_incubator_id' => $incubatorType->id,
                'no_id' => $this->normalizeValue($data['no_id'] ?? null),
                'calibration_date' => $this->normalizeValue($data['calibration_date'] ?? null),
                'due_date_calibration' => $this->normalizeValue($data['due_date_calibration'] ?? ($data['due_date'] ?? null)),
            ];

            $this->persistIncubatorWithLocks($incubator, $incomingIncubator);

            foreach ($this->extractEntryPayloads($data) as $mediumType => $entryData) {
                if (! in_array((string) $mediumType, ['monitoring', 'swab'], true) || ! is_array($entryData)) {
                    continue;
                }

                $entry = $this->repository->findOrCreateIncubatorMediumEntry($incubator, (string) $mediumType);

                $incomingEntry = [
                    'incubated_by' => $this->normalizeValue($entryData['incubated_by'] ?? null),
                    'date_in' => $this->normalizeValue($entryData['date_in'] ?? null),
                    'time_in' => $this->normalizeValue($entryData['time_in'] ?? null),
                    'removed_by' => $this->normalizeValue($entryData['removed_by'] ?? null),
                    'date_out' => $this->normalizeValue($entryData['date_out'] ?? null),
                    'time_out' => $this->normalizeValue($entryData['time_out'] ?? null),
                ];

                $this->persistIncubatorEntryWithLocks($entry, $incomingEntry);
            }
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Domains\ReportEntry\IncubatorEntry\Models\Incubator>  $incubators
     * @return array<string, array<string, mixed>>
     */
    public function getFieldLocksByIncubatorConfigId($incubators): array
    {
        $result = [];

        foreach ($incubators as $incubator) {
            if (! $incubator?->id || ! $incubator?->report_type_incubator_id) {
                continue;
            }

            $configId = (string) $incubator->report_type_incubator_id;

            $result[$configId] = [
                'info' => $this->fieldLockRepository->getOwnerMap(
                    self::LOCK_TABLE_INCUBATORS,
                    (string) $incubator->id,
                    self::LOCKABLE_INCUBATOR_FIELDS
                ),
                'entries' => [],
            ];

            foreach (($incubator->entries ?? collect()) as $entry) {
                if (! $entry?->id || ! $entry?->medium_type) {
                    continue;
                }

                $result[$configId]['entries'][(string) $entry->medium_type] = $this->fieldLockRepository->getOwnerMap(
                    self::LOCK_TABLE_INCUBATOR_ENTRIES,
                    (string) $entry->id,
                    self::LOCKABLE_INCUBATOR_ENTRY_FIELDS
                );
            }
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $incoming
     */
    private function persistIncubatorWithLocks($incubator, array $incoming): void
    {
        $user = Auth::user();
        if (($user?->role ?? null) !== 'analis') {
            $this->repository->updateIncubatorFields($incubator, $incoming);

            return;
        }

        $userId = (string) $user->id;
        $allowedUpdates = [
            'report_type_incubator_id' => $incoming['report_type_incubator_id'],
        ];

        foreach (self::LOCKABLE_INCUBATOR_FIELDS as $fieldName) {
            $newValue = $this->normalizeValue($incoming[$fieldName] ?? null);
            $currentValue = $this->normalizeValue($incubator->{$fieldName});

            if ($newValue === $currentValue) {
                continue;
            }

            $canWrite = $this->fieldLockRepository->acquireOrOwned(
                self::LOCK_TABLE_INCUBATORS,
                (string) $incubator->id,
                $fieldName,
                $userId
            );

            if (! $canWrite) {
                continue;
            }

            if ($newValue === null) {
                $this->fieldLockRepository->releaseIfOwned(
                    self::LOCK_TABLE_INCUBATORS,
                    (string) $incubator->id,
                    $fieldName,
                    $userId
                );
            }

            $allowedUpdates[$fieldName] = $newValue;
        }

        $this->repository->updateIncubatorFields($incubator, $allowedUpdates);
    }

    /**
     * @param  array<string, mixed>  $incoming
     */
    private function persistIncubatorEntryWithLocks($entry, array $incoming): void
    {
        $user = Auth::user();
        if (($user?->role ?? null) !== 'analis') {
            $this->repository->updateIncubatorEntryFields($entry, $incoming);

            return;
        }

        $userId = (string) $user->id;
        $allowedUpdates = [];

        foreach (self::LOCKABLE_INCUBATOR_ENTRY_FIELDS as $fieldName) {
            $newValue = $this->normalizeValue($incoming[$fieldName] ?? null);
            $currentValue = $this->normalizeValue($entry->{$fieldName});

            if ($newValue === $currentValue) {
                continue;
            }

            $canWrite = $this->fieldLockRepository->acquireOrOwned(
                self::LOCK_TABLE_INCUBATOR_ENTRIES,
                (string) $entry->id,
                $fieldName,
                $userId
            );

            if (! $canWrite) {
                continue;
            }

            if ($newValue === null) {
                $this->fieldLockRepository->releaseIfOwned(
                    self::LOCK_TABLE_INCUBATOR_ENTRIES,
                    (string) $entry->id,
                    $fieldName,
                    $userId
                );
            }

            $allowedUpdates[$fieldName] = $newValue;
        }

        $this->repository->updateIncubatorEntryFields($entry, $allowedUpdates);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, array<string, mixed>>
     */
    private function extractEntryPayloads(array $payload): array
    {
        $entryPayloads = [];

        foreach ($payload as $mediumType => $entryData) {
            if (is_array($entryData)) {
                $entryPayloads[(string) $mediumType] = $entryData;
            }
        }

        if ($entryPayloads !== []) {
            return $entryPayloads;
        }

        if (
            isset($payload['incubated_by']) || isset($payload['date_in']) || isset($payload['time_in']) ||
            isset($payload['removed_by']) || isset($payload['date_out']) || isset($payload['time_out'])
        ) {
            return [
                'monitoring' => [
                    'incubated_by' => $payload['incubated_by'] ?? null,
                    'date_in' => $payload['date_in'] ?? null,
                    'time_in' => $payload['time_in'] ?? null,
                    'removed_by' => $payload['removed_by'] ?? null,
                    'date_out' => $payload['date_out'] ?? null,
                    'time_out' => $payload['time_out'] ?? null,
                ],
            ];
        }

        return [];
    }

    private function normalizeValue(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        $trimmed = trim((string) ($value ?? ''));

        return $trimmed !== '' ? $trimmed : null;
    }
}
