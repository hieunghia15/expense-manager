<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

final class ExceptionHandler
{
    public function __construct(
        private Logger $logger,
        private bool $debug = false
    ) {}

    public function register(): void
    {
        set_exception_handler(
            [$this, 'handle']
        );

        set_error_handler(
            [$this, 'handleError']
        );

        register_shutdown_function(
            [$this, 'handleShutdown']
        );
    }

    public function handle(Throwable $exception): void
    {
        $this->logger->error(
            'Unhandled exception.',
            [
                'exception' => get_class(
                    $exception
                ),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]
        );

        http_response_code(500);

        if ($this->debug) {
            echo '<h1>Application Error</h1>';

            echo '<pre>';
            echo htmlspecialchars(
                $exception->getMessage(),
                ENT_QUOTES,
                'UTF-8'
            );

            echo "\n\n";
            echo htmlspecialchars(
                $exception->getTraceAsString(),
                ENT_QUOTES,
                'UTF-8'
            );

            echo '</pre>';

            return;
        }

        echo '<h1>500 - Internal Server Error</h1>';
        echo '<p>Something went wrong.</p>';
    }

    public function handleError(
        int $severity,
        string $message,
        string $file,
        int $line
    ): bool {
        throw new \ErrorException(
            $message,
            0,
            $severity,
            $file,
            $line
        );
    }

    public function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error === null) {
            return;
        }

        $fatalTypes = [
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_COMPILE_ERROR,
        ];

        if (!in_array(
            $error['type'],
            $fatalTypes,
            true
        )) {
            return;
        }

        $this->logger->error(
            'Fatal PHP error.',
            $error
        );

        http_response_code(500);

        if ($this->debug) {
            echo '<pre>';
            echo htmlspecialchars(
                $error['message'],
                ENT_QUOTES,
                'UTF-8'
            );
            echo '</pre>';
        } else {
            echo '500 - Internal Server Error';
        }
    }
}
