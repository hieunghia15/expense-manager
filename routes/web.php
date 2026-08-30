<?php

declare(strict_types=1);

use App\Controllers\CategoryController;
use App\Core\Router;

return static function (
    Router $router,
    CategoryController $categoryController
): void {
    /*
    |--------------------------------------------------------------------------
    | Category
    |--------------------------------------------------------------------------
    */

    $router->get(
        '/',
        [$categoryController, 'index']
    );

    $router->get(
        '/categories',
        [$categoryController, 'index']
    );

    $router->get(
        '/categories/create',
        [$categoryController, 'create']
    );

    $router->post(
        '/categories/create',
        [$categoryController, 'store']
    );

    $router->get(
        '/categories/{id}/edit',
        [$categoryController, 'edit']
    );

    $router->post(
        '/categories/{id}',
        [$categoryController, 'update']
    );

    $router->post(
        '/categories/{id}/delete',
        [$categoryController, 'delete']
    );
};
