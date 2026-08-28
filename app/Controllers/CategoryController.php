<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\CategoryService;
use App\Core\View;

final class CategoryController extends BaseController
{
    public function __construct(
        View $view,
        private CategoryService $categoryService
    ) {
        parent::__construct($view);
    }

    /**
     * GET /
     */
    public function index(): void
    {
        $categories = $this->categoryService
            ->getCategories();

        $this->render(
            'categories/index.html.twig',
            [
                'title' => 'Categories',
                'categories' => $categories,
            ]
        );
    }
}
