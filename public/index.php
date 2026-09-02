<?php

declare(strict_types=1);

use App\Controllers\CategoryController;
use App\Core\BaseModel;
use App\Core\Config;
use App\Core\Database;
use App\Core\ExceptionHandler;
use App\Core\Logger;
use App\Core\QueryBuilderFactory;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;
use App\Services\CategoryService;
use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Environment & Config
|--------------------------------------------------------------------------
*/
$basePath = dirname(__DIR__);

$dotenv = Dotenv::createImmutable($basePath);
$dotenv->load();

$dotenv->required([
    'APP_ENV',
    'APP_URL',
    'DB_HOST',
    'DB_PORT',
    'DB_DATABASE',
    'DB_USERNAME',
]);

Config::load($basePath . '/config');

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
    $basePath . '/storage/logs/app.log'
);

/*
|--------------------------------------------------------------------------
| Exception Handler
|--------------------------------------------------------------------------
*/

$exceptionHandler = new ExceptionHandler(
    $logger,
    (bool) Config::get('app.debug', false)
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
    $basePath . '/views'
);

/*
|--------------------------------------------------------------------------
| Models
|--------------------------------------------------------------------------
*/

BaseModel::setFactory($queryBuilderFactory);

/*
|--------------------------------------------------------------------------
| Services
|--------------------------------------------------------------------------
*/

$categoryService = new CategoryService();

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

$routes = require $basePath . '/routes/web.php';

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
