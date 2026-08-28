<?php

declare(strict_types=1);

use App\Controllers\CategoryController;
use App\Core\Router;

return static function (
    Router $router,
    CategoryController $categoryController
): void {
    $router->get('/', [$categoryController, 'index']);

    // $router->get(
    //     '/categories',
    //     [$categoryController, 'index']
    // );

    // $router->get(
    //     '/categories/create',
    //     [$categoryController, 'create']
    // );

    // $router->post(
    //     '/categories',
    //     [$categoryController, 'store']
    // );

    // $router->get(
    //     '/categories/edit',
    //     [$categoryController, 'edit']
    // );

    // $router->post(
    //     '/categories/update',
    //     [$categoryController, 'update']
    // );

    // $router->post(
    //     '/categories/delete',
    //     [$categoryController, 'delete']
    // );
};
