# 💰 Finance API

**Symfony · API Platform · JWT · PostgreSQL · Docker**

A RESTful finance management API built with **Symfony** and **API Platform**, featuring **JWT authentication**, **user-owned resources**, and a **fully Dockerized setup** for easy testing and onboarding.

This project demonstrates real-world backend patterns: authentication, authorization, ownership, and clean API design.

---

## 🚀 Tech Stack

- PHP 8.3
- Symfony
- API Platform
- Doctrine ORM
- PostgreSQL
- JWT Authentication (LexikJWTAuthenticationBundle)
- Docker & Docker Compose

---

## ✨ Features

- JWT-based authentication
- Stateless REST API
- User-owned **Categories** and **Transactions**
- Automatic ownership assignment (server-side)
- Swagger / OpenAPI documentation
- Dockerized environment (no local PHP/Postgres required)

---

## 🐳 Run the project with Docker (recommended)

### 1️⃣ Clone the repository

```bash
git clone https://github.com/amanilabs/symfony-finance-api.git
cd symfony-finance-api
```

### 2️⃣ Running the Project (Docker)

```
docker compose up --build
docker compose exec app php bin/console doctrine:migrations:migrate
```

---

### 2️⃣ Demo User Setup (Development Only)

These steps are intended for local development and demonstration purposes only.

1. Generate password hash:
   docker compose exec app php bin/console security:hash-password
2. Insert user into database:

```
docker compose exec -it db psql -U app -d app
INSERT INTO "user" (email, roles, password)
VALUES ('admin@example.com', '["ROLE_USER"]', 'HASH_HERE');
\q
```

---

## Authentication (JWT)

```
POST /api/login_check
Body:
{ "email": "admin@example.com", "password": "password" }
```

Returns a JWT token used for secured requests.

---

## Swagger API Documentation

URL: http://127.0.0.1:8000/api/docs
Use the Authorize button and paste the JWT token (without 'Bearer').

---

## Categories

POST /api/categories
{ "name": "Groceries" }
Owner is automatically assigned from the authenticated user.

---

## Transactions

POST /api/transactions
{
"amount": 55.75,
"description": "Grocery shopping",
"type": "expense",
"date": "2023-11-05",
"category": "/api/categories/1"
}

---

## Security & Architecture

• Stateless JWT authentication
• Owner fields are not writable from requests
• Ownership enforced server-side using processors
• Clean separation of concerns

---

## Project Purpose

This project demonstrates professional Symfony backend architecture, secure API design, JWT
authentication, and Docker-based development workflows.
