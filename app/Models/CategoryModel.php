<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\QueryBuilderFactory;

final class CategoryModel
{
    public function __construct(
        private QueryBuilderFactory $queryBuilderFactory
    ) {}

    public function getAll(): array
    {
        return $this->queryBuilderFactory
            ->make()
            ->table('categories')
            ->select([
                'id',
                'name',
                'type',
                'created_at',
                'updated_at',
            ])
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    public function paginate(
        int $page,
        int $perPage
    ): array {
        return $this->queryBuilderFactory
            ->make()
            ->table('categories')
            ->select([
                'id',
                'name',
                'type',
                'created_at',
                'updated_at',
            ])
            ->orderBy('created_at', 'DESC')
            ->paginate(
                $page,
                $perPage
            );
    }

    public function find(int $id): ?array
    {
        return $this->queryBuilderFactory
            ->make()
            ->table('categories')
            ->select([
                'id',
                'name',
                'type',
                'created_at',
                'updated_at',
            ])
            ->where('id', '=', $id)
            ->first();
    }

    public function findByIds(
        array $ids
    ): array {
        return $this->queryBuilderFactory
            ->make()
            ->table('categories')
            ->select([
                'id',
                'name',
                'type',
            ])
            ->whereIn('id', $ids)
            ->get();
    }

    public function create(
        string $name,
        string $type
    ): int {
        return $this->queryBuilderFactory
            ->make()
            ->table('categories')
            ->insert([
                'name' => $name,
                'type' => $type,
            ]);
    }

    public function update(
        int $id,
        string $name,
        string $type
    ): int {
        return $this->queryBuilderFactory
            ->make()
            ->table('categories')
            ->where('id', '=', $id)
            ->update([
                'name' => $name,
                'type' => $type,
            ]);
    }

    public function delete(int $id): int
    {
        return $this->queryBuilderFactory
            ->make()
            ->table('categories')
            ->where('id', '=', $id)
            ->delete();
    }
}
