<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

final class Database
{
    private PDO $connection;

    public function __construct()
    {
        $host = Config::get('database.host');
        $port = Config::get('database.port');
        $database = Config::get('database.database');
        $username = Config::get('database.username');
        $password = Config::get('database.password');
        $charset = Config::get('database.charset', 'utf8mb4');

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";

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
        if (! $this->connection->inTransaction()) {
            throw new \LogicException(
                'No active transaction to commit.'
            );
        }

        $this->connection->commit();
    }

    public function rollBack(): void
    {
        if (! $this->connection->inTransaction()) {
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
