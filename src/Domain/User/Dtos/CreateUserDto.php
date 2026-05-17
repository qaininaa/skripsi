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
        public readonly string $password,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            username: (string) $data['username'],
            role: (string) $data['role'],
            password: (string) $data['password'],
        );
    }
}
