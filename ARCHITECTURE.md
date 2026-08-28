# 2. `ARCHITECTURE.md`

# Architecture

## 1. Overview

Mini Expense Management is a framework-free PHP 8.5 web application.

Technology stack:
```text
| Component             | Technology                 |
| --------------------- | -------------------------- |
| Language              | PHP 8.5                    |
| Database              | MySQL                      |
| Database Driver       | PDO                        |
| Query Layer           | Custom QueryBuilder        |
| Template Engine       | Twig 3.x                   |
| Dependency Management | Composer                   |
| Architecture          | MVC + Service Layer        |
| Routing               | Custom Router              |
| Session               | Native PHP Session         |
| CSRF                  | Custom CSRF implementation |
| Logging               | Custom Logger              |
```
No PHP framework is used.

---

# 2. High-Level Architecture
```text
                         Browser
                            │
                            ▼
                    public/index.php
                            │
                            ▼
                         Router
                            │
                            ▼
                       Controller
                            │
                            ▼
                         Service
                            │
                            ▼
                          Model
                            │
                            ▼
                   QueryBuilderFactory
                            │
                            ▼
                       QueryBuilder
                            │
                            ▼
                           PDO
                            │
                            ▼
                          MySQL
```
View flow:
```text
Controller
│
▼
View
│
▼
Twig
│
▼
HTML
```
Infrastructure:

Session
CSRF
Logger
ExceptionHandler
Database

# 3. Request Lifecycle

Example:

GET /transactions/10/edit

Flow:
```text
Browser
↓
Web Server
↓
public/index.php
↓
Router
↓
TransactionController::edit('10')
↓
TransactionService::getTransaction(10)
↓
TransactionModel::find(10)
↓
QueryBuilder
↓
PDO
↓
MySQL
↓
TransactionModel
↓
Service
↓
Controller
↓
Twig
↓
HTML
```
# 4. Directory Architecture
```text
app/
│
├── Controllers/
│ ├── BaseController.php
│ ├── CategoryController.php
│ └── TransactionController.php
│
├── Services/
│ ├── CategoryService.php
│ └── TransactionService.php
│
├── Models/
│ ├── CategoryModel.php
│ └── TransactionModel.php
│
└── Core/
├── Database.php
├── Env.php
├── ExceptionHandler.php
├── Flash.php
├── Logger.php
├── QueryBuilder.php
├── QueryBuilderFactory.php
├── Router.php
├── Session.php
├── Csrf.php
├── Validator.php
└── View.php
```
# 5. Core Layer

Database

File:

app/Core/Database.php

Responsibilities:

- Create PDO connection.
- Expose PDO connection.
- Begin transactions.
- Commit transactions.
- Roll back transactions.
- Check transaction state.

Database does NOT contain business logic.

QueryBuilder

File:

app/Core/QueryBuilder.php

Responsibilities:

- Build SELECT queries.
- Build INSERT queries.
- Build UPDATE queries.
- Build DELETE queries.
- Add WHERE clauses.
- Add WHERE IN clauses.
- Add JOIN.
- Add LEFT JOIN.
- Add GROUP BY.
- Add ORDER BY.
- Add LIMIT.
- Add OFFSET.
- Handle simple pagination.
- Bind query values.

The QueryBuilder is not an ORM.

QueryBuilderFactory

File:

app/Core/QueryBuilderFactory.php

Purpose:

Create a fresh QueryBuilder instance using the same PDO connection.
```text
PDO
│
▼
QueryBuilderFactory
│
├── QueryBuilder instance
├── QueryBuilder instance
└── QueryBuilder instance
```
A Model should request a fresh QueryBuilder for each query.

# 6. Controller Layer

Controllers are HTTP adapters.

Responsibilities:

- Request input.
- Route parameters.
- Validation.
- Calling Services.
- Returning views.
- Redirecting.

Controllers should remain thin.

Example:
```text
TransactionController
│
▼
TransactionService
```
Controller should not know SQL details.

# 7. Service Layer

The Service Layer contains business logic.

Example:

TransactionService

Responsibilities:

- Verify category exists.
- Validate amount rules.
- Coordinate transaction operations.
- Start/commit/rollback DB transactions.
- Log business events.

Example:

createTransaction()

    1. Validate category
    2. Validate amount
    3. beginTransaction()
    4. create transaction
    5. commit()
    6. log success

    on exception:

    7. rollback()
    8. rethrow exception

# 8. Model Layer

Models represent database operations.

Example:

TransactionModel

Methods may include:

- getAll()
- paginate()
- find()
- create()
- update()
- delete()

Models communicate exclusively through QueryBuilder.

# 9. No Repository Layer

This project intentionally does NOT use:

Repository

Do not create:

- CategoryRepository
- TransactionRepository

