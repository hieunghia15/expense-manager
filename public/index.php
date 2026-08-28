<?php

declare(strict_types=1);

use App\Controllers\CategoryController;
use App\Controllers\TransactionController;
use App\Core\Database;
use App\Core\Env;
use App\Core\ExceptionHandler;
use App\Core\Logger;
use App\Core\QueryBuilderFactory;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;
use App\Models\CategoryModel;
use App\Models\TransactionModel;
use App\Services\CategoryService;
use App\Services\TransactionService;

require_once __DIR__ . '/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Environment
|--------------------------------------------------------------------------
*/

Env::load(
    dirname(__DIR__) . '/.env'
);

/*
|--------------------------------------------------------------------------
| Session
|--------------------------------------------------------------------------
*/

Session::start();

/*
|--------------------------------------------------------------------------
| Logger
|--------------------------------------------------------------------------
*/

$logger = new Logger(
    dirname(__DIR__) . '/storage/logs/app.log'
);

/*
|--------------------------------------------------------------------------
| Exception Handler
|--------------------------------------------------------------------------
*/

$exceptionHandler = new ExceptionHandler(
    $logger,
    Env::get('APP_DEBUG', 'false') === 'true'
);

$exceptionHandler->register();

/*
|--------------------------------------------------------------------------
| Database
|--------------------------------------------------------------------------
*/

$database = new Database();

$pdo = $database->getConnection();

/*
|--------------------------------------------------------------------------
| Query Builder Factory
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
| Models
|--------------------------------------------------------------------------
*/

$categoryModel = new CategoryModel(
    $queryBuilderFactory
);

/*
|--------------------------------------------------------------------------
| Services
|--------------------------------------------------------------------------
*/

$categoryService = new CategoryService(
    $categoryModel
);

/*
|--------------------------------------------------------------------------
| Controllers
|--------------------------------------------------------------------------
*/

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
    $categoryController,
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