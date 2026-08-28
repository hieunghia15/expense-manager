<?php

declare(strict_types=1);

namespace App\Core;

final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
    }

    public static function get(
        string $key,
        mixed $default = null
    ): mixed {
        return $_SESSION[$key] ?? $default;
    }

    public static function put(
        string $key,
        mixed $value
    ): void {
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
}
