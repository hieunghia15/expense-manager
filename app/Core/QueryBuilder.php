<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

final class QueryBuilder
{
    private PDO $pdo;

    private string $table = '';

    private array $columns = ['*'];

    private array $joins = [];

    private array $wheres = [];

    private array $groups = [];

    private array $orders = [];

    private array $bindings = [];

    private ?int $limitValue = null;

    private ?int $offsetValue = null;

    private int $parameterIndex = 0;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Set main table.
     */
    public function table(string $table): self
    {
        $this->table = $this->validateIdentifier($table);

        return $this;
    }

    /**
     * Define selected columns.
     *
     * Example:
     * ->select(['id', 'name'])
     */
    public function select(array $columns = ['*']): self
    {
        $validatedColumns = [];

        foreach ($columns as $column) {
            $validatedColumns[] = $this->validateColumnExpression(
                $column
            );
        }

        $this->columns = $validatedColumns;

        return $this;
    }

    /**
     * Add WHERE condition.
     */
    public function where(
        string $column,
        string $operator,
        mixed $value
    ): self {
        $column = $this->validateColumnExpression($column);

        $operator = strtoupper(trim($operator));

        $allowedOperators = [
            '=',
            '!=',
            '<>',
            '>',
            '>=',
            '<',
            '<=',
            'LIKE',
            'NOT LIKE',
        ];

        if (!in_array($operator, $allowedOperators, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid operator: %s',
                    $operator
                )
            );
        }

        $parameter = $this->createPlaceholder('where');
        $placeholder = ':' . $parameter;

        $this->wheres[] = sprintf(
            '%s %s %s',
            $column,
            $operator,
            $placeholder
        );

        $this->bindings[$placeholder] = $value;

