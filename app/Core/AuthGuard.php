<?php

declare(strict_types=1);

namespace App\Core;

final class AuthGuard
{
    public static function check(): bool
    {
        Session::start();

        return Session::has('user_id') && Session::get('user_id') !== null;
    }

    public static function enforce(): void
    {
        if (! self::check()) {
            header('Location: /');
            exit;
        }
    }

    public static function protect(callable $handler): callable
    {
        return static function (mixed ...$args) use ($handler): void {
            self::enforce();
            $handler(...$args);
        };
    }
}
