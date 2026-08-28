<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CategoryModel;
use InvalidArgumentException;

final class CategoryService
{
    public function __construct(
        private CategoryModel $categoryModel
    ) {}

    /**
     * Get category list.
     */
    public function getCategories(): array
    {
        return $this->categoryModel->getAll();
    }

    /**
     * Get one category.
     */
    public function getCategory(int $id): ?array
    {
        if ($id <= 0) {
            throw new InvalidArgumentException(
                'Category ID must be greater than zero.'
            );
        }

        return $this->categoryModel->find($id);
    }

    /**
     * Create category.
     */
    public function createCategory(
        string $name,
        string $type
    ): int {
        $name = trim($name);

        if ($name === '') {
            throw new InvalidArgumentException(
                'Category name is required.'
            );
        }

        if (!in_array($type, ['income', 'expense'], true)) {
            throw new InvalidArgumentException(
                'Invalid category type.'
            );
        }

        return $this->categoryModel->create(
            $name,
            $type
        );
    }
}
