<?php

namespace Domain\AuditLog\Interfaces;

use Domain\AuditLog\Dtos\CreateAuditLogDto;
use Domain\AuditLog\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Contract for AuditLog data access.
 */
interface AuditLogRepositoryInterface
{
    /**
     * Persist a new audit log entry.
     */
    public function create(CreateAuditLogDto $dto): AuditLog;

    /**
     * Get paginated audit logs ordered by latest.
     *
     * @return LengthAwarePaginator<int, AuditLog>
     */
    public function paginate(int $perPage = 25): LengthAwarePaginator;
}
