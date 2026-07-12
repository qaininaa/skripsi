<?php

namespace Domain\AuditLog\Dtos;

/**
 * DTO for creating an audit log entry.
 */
class CreateAuditLogDto
{
    public function __construct(
        public readonly ?string $userId,
        public readonly string $action,
        public readonly string $description,
        public readonly ?string $ipAddress,
        public readonly ?string $userAgent,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'action' => $this->action,
            'description' => $this->description,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
        ];
    }
}
