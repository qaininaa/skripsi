<?php

namespace Domain\AuditLog\Services;

use Domain\AuditLog\Dtos\CreateAuditLogDto;
use Domain\AuditLog\Interfaces\AuditLogRepositoryInterface;
use Domain\AuditLog\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Service to write and read audit logs.
 */
class AuditLogService
{
    public function __construct(private AuditLogRepositoryInterface $repository) {}

    /**
     * Convenience writer used across domains.
     *
     * @param  array{user_id?: string|null, ip_address?: string|null, user_agent?: string|null}  $meta
     */
    public function log(string $action, string $description, array $meta = []): AuditLog
    {
        return $this->repository->create(new CreateAuditLogDto(
            userId: $meta['user_id'] ?? null,
            action: $action,
            description: $description,
            ipAddress: $meta['ip_address'] ?? null,
            userAgent: $meta['user_agent'] ?? null,
        ));
    }

    /**
     * Get paginated audit logs ordered by latest.
     *
     * @return LengthAwarePaginator<int, AuditLog>
     */
    public function paginate(int $perPage = 25): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }
}
