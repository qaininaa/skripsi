<?php

namespace Domain\Report\Interfaces;

/**
 * Contract for field-lock data access.
 */
interface FieldLockRepositoryInterface
{
    /**
     * Get owner map for given fields on a row.
     *
     * @param  array<int, string>  $fieldNames
     * @return array<string, string>
     */
    public function getOwnerMap(string $tableName, string $rowId, array $fieldNames): array;

    /**
     * Get owner maps for multiple rows.
     *
     * @param  array<int, string>  $rowIds
     * @param  array<int, string>  $fieldNames
     * @return array<string, array<string, string>>
     */
    public function getOwnerMapByRows(string $tableName, array $rowIds, array $fieldNames): array;

    /**
     * Acquire lock or confirm ownership.
     */
    public function acquireOrOwned(string $tableName, string $rowId, string $fieldName, string $userId): bool;

    /**
     * Release lock if currently owned.
     */
    public function releaseIfOwned(string $tableName, string $rowId, string $fieldName, string $userId): bool;
}
