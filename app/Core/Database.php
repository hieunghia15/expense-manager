<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private PDO $connection;

    public function __construct()
    {
        $host = (string) Env::get('DB_HOST', '127.0.0.1');
        $port = (int) Env::get('DB_PORT', 3306);
        $database = (string) Env::get('DB_DATABASE');
        $username = (string) Env::get('DB_USERNAME');
        $password = (string) Env::get('DB_PASSWORD', '');
        $charset = (string) Env::get('DB_CHARSET', 'utf8mb4');

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $host,
            $port,
            $database,
            $charset
        );

        try {
            $this->connection = new PDO(
                $dsn,
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $exception) {
            throw new \RuntimeException(
                'Database connection failed.',
                previous: $exception
            );
        }
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    public function beginTransaction(): void
    {
        if ($this->connection->inTransaction()) {
            throw new \LogicException(
                'A transaction is already active.'
            );
        }

        $this->connection->beginTransaction();
    }

    public function commit(): void
    {
        if (!$this->connection->inTransaction()) {
            throw new \LogicException(
                'No active transaction to commit.'
            );
        }

        $this->connection->commit();
    }

    public function rollBack(): void
    {
        if (!$this->connection->inTransaction()) {
            throw new \LogicException(
                'No active transaction to roll back.'
            );
        }

        $this->connection->rollBack();
    }

    public function inTransaction(): bool
    {
        return $this->connection->inTransaction();
    }
}
