````markdown

## Description
User api service Demonstrates clean Laravel code applying SOLID principles (SRP, DIP) with a service layer, domain exceptions, thin controllers, explicit validation, and seeded role-based test users.

---

## Table of Contents
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Principles and Structure](#principles-and-structure)
- [Project Structure](#project-structure)
- [Seeded Test Users](#seeded-test-users)
- [Setup](#setup)
- [Running With Docker](#running-with-docker)
- [Running Locally](#running-locally)
- [Authentication Flow](#authentication-flow)
- [Error Format](#error-format)
- [Testing](#testing)

---

## Features
- ✅ Create User API
- ✅ Role-based permission (`Administrator`, `Manager`, `User`)
- ✅ JWT Authentication
- ✅ PostgreSQL
- ✅ Database Seeder
- ✅ Docker (Nginx + PHP-FPM + Postgres)

---

## 🧱 Tech Stack

- PHP >= 8.4
- Laravel
- PostgreSQL
- tymon/jwt-auth
- Docker & Docker Compose
- Nginx
- PHPUnit

## principles and structure
- Controllers: Thin orchestration, delegate to `UserService` | Single Responsibility Principle (SRP) |
- Service Layer: Encapsulates user lifecycle, permissions, side effects | SRP |
- Domain Exceptions: Explicit failure modes: forbidden, duplicate email, not found | Clean Error Handling |
- Validation: `CreateUserRequest` / `UpdateUserRequest` isolate rules | SRP |
- Dependency Inversion: Controller depends on `UserServiceInterface` (bound in container) | Dependency Inversion Principle (DIP) |
- Authentication: JWT (Authorization: Bearer <token>) on protected routes | Standard Authentication |

## 📂 Project Structure (Important Parts)

app/
├── Http/
│ ├── Controllers/Api
│ │ ├── AuthController.php
│ │ └── UserController.php
│ ├── Requests/
│ │ ├── CreateUserRequest.php
│ │ └── UpdateUserRequest.php
├── Models/User.php
├── Services/
│ ├── UserService.php
│ └── Contracts/UserServiceInterface.php
├── Exceptions/Domain/
database/
├── migrations/
├── seeders/
routes/
├── api.php
docker/
├── nginx/default.conf
└── php/Dockerfile
docker-compose.yml

---

## Seeded Test Users
Seeder creates 2 users per role (**Administrator**, **Manager**, **User**) if absent. All use password: `password123`.

| Role | Emails (example) |
| Administrator: administrator1@example.com, administrator2@example.com |
| Manager: manager1@example.com, manager2@example.com |
| User: user1@example.com, user2@example.com |

Quick login test:
```bash
curl -X POST http://localhost:8080/api/login \
 -H "Content-Type: application/json" \
 -d '{"email":"administrator1@example.com","password":"password123"}'
````

-----

## Setup

```bash
cp .env.example .env
composer install
php artisan key:generate
```

Adjust DB host:

- Docker: `DB_HOST=userapidb`
- Local (no Docker): `DB_HOST=localhost`

Set `JWT_SECRET` (already present in `.env`).

-----

## Running With Docker

```bash
docker compose up --build
```

Startup command runs migrations + seeds automatically. App available at: **http://localhost:8080**

-----

## Running Locally (no Docker)

Ensure PostgreSQL running and `.env` points to localhost:

```bash
php artisan migrate --seed
php artisan serve
```

App: **http://localhost:8000**

-----

## Authentication Flow

1.  Register or use seeded credentials
2.  Login issues **JWT** (signed with `JWT_SECRET`)
3.  Client sends `Authorization: Bearer <token>`
4.  Middleware resolves user context
5.  Logout (token invalidation strategy: client discard / blacklist)

-----

### **cURL Examples**

```bash
# Register
curl -X POST http://localhost:8080/api/register \
 -H "Content-Type: application/json" \
 -d '{"name":"Alice","email":"alice@example.com","password":"Secret123"}'

# Login (seeded admin)
curl -X POST http://localhost:8080/api/login \
 -H "Content-Type: application/json" \
 -d '{"email":"administrator1@example.com","password":"password123"}'

# List users (protected)
curl -X GET http://localhost:8080/api/users \
 -H "Authorization: Bearer YOUR_JWT"

# Create user (admin/manager)
curl -X POST http://localhost:8080/api/users \
 -H "Authorization: Bearer ADMIN_JWT" \
 -H "Content-Type: application/json" \
 -d '{"name":"Bob","email":"bob@example.com","password":"Secret123","role":"User"}'
```

-----

## Error Format

Validation:

```json
{
  "error": "validation_failed",
  "messages": {
    "email": ["Email already exists."]
  }
}
```

Other HTTP/Domain Errors:

- `403` forbidden
- `404` not\_found
- `409` email\_exists
- `500` user\_creation\_failed / update\_failed / email\_failed

-----

## Testing

```bash
php artisan test
```

Add unit tests for permission matrix, duplicate email, exception mapping.

-----