The project uses:
```text
Service
↓
Model
↓
QueryBuilder
```
# 10. Routing

Routes are defined in:

routes/web.php

Example:
```php
$router->get(
    '/transactions',
    [$transactionController, 'index']
);

$router->get(
    '/transactions/{id}',
    [$transactionController, 'show']
);

$router->get(
    '/transactions/{id}/edit',
    [$transactionController, 'edit']
);
```
Dynamic route parameters are passed to controllers.

# 11. Front Controller

Only:

public/index.php

is the web application entry point.

Responsibilities:

- Load Composer.
- Load environment.
- Start session.
- Create Logger.
- Register ExceptionHandler.
- Create Database.
- Create QueryBuilderFactory.
- Create View.
- Create Models.
- Create Services.
- Create Controllers.
- Register routes.
- Dispatch request.

# 12. Dependency Injection

Dependency injection is manual.

Example:
```php
$database = new Database();

$pdo = $database->getConnection();

$queryBuilderFactory = new QueryBuilderFactory(
$pdo
);

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
```
The project does not use a framework DI container.

# 13. Transaction Flow

Transactions belong to Service methods.

Example:
```text
TransactionService
│
▼
beginTransaction()
│
▼
TransactionModel
│
▼
QueryBuilder
│
▼
PDO
│
▼
commit()

On error:

Exception
↓
rollBack()
↓
throw
↓
ExceptionHandler
↓
Logger
```
# 14. Validation Architecture

There are two levels.

Request Validation

Performed by Controller.

Examples:

- required fields
- input format
- integer
- date format
- string length
- Business Validation

Performed by Service.

Examples:

- category exists
- amount must be positive
- entity must exist before update
- entity cannot be deleted under certain business conditions

# 15. CSRF Flow

For POST requests:
```text
GET form
↓
Csrf::token()
↓
Twig hidden input
↓
POST form
↓
Controller
↓
Csrf::validateOrFail()
↓
Service
```
CSRF token is stored in Session.

# 16. Flash Message Flow
```text
Service success
↓
Controller
↓
Flash::success()
↓
Redirect
↓
GET request
↓
Flash::pull()
↓
Twig
```
Flash messages are stored temporarily in Session.

# 17. Exception Flow
```text
Unexpected exception
↓
ExceptionHandler
↓
Logger
↓
HTTP 500
```
Development:

APP_DEBUG=true

may expose detailed debugging information.

Production:

APP_DEBUG=false

must show a generic error page.

# 18. Logging Architecture

Logs:

storage/logs/app.log

Logger responsibilities:

INFO
WARNING
ERROR

Important business events should be logged.

Sensitive information must never be logged.

# 19. Database Schema

categories
id
name
type
created_at
updated_at

type values:

income
expense
transactions
id
category_id
amount
transaction_date
note
created_at
updated_at

Relationship:
```text
categories
│
│ 1
│
│ N
▼
transactions
```
Foreign key:
```text
transactions.category_id
↓
categories.id
```
# 20. Domain Rule

Transaction does not duplicate category type.

Example:

Transaction
category_id = 5

Category #5
type = expense

Therefore transaction type is determined through category.

# 21. Pagination

Basic pagination is implemented in QueryBuilder.

Result format:
```php
[
    'data' => [],
    'pagination' => [
    'current_page' => 1,
    'per_page' => 10,
    'total' => 100,
    'last_page' => 10,
    'from' => 1,
    'to' => 10,
    'has_previous_page' => false,
    'has_next_page' => true,
    ],
]
```
Pagination is intended for normal CRUD listings.

Complex aggregate reports should use dedicated queries.

# 22. Security Architecture

Database:

PDO prepared statements

HTML:

Twig autoescape

Forms:

CSRF token

Session:

HttpOnly
SameSite=Lax
Secure when HTTPS is enabled

SQL identifiers:

validated by QueryBuilder

DELETE and UPDATE:

WHERE is required

# 23. Feature Implementation Pattern

For a new entity, follow:
```text
routes/web.php
↓
XController.php
↓
XService.php
↓
XModel.php
↓
QueryBuilder
↓
MySQL
```
View:
views/x/

Example:
```text
Report
↓
ReportController
↓
ReportService
↓
ReportModel
↓
QueryBuilder
```
# 24. Architectural Constraints

The following are explicitly forbidden:

- Laravel
- Symfony
- CodeIgniter
- Doctrine ORM
- Eloquent
- Repository Pattern
- Active Record
- Service Locator
- Raw SQL inside Controllers
- PDO inside Controllers
- PDO inside Views
- Database logic inside Twig
- Business logic inside Twig 25. Design Philosophy

The application intentionally favors:

- explicit dependencies
- small classes
- understandable code
- strict separation of responsibilities
- security
- maintainability
- low dependency count

The application is intentionally NOT a full framework.

Do not turn the project into one.
