<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

final class QueryBuilderFactory
{
    public function __construct(
        private PDO $pdo
    ) {}

    public function make(): QueryBuilder
    {
        return new QueryBuilder(
            $this->pdo
        );
    }
}
