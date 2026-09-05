<?php

declare(strict_types=1);

namespace App\Core;

final class Flash
{
    private const SESSION_KEY = '_flash';

    public static function success(string $message): void
    {
        self::set('success', $message);
    }

    public static function error(string $message): void
    {
        self::set('error', $message);
    }

    public static function set(
        string $type,
        string $message
    ): void {
        Session::put(
            self::SESSION_KEY,
            [
                'type' => $type,
                'message' => $message,
                'error' => $message,
            ]
        );
    }

    public static function pull(): ?array
    {
        $flash = Session::get(
            self::SESSION_KEY
        );

        Session::forget(
            self::SESSION_KEY
        );

        return $flash;
    }
}
