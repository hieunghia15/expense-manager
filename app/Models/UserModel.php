<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

final class UserModel extends BaseModel
{
    protected static string $table = 'users';

    public static function findByEmail(string $email): ?array
    {
        return self::where('email', '=', $email)->first();
    }
}
