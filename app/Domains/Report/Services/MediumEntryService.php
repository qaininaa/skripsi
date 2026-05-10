<?php

namespace App\Domains\Report\Services;

use App\Domains\Report\Repositories\FieldLockRepository;
use App\Domains\Report\DTOs\MediumEntryData;
use App\Domains\Report\Repositories\MediumEntryRepository;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Service for medium identity entry persistence.
 */
class MediumEntryService
{
    private const LOCK_TABLE_NAME = 'medium_identities';

    /** @var array<int, string> */
    private const LOCKABLE_FIELDS = ['batch_number', 'gpt_number', 'expiration_date'];

    public function __construct(
        private MediumEntryRepository $repository,
        private FieldLockRepository $fieldLockRepository,
    ) {}

    public function saveFromRequest(Request $request, Report $report): void
    {
        if (! $request->has('medium')) {
            return;
        }

        $this->saveFromArray((array) $request->input('medium', []), $report);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function saveFromArray(array $payload, Report $report): void
    {
        if ($payload === []) {
            return;
        }

        $report->loadMissing('reportType.mediumTypes');

        foreach ($payload as $name => $item) {
            if (! is_array($item)) {
                continue;
            }

            $data = MediumEntryData::fromArray((string) $name, $item);
            if ($data->name === '') {
                continue;
            }

            $mediumType = $report->reportType->mediumTypes->firstWhere('name', $data->name);
            if (! $mediumType) {
                continue;
            }

            $entry = $this->repository->findOrCreateForReportMedium($report, $data->name, (string) $mediumType->id);

            $incoming = [
                'medium_id' => $mediumType->id,
                'batch_number' => $data->batchNumber,
                'gpt_number' => $data->gptNumber,
                'expiration_date' => $data->expirationDate,
            ];

            $user = Auth::user();
            if (($user?->role ?? null) !== 'analis') {
                $this->repository->updateFields($entry, $incoming);

                continue;
            }

            $userId = (string) $user->id;
            $allowedUpdates = [
                'medium_id' => $mediumType->id,
            ];

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
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Domains\Report\Models\MediumEntry>  $mediumRows
     * @return array<string, array<string, string>>
     */
    public function getFieldLocksByMediumName($mediumRows): array
    {
        $result = [];

        foreach ($mediumRows as $row) {
            if (! $row?->id || ! $row?->name) {
                continue;
            }

            $result[(string) $row->name] = $this->fieldLockRepository->getOwnerMap(
                self::LOCK_TABLE_NAME,
                (string) $row->id,
                self::LOCKABLE_FIELDS
            );
        }

        return $result;
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
