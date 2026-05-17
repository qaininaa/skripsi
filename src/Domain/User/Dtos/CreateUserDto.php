<?php

namespace Domain\User\Dtos;

/**
 * DTO for creating a new user.
 */
class CreateUserDto
{
    public function __construct(
        public readonly string $name,
        public readonly string $username,
        public readonly string $role,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            username: (string) $data['username'],
            role: (string) $data['role'],
        );
    }

    /**
     * Get base payload for user creation (without password and timestamps).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'username' => $this->username,
            'role' => $this->role,
        ];
    }
}
