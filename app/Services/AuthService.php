<?php

declare(strict_types=1);

namespace App\Services;

final readonly class AuthService
{
    public function __construct(
        private UserService $userService
    ) {}

    public function verifyCredentials(string $email, string $password): array|false
    {
        $user = $this->userService->getUserByEmail($email);

        if ($user === null) {
            return false;
        }

        if (! isset($user['password']) || ! is_string($user['password'])) {
            return false;
        }

        if (! password_verify($password, $user['password'])) {
            return false;
        }

        return $user;
    }
}
