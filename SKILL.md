# 3. `SKILL.md`

# SKILL.md

# AI Development Skill

This document defines how an AI coding agent should work inside this project.

---

# 1. Before Making Changes

The AI MUST first inspect:
- AGENTS.md
- ARCHITECTURE.md
- SKILL.md

Then inspect the relevant source files.

Do not make assumptions about implementation if the source code can be inspected.

# 2. Understand the Request

Before coding, identify:

- HTTP entry point.
- Route.
- Controller.
- Service.
- Model.
- QueryBuilder usage.
- View.
- Validation requirements.
- Security requirements.
- Database changes.

For example:

"Add transaction filtering"

should be interpreted as:
```text
Route
↓
Controller
↓
Service
↓
TransactionModel
↓
QueryBuilder
↓
Twig
```
# 3. Implementation Order

When implementing a complete feature, follow this order:

Step 1 - Database

If required:

database/schema.sql

Add or modify:

- table
- column
- index
- foreign key
Step 2 - Model

Add database methods.

Example:
```php
public function findByCategory(
int $categoryId
): array
{
return $this->queryBuilderFactory
->make()
->table('transactions')
->where(
'category_id',
'=',
$categoryId
)
->get();
}
```
Step 3 - Service

Add business logic.

Example:
```php
public function getByCategory(
int $categoryId
): array
{
    if ($categoryId <= 0) {
throw new InvalidArgumentException(
'Invalid category.'
);
}

    return $this->transactionModel
        ->findByCategory($categoryId);

}
```
Step 4 - Controller

Add:

- request handling
- input validation
- CSRF
- Service call
- response

Step 5 - Route

Add route to:

routes/web.php
Step 6 - View

Add/update Twig template.

Step 7 - Logging

Add logging if the feature changes important business state.

# 4. Controller Skill

Controllers should be thin.

Preferred:
```php
public function store(): void
{
Csrf::validateOrFail(
$\_POST['_csrf_token'] ?? null
);

    $data = $this->readInput();

    $validator = $this->validateInput(
        $data
    );

    if ($validator->fails()) {
        $this->render(...);

        return;
    }

    $this->transactionService
        ->createTransaction(...);

    Flash::success(
        'Transaction created successfully.'
    );

    $this->redirect(
        '/transactions'
    );

}
```
Avoid putting business logic directly inside Controller.

# 5. Service Skill

Services should be reusable from different entry points.

Avoid:
```php
$_POST
$\_GET
$\_SESSION
header()
echo
```
inside Service classes.

Service methods should receive explicit parameters.

Good:
```php
createTransaction(
int $categoryId,
string $amount,
string $date,
?string $note
)
```
Bad:
```php
createTransaction()

that reads $\_POST internally.
```
# 6. Model Skill

Use:

QueryBuilderFactory

to get a fresh QueryBuilder.

Example:
```php
return $this->queryBuilderFactory
->make()
->table('transactions')
->select([
'id',
'amount',
])
->where(
'category_id',
'=',
$categoryId
)
->orderBy(
'transaction_date',
'DESC'
)
->get();
```
Do not use raw PDO in Models unless there is a very strong technical reason and it is explicitly approved.

# 7. QueryBuilder Skill

Before extending QueryBuilder, inspect its current state.

Do not duplicate:

- parameter binding logic
- identifier validation
- JOIN logic
- WHERE logic
- pagination logic

New QueryBuilder functionality should be:

- small
- explicit
- parameterized
- secure
- chainable

Example:
```php
$queryBuilder
->table('transactions')
->where(
'category_id',
'=',
$categoryId
)
->orderBy(
'transaction_date',
'DESC'
)
->paginate(
$page,
$perPage
);
```
# 8. SQL Safety

Never create queries like:
```php
$sql = "SELECT \* FROM transactions WHERE id = $id";

Use QueryBuilder:

$queryBuilder
->table('transactions')
->where(
'id',
'=',
$id
)
->first();
```
Never concatenate:

- user input
- query parameter
- route parameter
- form data

into SQL.

# 9. ORDER BY Safety

Never allow direct user input:
```php
->orderBy(
$\_GET['sort'],
$\_GET['direction']
);
```
without whitelisting.

Example:
```php
$allowedSorts = [
'date' => 'transactions.transaction_date',
'amount' => 'transactions.amount',
'created_at' => 'transactions.created_at',
];

$sort = $\_GET['sort'] ?? 'date';

$sortColumn = $allowedSorts[$sort]
?? $allowedSorts['date'];

$direction = strtoupper(
$\_GET['direction'] ?? 'DESC'
);

if (!in_array(
$direction,
['ASC', 'DESC'],
true
)) {
$direction = 'DESC';
}
```
Then:
```php
->orderBy(
$sortColumn,
$direction
)
```
# 10. CSRF Skill

Every state-changing HTML form MUST contain:
```php
<input
type="hidden"
name="\_csrf_token"
value="{{ csrf_token }}"

>
```
Controller MUST validate:
```php
Csrf::validateOrFail(
$\_POST['_csrf_token'] ?? null
);
```
before calling Service.

# 11. Validation Skill

