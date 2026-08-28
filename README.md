# Mini Expense Management

A small web-based expense management application built with plain PHP 8.5.

The project is intentionally developed **without any PHP framework** in order to keep the architecture simple, explicit, and easy to understand.

## Features

Current features:

- Category management
  - Income categories
  - Expense categories
- Transaction management
  - Create transaction
  - View transactions
  - Update transaction
  - Delete transaction
- Transaction pagination
- Category and transaction validation
- CSRF protection
- Session management
- Flash messages
- Dynamic routing
- Exception handling
- Application logging
- MySQL database
- Twig templates
- Custom Query Builder

## Technology Stack

| Component | Technology |
|---|---|
| Language | PHP 8.5 |
| Database | MySQL |
| Database Driver | PDO |
| Template Engine | Twig 3.x |
| Dependency Management | Composer |
| Architecture | MVC + Service Layer |
| Router | Custom Router |
| Query Builder | Custom QueryBuilder |
| Session | Native PHP Session |
| Validation | Custom Validator |
| CSRF | Custom CSRF |
| Logging | Custom Logger |

No PHP framework is used.

## Architecture

The application follows:

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