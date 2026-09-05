<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\UserModel;

final readonly class UserService
{
    public function getUserByEmail(string $email): ?array
    {
        return UserModel::findByEmail($email);
    }
}
