<?php

declare(strict_types=1);

namespace App\Core;

final class Logger
{
    public function __construct(
        private string $logFile
    ) {
        $directory = dirname($logFile);

        if (!is_dir($directory)) {
            mkdir(
                $directory,
                0755,
                true
            );
        }
    }

    public function info(
        string $message,
        array $context = []
    ): void {
        $this->write(
            'INFO',
            $message,
            $context
        );
    }

    public function error(
        string $message,
        array $context = []
    ): void {
        $this->write(
            'ERROR',
            $message,
            $context
        );
    }

    public function warning(
        string $message,
        array $context = []
    ): void {
        $this->write(
            'WARNING',
            $message,
            $context
        );
    }

    private function write(
        string $level,
        string $message,
        array $context
    ): void {
        $timestamp = date(
            'Y-m-d H:i:s'
        );

        $contextJson = $context !== []
            ? json_encode(
                $context,
                JSON_UNESCAPED_UNICODE
            )
            : '';

        $line = sprintf(
            "[%s] %s %s %s%s",
            $timestamp,
            $level,
            $message,
            $contextJson !== ''
                ? $contextJson
                : '',
            PHP_EOL
        );

        file_put_contents(
            $this->logFile,
            $line,
            FILE_APPEND | LOCK_EX
        );
    }
}
