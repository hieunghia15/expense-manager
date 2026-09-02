<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CategoryModel;

final class CategoryService
{
    public function getCategories(): array
    {
        return CategoryModel::orderBy('created_at', 'DESC')->get();
    }

    public function getCategory(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        return CategoryModel::find($id);
    }

    public function createCategory(
        string $name,
        string $type
    ): int {
        return CategoryModel::insert([
            'name' => trim($name),
            'type' => $type,
        ]);
    }

    public function updateCategory(
        int $id,
        string $name,
        string $type
    ): int {
        if ($id <= 0) {
            throw new \InvalidArgumentException(
                'Invalid category ID.'
            );
        }

        return CategoryModel::where('id', '=', $id)
            ->update([
                'name' => trim($name),
                'type' => $type,
            ]);
    }

    public function deleteCategory(int $id): int
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException(
                'Invalid category ID.'
            );
        }

        return CategoryModel::where('id', '=', $id)->delete();
    }
}
