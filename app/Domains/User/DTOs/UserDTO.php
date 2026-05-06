<?php

namespace App\Domains\User\DTOs;

class UserDTO
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

    public function toCreatePayload(): array
    {
        return [
            'name' => $this->name,
            'username' => $this->username,
            'role' => $this->role,
        ];
    }
}
