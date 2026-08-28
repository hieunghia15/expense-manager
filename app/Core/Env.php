<?php

declare(strict_types=1);

namespace App\Core;

final class Env
{
    /**
     * Load variables from a .env file into $_ENV.
     */
    public static function load(string $path): void
    {
        if (!file_exists($path)) {
            throw new \RuntimeException(
                sprintf('.env file not found: %s', $path)
            );
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            throw new \RuntimeException('Unable to read .env file.');
        }

        foreach ($lines as $line) {
            $line = trim($line);

            // Ignore comments.
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);

            $key = trim($key);
            $value = trim($value);

            // Remove optional quotes.
            $value = trim($value, "\"'");

            $_ENV[$key] = $value;
        }
    }

    public static function get(
        string $key,
        mixed $default = null
    ): mixed {
        return $_ENV[$key] ?? $default;
    }
}
