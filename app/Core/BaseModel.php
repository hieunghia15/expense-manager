<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Abstract base for all Models.
 *
 * Mirrors the full QueryBuilder API as static entry-points so any model can
 * be queried without boilerplate:
 *
 * --- Entry-points (return QueryBuilder for further chaining) ---
 *
 *   Model::query()                              → QueryBuilder
 *   Model::select(['id', 'name'])               → QueryBuilder
 *   Model::where('type', '=', 'expense')        → QueryBuilder
 *   Model::whereIn('id', [1, 2, 3])             → QueryBuilder
 *   Model::orderBy('created_at', 'DESC')        → QueryBuilder
 *   Model::groupBy('type')                      → QueryBuilder
 *   Model::join('categories', ...)              → QueryBuilder
 *   Model::leftJoin('categories', ...)          → QueryBuilder
 *   Model::limit(10)                            → QueryBuilder
 *   Model::offset(20)                           → QueryBuilder
 *
 * --- Terminal shortcuts (execute immediately) ---
 *
 *   Model::all(): array                         → all rows
 *   Model::find(int $id): ?array               → single row by PK
 *   Model::insert(array $data): int            → inserted ID
 *   Model::count(): int                        → total row count
 *   Model::paginate(int $page, int $perPage): array
 *
 * --- Chained terminals (via QueryBuilder) ---
 *
 *   Model::where(...)->get()
 *   Model::where(...)->first()
 *   Model::where(...)->update([...])
 *   Model::where(...)->delete()
 *   Model::where(...)->paginate($page, $perPage)
 *   Model::where(...)->getCount()
 *
 * Bootstrap (once in public/index.php):
 *
 *   BaseModel::setFactory($queryBuilderFactory);
 *
 * Each concrete model MUST declare:
 *
 *   protected static string $table = 'your_table';
 */
abstract class BaseModel
{
    /**
     * Database table for this model.
     * Must be overridden by each concrete model class.
     */
    protected static string $table = '';

    /**
     * Shared QueryBuilderFactory — registered once at bootstrap.
     */
    private static ?QueryBuilderFactory $factory = null;

    // -------------------------------------------------------------------------
    // Bootstrap
    // -------------------------------------------------------------------------

    /**
     * Register the QueryBuilderFactory for all models.
     * Call this once in public/index.php after the factory is created.
     */
    public static function setFactory(QueryBuilderFactory $factory): void
    {
        self::$factory = $factory;
    }

    // -------------------------------------------------------------------------
    // Internal
    // -------------------------------------------------------------------------

    /**
     * Return a fresh QueryBuilder scoped to this model's table.
     * Uses late static binding so each subclass resolves its own $table.
     *
     * @throws \LogicException if factory is not registered or $table is empty.
     */
    public static function query(): QueryBuilder
    {
        if (self::$factory === null) {
            throw new \LogicException(
                'BaseModel::setFactory() must be called before using static query methods.'
            );
        }

        if (static::$table === '') {
            throw new \LogicException(
                sprintf(
                    'Model %s must define a non-empty $table property.',
                    static::class
                )
            );
        }

        return self::$factory->make()->table(static::$table);
    }

    // -------------------------------------------------------------------------
    // Entry-points — return QueryBuilder for further chaining
    // -------------------------------------------------------------------------

    /**
     * Start a query with specific columns selected.
     *
     * Example:
     *   CategoryModel::select(['id', 'name'])->get();
     *   CategoryModel::select(['id', 'name'])->where('type', '=', 'expense')->get();
     */
    public static function select(array $columns = ['*']): QueryBuilder
    {
        return static::query()->select($columns);
    }

    /**
     * Start a query with a WHERE condition applied.
     *
     * Example:
     *   CategoryModel::where('type', '=', 'income')->get();
     *   CategoryModel::where('type', '=', 'expense')->orderBy('name')->get();
     */
    public static function where(
        string $column,
        string $operator,
        mixed $value
    ): QueryBuilder {
        return static::query()->where($column, $operator, $value);
    }

