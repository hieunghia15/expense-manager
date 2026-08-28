<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CategoryModel;

final class CategoryService
{
    public function __construct(
        private CategoryModel $categoryModel
    ) {}

    public function getCategories(): array
    {
        return $this->categoryModel->getAll();
    }

    public function getCategory(
        int $id
    ): ?array {
        if ($id <= 0) {
            return null;
        }

        return $this->categoryModel->find($id);
    }

    public function createCategory(
        string $name,
        string $type
    ): int {
        return $this->categoryModel->create(
            trim($name),
            $type
        );
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

        return $this->categoryModel->update(
            $id,
            trim($name),
            $type
        );
    }

    public function deleteCategory(
        int $id
    ): int {
        if ($id <= 0) {
            throw new \InvalidArgumentException(
                'Invalid category ID.'
            );
        }

        return $this->categoryModel->delete(
            $id
        );
    }
}