Always validate:

- IDs
- required fields
- numeric values
- dates
- string lengths
- enum-like values

Example:
```php
$type = $\_POST['type'] ?? '';

if (!in_array(
$type,
['income', 'expense'],
true
)) {
// validation error
}
```
Never trust:

- HTML select
- HTML input
- JavaScript validation

as security validation.

# 12. Transaction Skill

Use database transactions when one business operation contains multiple dependent writes.

Example:
```php
$this->database->beginTransaction();

try {
$transactionId = ...;

    // additional operations

    $this->database->commit();

    return $transactionId;

} catch (\Throwable $exception) {
    if ($this->database->inTransaction()) {
$this->database->rollBack();
}

    throw $exception;

}
```
Never commit before all required operations succeed.

# 13. Flash Message Skill

After successful state-changing operations:
```php
Flash::success(
'Transaction created successfully.'
);
```
Then:
```php
$this->redirect(
'/transactions'
);
```
Use POST → Redirect → GET.

Avoid rendering the list directly after successful POST unless there is a specific reason.

# 14. Twig Skill

Twig templates should be presentation-focused.

Good:
```php
{{ transaction.amount }}
```
Good:
```php
{% if transaction.category_type == 'expense' %}
Expense
{% endif %}
```
Bad:
```php
{% set result = database.query(...) %}
```
Bad:
```php
{% set transactions = model.getAll() %}
```
# 15. Error Handling Skill

Do not silently swallow exceptions.

Bad:
```php
try {
// ...
} catch (\Throwable $exception) {
}
```
Correct:
```php
try {
// ...
} catch (\Throwable $exception) {
$this->logger->error(
'Operation failed.',
[
'message' => $exception->getMessage(),
]
);

    throw $exception;

}
```
Central unexpected exceptions are handled by:

ExceptionHandler

# 16. Logging Skill

Log state-changing business operations.

Examples:
```php
$logger->info(
'Transaction created.',
[
'transaction_id' => $id,
]
);
```
Do not log:

- password
- CSRF token
- session ID
- database password
- .env contents
- authentication secrets

# 17. Dynamic Route Skill

Routes such as:

- /categories/{id}
- /transactions/{id}
- /transactions/{id}/edit

must use Router dynamic parameters.

Controllers should accept route parameters explicitly:
```php
public function edit(
string $id
): void
```
The Controller is responsible for converting input to the expected type.

Example:
```php
$transactionId = (int) $id;
```
The Service must still validate:
```php
if ($transactionId <= 0) {
throw new InvalidArgumentException(
'Invalid transaction ID.'
);
}
```
# 18. CRUD Skill

For every CRUD entity:

- List
- Create form
- Create action
- Edit form
- Update action
- Delete action

Expected route design:

- GET /transactions
- GET /transactions/create
- POST /transactions
- GET /transactions/{id}/edit
- POST /transactions/{id}
- POST /transactions/{id}/delete

DELETE may use POST because the custom Router currently focuses on simple HTML form handling.

# 19. Testing Changes

After code changes, verify at least:
```text
PHP syntax
php -l app/Core/QueryBuilder.php
```
or:
```text
find app -name "\*.php" -print0 |
xargs -0 -n1 php -l
Composer autoload
composer dump-autoload
Development server
php -S localhost:8000 -t public
```
Then test the relevant route.

# 20. Manual Verification

For CRUD features manually verify:
```text
Create
    valid input → record created
    invalid input → validation error
    missing CSRF → rejected
Read
    existing record → displayed
    missing record → 404
Update
    valid input → updated
    invalid input → form restored with errors
    missing CSRF → rejected
Delete
    valid CSRF → deleted
    missing CSRF → rejected
```
# 21. Database Verification

After a database feature, verify:
```sql
SELECT \*
FROM categories;

SELECT \*
FROM transactions;
```
Also verify:

- foreign key
- indexes
- NULL handling
- amount precision
- date format

# 22. Code Review Skill

Before finishing a task, check:
```text
Architecture
Controller → Service → Model → QueryBuilder
Security
CSRF
Prepared statements
Escaping
Validation
Error Handling
ExceptionHandler
Logger
Transaction rollback
Code Quality
PSR-12
Strict types
Return types
Readable naming
Small methods
```
# 23. Avoid Unnecessary Refactoring

If a requested feature can be implemented by modifying:

1 Controller
1 Service
1 Model
1 View
1 Route

do not rewrite the entire architecture.

Do not rename existing classes without a strong reason.

Do not introduce abstractions unrelated to the requested feature.

# 24. New Class Rule

Before creating a new class, ask:

- Does an existing class already own this responsibility?
- Is the new class necessary?
- Does it create a new architectural layer?
- Does it violate the no-framework/no-repository rule?

Prefer extending an existing appropriate class when responsibilities remain clear.

# 25. Final Implementation Principle

Every feature should be understandable by reading:
```text
routes/web.php
↓
Controller
↓
Service
↓
Model
↓
QueryBuilder
```
If the behavior cannot be understood through this flow, the implementation may be too complex.

Keep the project explicit, predictable and framework-free.
