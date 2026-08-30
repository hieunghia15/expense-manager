<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Flash;
use App\Core\Validator;
use App\Core\View;
use App\Services\CategoryService;

final class CategoryController extends BaseController
{
    public function __construct(
        View $view,
        private CategoryService $categoryService
    ) {
        parent::__construct($view);
    }

    public function index(): void
    {
        $categories = $this->categoryService
            ->getCategories();

        $this->render(
            'categories/index.html.twig',
            [
                'title' => 'Categories',
                'categories' => $categories,
                'csrf_token' => Csrf::token(),
                'flash' => Flash::pull(),
            ]
        );
    }

    public function create(): void
    {
        $this->render(
            'categories/create.html.twig',
            [
                'title' => 'Add Category',
                'csrf_token' => Csrf::token(),
                'flash' => Flash::pull(),
            ]
        );
    }

    public function store(): void
    {
        Csrf::validateOrFail(
            $_POST['_csrf_token'] ?? null
        );

        $name = trim(
            (string) ($_POST['name'] ?? '')
        );

        $type = trim(
            (string) ($_POST['type'] ?? '')
        );

        $validator = new Validator();

        $validator
            ->required(
                'name',
                $name,
                'Category name is required.'
            )
            ->string(
                'name',
                $name,
                100,
                'Category name cannot exceed 100 characters.'
            )
            ->required(
                'type',
                $type,
                'Category type is required.'
            );

        if (
            !in_array(
                $type,
                ['income', 'expense'],
                true
            )
        ) {
            $validator->addError(
                'type',
                'Invalid category type.'
            );
        }

        if ($validator->fails()) {
            $this->render(
                'categories/create.html.twig',
                [
                    'title' => 'Add Category',
                    'category' => [
                        'name' => $name,
                        'type' => $type,
                    ],
                    'errors' => $validator->errors(),
                    'csrf_token' => Csrf::token(),
                ]
            );

            return;
        }

        $this->categoryService->createCategory(
            $name,
            $type
        );

        Flash::success(
            'Category created successfully.'
        );

        $this->redirect('/categories');
    }

    public function edit(
        string $id
    ): void {
        $categoryId = (int) $id;

        $category = $this->categoryService
            ->getCategory($categoryId);

        if ($category === null) {
            http_response_code(404);

            echo 'Category not found.';

            return;
        }

        $this->render(
            'categories/edit.html.twig',
            [
                'title' => 'Edit Category',
                'category' => $category,
                'csrf_token' => Csrf::token(),
                'flash' => Flash::pull(),
            ]
        );
    }

    public function update(
        string $id
    ): void {
        Csrf::validateOrFail(
            $_POST['_csrf_token'] ?? null
        );

        $categoryId = (int) $id;

        $name = trim(
            (string) ($_POST['name'] ?? '')
        );

        $type = trim(
            (string) ($_POST['type'] ?? '')
        );

        $category = $this->categoryService
            ->getCategory($categoryId);
        if ($category === null) {
            $this->render(
                'categories/edit.html.twig',
                [
                    'title' => 'Edit Category',
                    'category' => [
                        'id' => $categoryId,
                        'name' => $name,
                        'type' => $type,
                    ],
                    'errors' => 'Category not found',
                    'csrf_token' => Csrf::token(),
                ]
            );

            return;
        }

        $validator = new Validator();

        $validator
            ->required(
                'name',
                $name,
                'Category name is required.'
            )
            ->string(
                'name',
                $name,
                100,
                'Category name cannot exceed 100 characters.'
            )
            ->required(
                'type',
                $type,
                'Category type is required.'
            );

        if (
            !in_array(
                $type,
                ['income', 'expense'],
                true
            )
        ) {
            $validator->addError(
                'type',
                'Invalid category type.'
            );
        }

        if ($validator->fails()) {
            $this->render(
                'categories/edit.html.twig',
                [
                    'title' => 'Edit Category',
                    'category' => [
                        'id' => $categoryId,
                        'name' => $name,
                        'type' => $type,
                    ],
                    'errors' => $validator->errors(),
                    'csrf_token' => Csrf::token(),
                ]
            );

            return;
        }

        $this->categoryService->updateCategory(
            $categoryId,
            $name,
            $type
        );

        Flash::success(
            'Category updated successfully.'
        );

        $this->redirect('/categories');
    }

    public function delete(
        string $id
    ): void {
        Csrf::validateOrFail(
            $_POST['_csrf_token'] ?? null
        );
        $categoryId = (int) $id;
        $category = $this->categoryService
            ->getCategory($categoryId);
        if ($category === null) {
            $this->render(
                'categories/edit.html.twig',
                [
                    'title' => 'Edit Category',
                    'errors' => 'Category not found',
                    'csrf_token' => Csrf::token(),
                ]
            );

            return;
        }

        $this->categoryService->deleteCategory($categoryId);

        Flash::success(
            'Category deleted successfully.'
        );

        $this->redirect('/categories');

    }
}
