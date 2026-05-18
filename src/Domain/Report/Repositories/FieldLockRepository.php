<?php

namespace Domain\Report\Repositories;

use Domain\Report\Interfaces\FieldLockRepositoryInterface;
use Domain\Report\Models\FieldLock;
use Illuminate\Database\QueryException;

/**
 * Eloquent implementation of FieldLockRepositoryInterface.
 */
class FieldLockRepository implements FieldLockRepositoryInterface
{
    /**
     * @param  array<int, string>  $fieldNames
     * @return array<string, string>
     */
    public function getOwnerMap(string $tableName, string $rowId, array $fieldNames): array
    {
        if ($fieldNames === []) {
            return [];
        }

        return FieldLock::query()
            ->where('table_name', $tableName)
            ->where('row_id', $rowId)
            ->whereIn('field_name', $fieldNames)
            ->pluck('filled_by', 'field_name')
            ->map(fn ($value) => (string) $value)
            ->all();
    }

    /**
     * @param  array<int, string>  $rowIds
     * @param  array<int, string>  $fieldNames
     * @return array<string, array<string, string>>
     */
    public function getOwnerMapByRows(string $tableName, array $rowIds, array $fieldNames): array
    {
        if ($rowIds === [] || $fieldNames === []) {
            return [];
        }

        $rows = FieldLock::query()
            ->where('table_name', $tableName)
            ->whereIn('row_id', $rowIds)
            ->whereIn('field_name', $fieldNames)
            ->get(['row_id', 'field_name', 'filled_by']);

        $ownerMap = [];
        foreach ($rows as $row) {
            $rowId = (string) $row->row_id;
            $ownerMap[$rowId] ??= [];
            $ownerMap[$rowId][(string) $row->field_name] = (string) $row->filled_by;
        }

        return $ownerMap;
    }

    public function acquireOrOwned(string $tableName, string $rowId, string $fieldName, string $userId): bool
    {
        $existing = $this->findOne($tableName, $rowId, $fieldName);
        if ($existing) {
            return (string) $existing->filled_by === $userId;
        }

        try {
            FieldLock::query()->create([
                'table_name' => $tableName,
                'row_id' => $rowId,
                'field_name' => $fieldName,
                'filled_by' => $userId,
                'filled_at' => now(),
            ]);

            return true;
        } catch (QueryException) {
            $existing = $this->findOne($tableName, $rowId, $fieldName);

            return $existing && (string) $existing->filled_by === $userId;
        }
    }

    public function releaseIfOwned(string $tableName, string $rowId, string $fieldName, string $userId): bool
    {
        $existing = $this->findOne($tableName, $rowId, $fieldName);
        if (! $existing) {
            return true;
        }

        if ((string) $existing->filled_by !== $userId) {
            return false;
        }

        $existing->delete();

        return true;
    }

    private function findOne(string $tableName, string $rowId, string $fieldName): ?FieldLock
    {
        return FieldLock::query()
            ->where('table_name', $tableName)
            ->where('row_id', $rowId)
            ->where('field_name', $fieldName)
            ->first();
    }
}
