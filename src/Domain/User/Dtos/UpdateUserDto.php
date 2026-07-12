<?php

namespace Domain\User\Dtos;

/**
 * DTO for updating an existing user.
 *
 * Password is optional. When provided, it triggers a password reset and
 * forces the user to change password on next login.
 */
class UpdateUserDto
{
    public function __construct(
        public readonly string $name,
        public readonly string $username,
        public readonly string $role,
        public readonly ?string $password = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $password = $data['password'] ?? null;

        return new self(
            name: (string) $data['name'],
            username: (string) $data['username'],
            role: (string) $data['role'],
            password: is_string($password) && $password !== '' ? $password : null,
        );
    }

    /**
     * Whether the admin is also resetting the user's password.
     */
    public function hasPasswordReset(): bool
    {
        return $this->password !== null;
    }
}
