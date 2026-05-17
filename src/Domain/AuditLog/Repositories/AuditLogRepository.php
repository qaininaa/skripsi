<?php

namespace Domain\AuditLog\Repositories;

use Domain\AuditLog\Dtos\CreateAuditLogDto;
use Domain\AuditLog\Interfaces\AuditLogRepositoryInterface;
use Domain\AuditLog\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Eloquent implementation of AuditLogRepositoryInterface.
 */
class AuditLogRepository implements AuditLogRepositoryInterface
{
    /**
     * Persist a new audit log entry.
     */
    public function create(CreateAuditLogDto $dto): AuditLog
    {
        return AuditLog::create($dto->toArray());
    }

    /**
     * Get paginated audit logs ordered by latest.
     *
     * @return LengthAwarePaginator<int, AuditLog>
     */
    public function paginate(int $perPage = 25): LengthAwarePaginator
    {
        return AuditLog::with('user')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
