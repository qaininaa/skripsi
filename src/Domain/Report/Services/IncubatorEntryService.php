<?php

namespace Domain\Report\Services;

use Domain\Report\Interfaces\FieldLockRepositoryInterface;
use Domain\Report\Interfaces\IncubatorEntryRepositoryInterface;
use Domain\Report\Models\Report;
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
        private IncubatorEntryRepositoryInterface $repository,
        private FieldLockRepositoryInterface $fieldLockRepository,
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

        $user = Auth::user();
        $isAnalyst = ($user?->role ?? null) === 'analyst';
        $currentUserId = (string) ($user?->id ?? '');

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

                $dateIn = $this->normalizeValue($entryData['date_in'] ?? null);
                $timeIn = $this->normalizeValue($entryData['time_in'] ?? null);
                $dateOut = $this->normalizeValue($entryData['date_out'] ?? null);
                $timeOut = $this->normalizeValue($entryData['time_out'] ?? null);

                $inHasAny = $dateIn !== null || $timeIn !== null;
                $outHasAny = $dateOut !== null || $timeOut !== null;

                $incubatedBy = $isAnalyst
                    ? ($inHasAny ? $currentUserId : null)
                    : (array_key_exists('incubated_by', $entryData)
                        ? $this->normalizeValue($entryData['incubated_by'] ?? null)
                        : $this->normalizeValue($entry->incubated_by));

                $removedBy = $isAnalyst
                    ? ($outHasAny ? $currentUserId : null)
                    : (array_key_exists('removed_by', $entryData)
                        ? $this->normalizeValue($entryData['removed_by'] ?? null)
                        : $this->normalizeValue($entry->removed_by));

                $incomingEntry = [
                    'incubated_by' => $incubatedBy,
                    'date_in' => $dateIn,
                    'time_in' => $timeIn,
                    'removed_by' => $removedBy,
                    'date_out' => $dateOut,
                    'time_out' => $timeOut,
                ];

                $this->persistIncubatorEntryWithLocks($entry, $incomingEntry);
            }
        }
    }

    /**
     * Validate required date-time pairing for finish monitoring action.
     *
     * @return array{0: array<string, string>, 1: ?string}
     */
    public function validateMonitoringCompletion(Request $request, Report $report): array
    {
        $errors = [];
        $firstMissingKey = null;

        if (! $request->has('incubator')) {
            return [$errors, $firstMissingKey];
        }

        $report->loadMissing('reportType.incubatorTypes');
        $payload = (array) $request->input('incubator', []);

        foreach ($payload as $configId => $data) {
            if (! is_array($data)) {
                continue;
            }

            $incubatorType = $report->reportType->incubatorTypes->firstWhere('id', $configId);
            if (! $incubatorType) {
                continue;
            }

            foreach ($this->extractEntryPayloads($data) as $mediumType => $entryData) {
                if (! in_array((string) $mediumType, ['monitoring', 'swab'], true) || ! is_array($entryData)) {
                    continue;
                }

                $dateIn = $this->normalizeValue($entryData['date_in'] ?? null);
                $timeIn = $this->normalizeValue($entryData['time_in'] ?? null);
                $dateOut = $this->normalizeValue($entryData['date_out'] ?? null);
                $timeOut = $this->normalizeValue($entryData['time_out'] ?? null);

                if (($dateIn !== null) xor ($timeIn !== null)) {
                    $missingField = $dateIn === null ? 'date_in' : 'time_in';
                    $key = "incubator.{$configId}.{$mediumType}.{$missingField}";
                    $errors[$key] = 'Tanggal masuk dan jam masuk harus diisi berpasangan.';
                    $errors['incubator_incomplete'] = 'Lengkapi tanggal dan jam inkubasi yang masih kosong sebelum menyelesaikan monitoring.';
                    $firstMissingKey ??= $key;
                }

                if (($dateOut !== null) xor ($timeOut !== null)) {
                    $missingField = $dateOut === null ? 'date_out' : 'time_out';
                    $key = "incubator.{$configId}.{$mediumType}.{$missingField}";
                    $errors[$key] = 'Tanggal keluar dan jam keluar harus diisi berpasangan.';
                    $errors['incubator_incomplete'] = 'Lengkapi tanggal dan jam inkubasi yang masih kosong sebelum menyelesaikan monitoring.';
                    $firstMissingKey ??= $key;
                }
            }
        }

        return [$errors, $firstMissingKey];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \Domain\Report\Models\Incubator>  $incubators
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
        if (($user?->role ?? null) !== 'analyst') {
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
        if (($user?->role ?? null) !== 'analyst') {
            $this->repository->updateIncubatorEntryFields($entry, $incoming);

            return;
        }

        $userId = (string) $user->id;
        $allowedUpdates = [];

        $pairOwners = $this->fieldLockRepository->getOwnerMap(
            self::LOCK_TABLE_INCUBATOR_ENTRIES,
            (string) $entry->id,
            ['date_in', 'time_in', 'date_out', 'time_out']
        );
        $inPairOwner = $pairOwners['date_in'] ?? $pairOwners['time_in'] ?? null;
        $outPairOwner = $pairOwners['date_out'] ?? $pairOwners['time_out'] ?? null;

        foreach (self::LOCKABLE_INCUBATOR_ENTRY_FIELDS as $fieldName) {
            $newValue = $this->normalizeValue($incoming[$fieldName] ?? null);
            $currentValue = $this->normalizeValue($entry->{$fieldName});

            if ($newValue === $currentValue) {
                continue;
            }

            if (in_array($fieldName, ['incubated_by', 'date_in', 'time_in'], true)
                && $inPairOwner !== null
                && (string) $inPairOwner !== $userId) {
                continue;
            }

            if (in_array($fieldName, ['removed_by', 'date_out', 'time_out'], true)
                && $outPairOwner !== null
                && (string) $outPairOwner !== $userId) {
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
