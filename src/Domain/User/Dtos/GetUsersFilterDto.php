<?php

namespace Domain\User\Dtos;

/**
 * DTO for filtering paginated user list.
 */
class GetUsersFilterDto
{
    public function __construct(
        public readonly ?string $search = null,
        public readonly ?string $role = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            search: isset($data['search']) ? (string) $data['search'] : null,
            role: isset($data['role']) ? (string) $data['role'] : null,
        );
    }
}
