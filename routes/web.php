<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\CategoryController;
use App\Core\AuthGuard;
use App\Core\Router;

return static function (
    Router $router,
    AuthController $authController,
    CategoryController $categoryController
): void {
    /*
    |--------------------------------------------------------------------------
    | Public Routes
    |--------------------------------------------------------------------------
    */

    $router->get(
        '/',
        [$authController, 'showLoginForm']
    );

    $router->post(
        '/login',
        [$authController, 'login']
    );

    /*
    |--------------------------------------------------------------------------
    | Authenticated Routes
    |--------------------------------------------------------------------------
    */

    $router->post(
        '/logout',
        AuthGuard::protect([$authController, 'logout'])
    );

    /*
    |--------------------------------------------------------------------------
    | Category Routes
    |--------------------------------------------------------------------------
    */

    $router->get(
        '/categories',
        AuthGuard::protect([$categoryController, 'index'])
    );

    $router->get(
        '/categories/create',
        AuthGuard::protect([$categoryController, 'create'])
    );

    $router->post(
        '/categories/create',
        AuthGuard::protect([$categoryController, 'store'])
    );

    $router->get(
        '/categories/{id}/edit',
        AuthGuard::protect([$categoryController, 'edit'])
    );

    $router->post(
        '/categories/{id}',
        AuthGuard::protect([$categoryController, 'update'])
    );

    $router->post(
        '/categories/{id}/delete',
        AuthGuard::protect([$categoryController, 'delete'])
    );
};
