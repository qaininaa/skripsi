<?php

namespace App\Domains\Report\Services;

use App\Domains\Report\Repositories\FieldLockRepository;
use App\Domains\Report\DTOs\InstrumentIdentityEntryData;
use App\Domains\Report\Repositories\InstrumentIdentityEntryRepository;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Service for instrument identity entry persistence.
 */
class InstrumentIdentityEntryService
{
    private const LOCK_TABLE_NAME = 'instrument_entries';

    /** @var array<int, string> */
    private const LOCKABLE_FIELDS = ['no_id', 'calibration_date', 'due_date'];

    public function __construct(
        private InstrumentIdentityEntryRepository $repository,
        private FieldLockRepository $fieldLockRepository,
    ) {}

    public function saveFromRequest(Request $request, Report $report): void
    {
        if (! $request->has('air_sampler')) {
            return;
        }

        $payload = (array) $request->input('air_sampler', []);
        $this->saveFromArray($payload, $report);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function saveFromArray(array $payload, Report $report): void
    {
        $data = InstrumentIdentityEntryData::fromArray($payload);
        $entry = $this->repository->findOrCreateForReportTool($report, $data->toolName);

        $incoming = [
            'no_id' => $data->noId,
            'calibration_date' => $data->calibrationDate,
            'due_date' => $data->dueDate,
        ];

        $user = Auth::user();
        if (($user?->role ?? null) !== 'analis') {
            $this->repository->updateFields($entry, $incoming);

            return;
        }

        $userId = (string) $user->id;
        $allowedUpdates = [];

        foreach (self::LOCKABLE_FIELDS as $fieldName) {
            $newValue = $this->normalizeValue($incoming[$fieldName] ?? null);
            $currentValue = $this->normalizeValue($entry->{$fieldName});

            if ($newValue === $currentValue) {
                continue;
            }

            $canWrite = $this->fieldLockRepository->acquireOrOwned(
                self::LOCK_TABLE_NAME,
                (string) $entry->id,
                $fieldName,
                $userId
            );

            if (! $canWrite) {
                continue;
            }

            if ($newValue === null) {
                $this->fieldLockRepository->releaseIfOwned(
                    self::LOCK_TABLE_NAME,
                    (string) $entry->id,
                    $fieldName,
                    $userId
                );
            }

            $allowedUpdates[$fieldName] = $newValue;
        }

        $this->repository->updateFields($entry, $allowedUpdates);
    }

    /**
     * @return array<string, string>
     */
    public function getFieldLocksForRowId(?string $rowId): array
    {
        if (! $rowId) {
            return [];
        }

        return $this->fieldLockRepository->getOwnerMap(
            self::LOCK_TABLE_NAME,
            $rowId,
            self::LOCKABLE_FIELDS
        );
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
