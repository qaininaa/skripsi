<?php

namespace App\Domains\ReportEntry\FieldLock\Repositories;

use App\Domains\ReportEntry\FieldLock\Models\FieldLock;
use Illuminate\Database\QueryException;

class FieldLockRepository
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

    private function findOne(string $tableName, string $rowId, string $fieldName): ?FieldLock
    {
        return FieldLock::query()
            ->where('table_name', $tableName)
            ->where('row_id', $rowId)
            ->where('field_name', $fieldName)
            ->first();
    }
}
