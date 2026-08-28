# AGENTS.md

## Project Overview

This project is a Mini Expense Management application built with:

- PHP 8.5
- MySQL
- Twig 3.x
- Composer
- Custom MVC architecture
- Custom Router
- Custom Query Builder
- PDO

The project MUST NOT use any PHP framework.

Frameworks such as:

- Laravel
- Symfony
- CodeIgniter
- Yii
- CakePHP
- Slim

are NOT allowed.

---

# 1. Core Architecture

The application MUST follow this request flow:
```text
Request
↓
Router
↓
Controller
↓
Service
↓
Model
↓
QueryBuilder
↓
PDO
↓
MySQL

View flow:

Controller
↓
View
↓
Twig
↓
HTML
```
AI MUST preserve this architecture when modifying the project.

---

# 2. Strict Architectural Rules

## Controller

Controller responsibilities:

- Receive HTTP request.
- Read query parameters, route parameters and form input.
- Perform request/input validation.
- Call Service methods.
- Render Twig views.
- Redirect after successful operations.
- Handle HTTP-level concerns.

Controller MUST NOT:

- Execute SQL.
- Directly use PDO.
- Directly use QueryBuilder.
- Implement database persistence logic.
- Implement complex business logic.
- Access database tables directly.
- Contain reusable business rules.

Bad:
```php
$pdo->prepare(
'SELECT \* FROM transactions'
);
```
Bad:

```php
$queryBuilder
->table('transactions')
->get();
```
Correct:
```php
$transactions = $this->transactionService
->getTransactions(
$page,
$perPage
);
```
# 3. Service Layer

Services contain business logic.

Service responsibilities:

Business rules.
Business validation.
Coordinating multiple Models.
Database transactions.
Business-level error handling.
Calling Models.
Logging important business events.

Example:
```text
TransactionService
↓
CategoryModel
↓
TransactionModel
```
Services MUST NOT:

Render Twig templates.
Echo HTML.
Read directly from $\_GET or $\_POST when avoidable.
Execute raw SQL.
Use PDO directly for queries.

Services MUST NOT be replaced by Repository classes.

# 4. Model Layer

Models are responsible for database access.

Models MUST:

Use QueryBuilderFactory.
Use QueryBuilder for database queries.
Map database operations to meaningful methods.

Example:
```php
public function find(int $id): ?array
{
return $this->queryBuilderFactory
->make()
->table('categories')
->where('id', '=', $id)
->first();
}
```
Models MUST NOT:

Render views.
Handle sessions.
Handle CSRF.
Handle redirects.
Handle HTTP responses.
Contain presentation logic.

# 5. Query Builder

All normal database access MUST go through:
```text
Model
↓
QueryBuilderFactory
↓
QueryBuilder
↓
PDO
```
The custom QueryBuilder is mandatory.

Do NOT introduce:

Eloquent
Doctrine ORM
Generic ORM packages
Repository Pattern
Active Record libraries

The custom QueryBuilder currently supports:

select
insert
update
delete
where
whereIn
join
leftJoin
groupBy
orderBy
limit
offset
pagination

All database values MUST use PDO parameter binding.

Never concatenate user-controlled values into SQL.

# 6. Repository Pattern

Repository Pattern is explicitly forbidden.

Do NOT create:

Repositories/

Do NOT create classes such as:

CategoryRepository
TransactionRepository
UserRepository

The correct flow is:
```text
Service
↓
Model
↓
QueryBuilder
```
# 7. Database

Database access uses PDO with:
```php
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
PDO::ATTR_EMULATE_PREPARES => false
```
Database connection is managed by:

app/Core/Database.php

Transactions are managed through Database:
```php
$database->beginTransaction();

try {
// database operations

    $database->commit();

} catch (\Throwable $exception) {
    if ($database->inTransaction()) {
$database->rollBack();
}

    throw $exception;

}
```
Transactions belong to the Service layer because they represent business operations.

# 8. Router

All application routes MUST be defined in:

routes/web.php

Do NOT define application routes directly inside controllers.

Supported route styles include:

/categories
/categories/{id}
/categories/{id}/edit

/transactions
/transactions/create
/transactions/{id}
/transactions/{id}/edit

The Router is custom and framework-free.

Dynamic parameters MUST be passed from Router to Controller.

# 9. Views

Twig is the template engine.

Views MUST exist under:

views/

Views MUST:

Render HTML.
Display data.
Contain minimal presentation logic.

Views MUST NOT:

- Execute SQL.
- Access PDO.
- Access Models.
- Access Services.
- Modify database state.
- Contain business logic.

Example:

{{ transaction.amount }}

is valid.

SQL inside Twig is forbidden.

# 10. Twig

Twig is installed through Composer.

Do not replace Twig with:

- Blade
- Smarty
- native PHP templates
- another template engine
- unless explicitly requested.

Twig autoescape MUST remain enabled for HTML output.

# 11. Environment Configuration

Configuration is read from:

.env

Template:

.env.example

.env MUST NOT be committed to Git.

Database configuration MUST NOT be hardcoded into application classes.

Bad:
```php
$username = 'root';
$password = '123456';
```
Correct:
```php
$username = Env::get('DB_USERNAME');
$password = Env::get('DB_PASSWORD');
```
# 12. Session

Session handling belongs to:

app/Core/Session.php

Controllers may use Session indirectly when required.

Models MUST NOT access session data.

Session cookies MUST use secure defaults:

HttpOnly
SameSite=Lax
Secure when HTTPS is enabled

# 13. CSRF

All state-changing HTTP requests MUST validate CSRF:

POST
PUT
PATCH
DELETE

CSRF implementation belongs to:

app/Core/Csrf.php

