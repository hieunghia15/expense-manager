<?php

declare(strict_types=1);

use App\Controllers\CategoryController;
use App\Core\Database;
use App\Core\Env;
use App\Core\QueryBuilderFactory;
use App\Core\Router;
use App\Core\View;
use App\Models\CategoryModel;
use App\Services\CategoryService;

require_once __DIR__ . '/../vendor/autoload.php';

Env::load(
    dirname(__DIR__) . '/.env'
);

/*
|--------------------------------------------------------------------------
| Database
|--------------------------------------------------------------------------
*/

$database = new Database();

$pdo = $database->getConnection();

/*
|--------------------------------------------------------------------------
| Query Builder
|--------------------------------------------------------------------------
*/

$queryBuilderFactory = new QueryBuilderFactory(
    $pdo
);

/*
|--------------------------------------------------------------------------
| View
|--------------------------------------------------------------------------
*/

$view = new View(
    dirname(__DIR__) . '/views'
);

/*
|--------------------------------------------------------------------------
| Category
|--------------------------------------------------------------------------
*/

$categoryModel = new CategoryModel(
    $queryBuilderFactory
);

$categoryService = new CategoryService(
    $categoryModel
);

$categoryController = new CategoryController(
    $view,
    $categoryService
);

/*
|--------------------------------------------------------------------------
| Router
|--------------------------------------------------------------------------
*/

$router = new Router();

$routes = require dirname(__DIR__) . '/routes/web.php';

$routes(
    $router,
    $categoryController
);

/*
|--------------------------------------------------------------------------
| Dispatch
|--------------------------------------------------------------------------
*/

$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI']
);