    /**
     * Start a query with a WHERE IN condition applied.
     *
     * Example:
     *   CategoryModel::whereIn('id', [1, 2, 3])->get();
     */
    public static function whereIn(string $column, array $values): QueryBuilder
    {
        return static::query()->whereIn($column, $values);
    }

    /**
     * Start a query with an INNER JOIN applied.
     *
     * Example:
     *   TransactionModel::join('categories', 'transactions.category_id', '=', 'categories.id')->get();
     */
    public static function join(
        string $table,
        string $firstColumn,
        string $operator,
        string $secondColumn
    ): QueryBuilder {
        return static::query()->join($table, $firstColumn, $operator, $secondColumn);
    }

    /**
     * Start a query with a LEFT JOIN applied.
     *
     * Example:
     *   TransactionModel::leftJoin('categories', 'transactions.category_id', '=', 'categories.id')->get();
     */
    public static function leftJoin(
        string $table,
        string $firstColumn,
        string $operator,
        string $secondColumn
    ): QueryBuilder {
        return static::query()->leftJoin($table, $firstColumn, $operator, $secondColumn);
    }

    /**
     * Start a query with GROUP BY applied.
     *
     * Example:
     *   TransactionModel::groupBy('category_id')->select(['category_id', 'SUM(amount) AS total'])->get();
     */
    public static function groupBy(string|array $columns): QueryBuilder
    {
        return static::query()->groupBy($columns);
    }

    /**
     * Start a query with ORDER BY applied.
     *
     * Example:
     *   CategoryModel::orderBy('created_at', 'DESC')->get();
     */
    public static function orderBy(
        string $column,
        string $direction = 'ASC'
    ): QueryBuilder {
        return static::query()->orderBy($column, $direction);
    }

    /**
     * Start a query with LIMIT applied.
     *
     * Example:
     *   CategoryModel::limit(5)->get();
     */
    public static function limit(int $limit): QueryBuilder
    {
        return static::query()->limit($limit);
    }

    /**
     * Start a query with OFFSET applied.
     *
     * Example:
     *   CategoryModel::offset(10)->limit(5)->get();
     */
    public static function offset(int $offset): QueryBuilder
    {
        return static::query()->offset($offset);
    }

    // -------------------------------------------------------------------------
    // Terminal shortcuts — execute immediately and return results
    // -------------------------------------------------------------------------

    /**
     * Retrieve all rows from the model's table.
     *
     * Equivalent to:
     *   CategoryModel::query()->get();
     *
     * Example:
     *   $categories = CategoryModel::all();
     */
    public static function all(): array
    {
        return static::query()->get();
    }

    /**
     * Find a single row by its primary key (id).
     *
     * Returns null if no row is found.
     *
     * Equivalent to:
     *   CategoryModel::query()->where('id', '=', $id)->first();
     *
     * Example:
     *   $category = CategoryModel::find(1);
     */
    public static function find(int $id): ?array
    {
        return static::query()->where('id', '=', $id)->first();
    }

    /**
     * Insert a new row and return its inserted ID.
     *
     * Equivalent to:
     *   CategoryModel::query()->insert([...]);
     *
     * Example:
     *   $id = CategoryModel::insert(['name' => 'Ăn uống', 'type' => 'expense']);
     */
    public static function insert(array $data): int
    {
        return static::query()->insert($data);
    }

    /**
     * Count all rows in the model's table.
     *
     * Equivalent to:
     *   CategoryModel::query()->getCount();
     *
     * Example:
     *   $total = CategoryModel::count();
     */
    public static function count(): int
    {
        return static::query()->getCount();
    }

    /**
     * Paginate all rows in the model's table.
     *
     * Returns an array with 'data' and 'pagination' keys.
     *
     * Equivalent to:
     *   CategoryModel::query()->paginate($page, $perPage);
     *
     * Example:
     *   $result = CategoryModel::paginate(1, 10);
     *   $rows   = $result['data'];
     *   $meta   = $result['pagination'];
     */
    public static function paginate(int $page = 1, int $perPage = 10): array
    {
        return static::query()->paginate($page, $perPage);
    }
}
