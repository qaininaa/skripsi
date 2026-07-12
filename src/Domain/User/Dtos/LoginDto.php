<?php

namespace Domain\User\Dtos;

/**
 * DTO for login attempt payload.
 */
class LoginDto
{
    public function __construct(
        public readonly string $username,
        public readonly string $password,
        public readonly bool $remember,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            username: (string) $data['username'],
            password: (string) $data['password'],
            remember: (bool) ($data['remember'] ?? false),
        );
    }

    public function credentials(): array
    {
        return [
            'username' => $this->username,
            'password' => $this->password,
        ];
    }
}