Forms MUST contain:
```php
<input
type="hidden"
name="\_csrf_token"
value="{{ csrf_token }}"
>
```
Controllers MUST validate the token before processing state-changing requests.

# 14. Flash Messages

Flash messages are stored in Session.

Use:

Flash::success('...');
Flash::error('...');

Flash messages are intended for:

successful create
successful update
successful delete
user-visible errors

Flash messages MUST be consumed with:

Flash::pull()

# 15. Validation

Request validation happens at Controller level.

Business validation happens at Service level.

Example:
```text
Controller
↓
Required field
Format
Basic input validation
↓
Service
↓
Business rules
↓
Model
```
Do not rely only on frontend validation.

# 16. Logging

Application logging uses:

app/Core/Logger.php

Logs are stored in:

storage/logs/app.log

Important events SHOULD be logged, including:

transaction created
transaction updated
transaction deleted
unexpected application exceptions
important business failures

Do NOT log:

passwords
session IDs
CSRF tokens
secrets
database passwords

# 17. Exception Handling

Global exception handling is registered in:

app/Core/ExceptionHandler.php

Production MUST NOT expose:

- stack traces
- SQL statements
- file paths
- sensitive configuration
- environment values

Development may show debugging information when:

APP_DEBUG=true

Production should use:

APP_DEBUG=false

All unexpected exceptions MUST be logged.

# 18. Security Rules

AI MUST consider security whenever modifying code.

Mandatory rules:
- Use prepared statements.
- Never concatenate user input into SQL.
- Validate SQL identifiers in QueryBuilder.
- Validate CSRF for state-changing requests.
- Escape Twig output.
- Never expose secrets.
- Do not trust frontend validation.
- Validate route parameters.
- Prevent mass deletion by requiring WHERE conditions.
- QueryBuilder UPDATE and DELETE SHOULD require WHERE conditions.

# 19. QueryBuilder Safety

The QueryBuilder MUST NOT allow unsafe identifiers from raw user input.

Allowed examples:

id
name
created_at
categories.id
transactions.amount

Order direction MUST be restricted to:

ASC
DESC

JOIN operators MUST be restricted to allowed operators.

User values MUST be parameterized.

# 20. Delete Safety

The following is forbidden:
```php
->table('transactions')
->delete();
```

without a WHERE condition.

DELETE MUST require explicit filtering.

The same rule applies to UPDATE.

# 21. Naming Conventions

Use PSR-12 style.

Classes:

PascalCase

Examples:

TransactionService
TransactionModel
TransactionController
QueryBuilder

Methods and variables:

camelCase

Examples:

getTransactions()
createTransaction()
$transactionId

Database tables:

snake_case

Examples:

categories
transactions
created_at
transaction_date

# 22. File Naming

Use:

CategoryController.php
CategoryService.php
CategoryModel.php

TransactionController.php
TransactionService.php
TransactionModel.php

Twig:

index.html.twig
create.html.twig
edit.html.twig

# 23. Dependency Injection

Manual Dependency Injection is required.

Do NOT instantiate dependencies inside business methods.

Bad:

```php
public function index()
{
$service = new CategoryService(...);
}
```
Dependencies SHOULD be injected through constructors.

Correct:
```php
public function \_\_construct(
View $view,
CategoryService $categoryService
) {
}
```
The application entry point:

public/index.php

is responsible for wiring dependencies.

# 24. No Hidden Framework

Do not introduce framework-like magic unless explicitly requested.

Avoid:

- global service locator
- facades
- magic ORM
- automatic repository generation
- hidden dependency resolution
- annotations for routing
- reflection-heavy dependency containers

Keep the architecture explicit and understandable.

# 25. Expense Domain

The application currently contains:

Categories

Fields:

id
name
type
created_at
updated_at

Type:

income
expense
Transactions

Fields:

id
category_id
amount
transaction_date
note
created_at
updated_at

A transaction belongs to a category.

The transaction type MUST be derived from the category.

Do NOT duplicate category type in transactions unless explicitly required.

# 26. Change Policy for AI

Before modifying existing code:
- Read AGENTS.md.
- Read ARCHITECTURE.md.
- Read SKILL.md.
- Inspect the relevant existing files.
- Follow the existing architectural pattern.
- Avoid unnecessary refactoring.
- Do not introduce frameworks.
- Do not introduce Repository Pattern.
- Preserve backward compatibility where possible.

AI MUST NOT rewrite unrelated files just to satisfy a task.

# 27. New Feature Checklist

When implementing a new feature:

- Add/modify route.
- Add/modify Controller.
- Add/modify Service.
- Add/modify Model.
- Use QueryBuilder.
- Add/update Twig view.
- Add validation.
- Add CSRF for state-changing requests.
- Add flash messages when appropriate.
- Add logging for important business events.
- Handle exceptions.
- Verify the complete request flow.

# 28. Coding Style

All PHP files MUST start with:

```php
<?php

declare(strict_types=1);
```

Use strict typing.

Prefer:
- public function find(int $id): ?array
- instead of untyped methods.
- Use explicit return types.
- Prefer small methods with one responsibility.

# 29. Do Not Overengineer

This is intentionally a mini application.

Do not introduce unnecessary layers such as:
- Repository
- DAO
- DTO
- UnitOfWork
- EventBus
- CQRS
- CommandBus
- Mediator
- ORM

unless explicitly requested.

The preferred architecture remains:
```text
Controller
    ↓
Service
    ↓
Model
    ↓
QueryBuilder
```
# 30. Final Rule

When uncertain, prefer:
- simpler code
- explicit dependencies
- existing project conventions
- security
- maintainability
- clear separation of responsibilities

over:

- framework conventions
- abstractions for abstraction's sake
- excessive generic code
- unnecessary dependencies