        return $this;
    }

    /**
     * Add WHERE IN condition.
     *
     * Example:
     * ->whereIn('id', [1, 2, 3])
     */
    public function whereIn(
        string $column,
        array $values
    ): self {
        $column = $this->validateColumnExpression($column);

        if ($values === []) {
            /*
             * Empty IN (...) is invalid SQL.
             * Return a condition that can never be true.
             */
            $this->wheres[] = '1 = 0';

            return $this;
        }

        $placeholders = [];

        foreach ($values as $value) {
            $parameter = $this->createPlaceholder('in');
            $placeholder = ':' . $parameter;

            $placeholders[] = $placeholder;
            $this->bindings[$placeholder] = $value;
        }

        $this->wheres[] = sprintf(
            '%s IN (%s)',
            $column,
            implode(', ', $placeholders)
        );

        return $this;
    }

    /**
     * Add INNER JOIN.
     *
     * Example:
     * ->join(
     *     'categories',
     *     'transactions.category_id',
     *     '=',
     *     'categories.id'
     * )
     */
    public function join(
        string $table,
        string $firstColumn,
        string $operator,
        string $secondColumn
    ): self {
        $table = $this->validateIdentifier($table);

        $firstColumn = $this->validateColumnExpression(
            $firstColumn
        );

        $secondColumn = $this->validateColumnExpression(
            $secondColumn
        );

        $allowedOperators = [
            '=',
            '!=',
            '<>',
            '>',
            '>=',
            '<',
            '<=',
        ];

        if (!in_array($operator, $allowedOperators, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid JOIN operator: %s',
                    $operator
                )
            );
        }

        $this->joins[] = sprintf(
            'INNER JOIN %s ON %s %s %s',
            $table,
            $firstColumn,
            $operator,
            $secondColumn
        );

        return $this;
    }

    /**
     * Add LEFT JOIN.
     */
    public function leftJoin(
        string $table,
        string $firstColumn,
        string $operator,
        string $secondColumn
    ): self {
        $table = $this->validateIdentifier($table);

        $firstColumn = $this->validateColumnExpression(
            $firstColumn
        );

        $secondColumn = $this->validateColumnExpression(
            $secondColumn
        );

        $allowedOperators = [
            '=',
            '!=',
            '<>',
            '>',
            '>=',
            '<',
            '<=',
        ];

        if (!in_array($operator, $allowedOperators, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid JOIN operator: %s',
                    $operator
                )
            );
        }

        $this->joins[] = sprintf(
            'LEFT JOIN %s ON %s %s %s',
            $table,
            $firstColumn,
            $operator,
            $secondColumn
        );

        return $this;
    }

    /**
     * Add GROUP BY.
     */
    public function groupBy(
        string|array $columns
    ): self {
        $columns = is_array($columns)
            ? $columns
            : [$columns];

        foreach ($columns as $column) {
            $this->groups[] = $this->validateColumnExpression(
                $column
            );
        }

        return $this;
    }

    /**
     * Add ORDER BY.
     *
     * Example:
     * ->orderBy('created_at', 'DESC')
     */
    public function orderBy(
        string $column,
        string $direction = 'ASC'
    ): self {
        $column = $this->validateColumnExpression($column);

        $direction = strtoupper(trim($direction));

        if (!in_array($direction, ['ASC', 'DESC'], true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid order direction: %s',
                    $direction
                )
            );
        }

        $this->orders[] = sprintf(
            '%s %s',
            $column,
            $direction
        );

        return $this;
    }

    /**
     * Set LIMIT.
     */
    public function limit(int $limit): self
    {
        if ($limit < 1) {
            throw new \InvalidArgumentException(
                'Limit must be greater than zero.'
            );
        }

        $this->limitValue = $limit;

        return $this;
    }

    /**
     * Set OFFSET.
     */
    public function offset(int $offset): self
    {
        if ($offset < 0) {
            throw new \InvalidArgumentException(
                'Offset cannot be negative.'
            );
        }

        $this->offsetValue = $offset;

        return $this;
    }

    /**
     * Execute SELECT query.
     */
    public function get(): array
    {
        $sql = $this->buildSelectSql();

        $statement = $this->pdo->prepare($sql);

        $statement->execute(
            $this->bindings
        );

        return $statement->fetchAll();
    }

    /**
     * Execute SELECT query and return first row.
     */
    public function first(): ?array
    {
        $results = $this->limit(1)->get();

        return $results[0] ?? null;
    }

    /**
     * Insert record.
     *
     * Returns inserted ID.
     */
    public function insert(array $data): int
    {
        if ($data === []) {
            throw new \InvalidArgumentException(
                'Insert data cannot be empty.'
            );
        }

        $columns = [];
        $placeholders = [];
        $bindings = [];

        foreach ($data as $column => $value) {
            $column = $this->validateIdentifier(
                (string) $column
            );

            $parameter = $this->createPlaceholder('insert');
            $placeholder = ':' . $parameter;

            $columns[] = $column;
            $placeholders[] = $placeholder;
            $bindings[$placeholder] = $value;
        }

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $statement = $this->pdo->prepare($sql);

        $statement->execute($bindings);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Update records.
     */
    public function update(array $data): int
    {
        if ($data === []) {
            throw new \InvalidArgumentException(
                'Update data cannot be empty.'
            );
        }

        if ($this->wheres === []) {
            throw new \LogicException(
                'Update requires at least one WHERE condition.'
            );
        }

        $setStatements = [];
        $bindings = $this->bindings;

        foreach ($data as $column => $value) {
            $column = $this->validateIdentifier(
                (string) $column
            );

            $parameter = $this->createPlaceholder('update');
            $placeholder = ':' . $parameter;

            $setStatements[] = sprintf(
                '%s = %s',
                $column,
                $placeholder
            );

            $bindings[$placeholder] = $value;
        }

        $sql = sprintf(
            'UPDATE %s SET %s%s',
            $this->table,
            implode(', ', $setStatements),
            $this->buildWhere()
        );

        $statement = $this->pdo->prepare($sql);

        $statement->execute($bindings);

        return $statement->rowCount();
    }

    /**
     * Delete records.
     */
    public function delete(): int
    {
        if ($this->wheres === []) {
            throw new \LogicException(
                'Delete requires at least one WHERE condition.'
            );
        }

        $sql = sprintf(
            'DELETE FROM %s%s',
            $this->table,
            $this->buildWhere()
        );

        $statement = $this->pdo->prepare($sql);

        $statement->execute($this->bindings);

        return $statement->rowCount();
    }

    /**
     * Paginate records.
     *
     * Returns:
     *
     * [
     *     'data' => [...],
     *     'pagination' => [
     *         'current_page' => 1,
     *         'per_page' => 10,
     *         'total' => 100,
     *         'last_page' => 10,
     *     ],
     * ]
     */
    public function paginate(
        int $page = 1,
        int $perPage = 10
    ): array {
        if ($page < 1) {
            throw new \InvalidArgumentException(
                'Page must be greater than zero.'
            );
        }

        if ($perPage < 1) {
            throw new \InvalidArgumentException(
                'Per page must be greater than zero.'
            );
        }

        $total = $this->getCount();

        $offset = ($page - 1) * $perPage;

        $this->limit($perPage);
        $this->offset($offset);

        $data = $this->get();

        $lastPage = $total > 0
            ? (int) ceil($total / $perPage)
            : 1;

        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
                'from' => $total > 0
                    ? $offset + 1
                    : null,
                'to' => min(
                    $offset + $perPage,
                    $total
                ),
                'has_previous_page' => $page > 1,
                'has_next_page' => $page < $lastPage,
            ],
        ];
    }

    /**
     * Count rows for pagination.
     */
    public function getCount(): int
    {
        $sql = sprintf(
            'SELECT COUNT(*) AS aggregate FROM %s',
            $this->table
        );

        $sql .= $this->buildJoins();
        $sql .= $this->buildWhere();

        /*
         * GROUP BY changes COUNT(*) semantics.
         *
         * For this basic QueryBuilder, pagination should be
         * used on the main table without GROUP BY.
         */
        if ($this->groups !== []) {
            throw new \LogicException(
                'Pagination count with GROUP BY '
                    . 'is not supported by this QueryBuilder.'
            );
        }

        $statement = $this->pdo->prepare($sql);

        $statement->execute($this->bindings);

        $result = $statement->fetch();

        return (int) ($result['aggregate'] ?? 0);
    }

    /**
     * Build SELECT SQL.
     */
    private function buildSelectSql(): string
    {
        $sql = sprintf(
            'SELECT %s FROM %s',
            implode(', ', $this->columns),
            $this->table
        );

        $sql .= $this->buildJoins();
        $sql .= $this->buildWhere();

        if ($this->groups !== []) {
            $sql .= ' GROUP BY ' . implode(
                ', ',
                $this->groups
            );
        }

        if ($this->orders !== []) {
            $sql .= ' ORDER BY ' . implode(
                ', ',
                $this->orders
            );
        }

        if ($this->limitValue !== null) {
            $sql .= ' LIMIT ' . $this->limitValue;
        }

        if ($this->offsetValue !== null) {
            $sql .= ' OFFSET ' . $this->offsetValue;
        }

        return $sql;
    }

    /**
     * Build JOIN clauses.
     */
    private function buildJoins(): string
    {
        if ($this->joins === []) {
            return '';
        }

        return ' ' . implode(' ', $this->joins);
    }

    /**
     * Build WHERE clause.
     */
    private function buildWhere(): string
    {
        if ($this->wheres === []) {
            return '';
        }

        return ' WHERE ' . implode(
            ' AND ',
            $this->wheres
        );
    }

    /**
     * Generate unique parameter name.
     */
    private function createPlaceholder(
        string $prefix
    ): string {
        return $prefix . '_' . (++$this->parameterIndex);
    }

    /**
     * Validate simple SQL identifier.
     *
     * Valid:
     * id
     * name
     * categories.id
     * transactions.created_at
     */
    private function validateIdentifier(
        string $identifier
    ): string {
        if (!preg_match(
            '/^[a-zA-Z_][a-zA-Z0-9_]*$/',
            $identifier
        )) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid SQL identifier: %s',
                    $identifier
                )
            );
        }

        return $identifier;
    }

    /**
     * Validate SQL column expression.
     *
     * Supports:
     * id
     * categories.id
     * COUNT(id)
     * COUNT(*) AS total
     *
     * Note:
     * Keep this whitelist intentionally limited.
     */
    private function validateColumnExpression(
        string $expression
    ): string {
        $expression = trim($expression);

        if ($expression === '*') {
            return '*';
        }

        /*
         * Simple column:
         * id
         * name
         */
        if (preg_match(
            '/^[a-zA-Z_][a-zA-Z0-9_]*$/',
            $expression
        )) {
            return $expression;
        }

        /*
         * Table-qualified column:
         * categories.id
         */
        if (preg_match(
            '/^[a-zA-Z_][a-zA-Z0-9_]*\.[a-zA-Z_][a-zA-Z0-9_]*$/',
            $expression
        )) {
            return $expression;
        }

        /*
         * Aggregate expression:
         * COUNT(*)
         * COUNT(id)
         * SUM(amount)
         *
         * This is intentionally limited.
         */
        if (preg_match(
            '/^(COUNT|SUM|AVG|MIN|MAX)\(([a-zA-Z_][a-zA-Z0-9_]*|\*)\)(?:\s+AS\s+[a-zA-Z_][a-zA-Z0-9_]*)?$/i',
            $expression
        )) {
            return $expression;
        }

        if (preg_match(
            '/^[a-zA-Z_][a-zA-Z0-9_]*\.[a-zA-Z_][a-zA-Z0-9_]*\s+AS\s+[a-zA-Z_][a-zA-Z0-9_]*$/i',
            $expression
        )) {
            return $expression;
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Invalid column expression: %s',
                $expression
            )
        );
    }
}
