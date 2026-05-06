# Test Payment System

Laravel 12 + PHP 8.2 + Docker

## Requirements

- [Docker Desktop](https://www.docker.com/products/docker-desktop/)
- Git

## Quick Start

**1. Clone the repository**
```bash
git clone https://github.com/anton-chernets/test-payment-system.git
cd test-payment-system
```

**2. Copy the env file**
```bash
cp .env.example .env
```

**3. Configure `.env`** — set database credentials:
```
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=brazy
DB_USERNAME=brazy
DB_PASSWORD=secret
```

**4. Go to the project root and start containers**
```bash
cd ..
docker compose up -d --build
```

**5. Install dependencies and run migrations**
```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

Application is available at: **http://localhost:8080**

---

## Docker Services

| Service | Image | Port |
|---------|-------|------|
| app | PHP 8.2-FPM | 9000 (internal) |
| nginx | nginx:alpine | 8080 |
| db | PostgreSQL 16 | 5432 |
| redis | Redis 7 | 6379 |

Laravel source code is located in the `src/` directory, Docker configs are in `docker/`.

---

## API Documentation (Swagger)

The project uses [l5-swagger](https://github.com/DarkaOnLine/L5-Swagger) based on OpenAPI 3.0.

| URL | Description |
|-----|-------------|
| http://localhost:8080/api/documentation | Swagger UI |
| http://localhost:8080/api/documentation.json | OpenAPI JSON schema |

> **Note:** `storage/api-docs/api-docs.json` is excluded from git.
> You must regenerate it locally after cloning or after any annotation changes:

```bash
docker compose exec app php artisan l5-swagger:generate
```

**Adding annotations to a controller:**
```php
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/example',
    summary: 'Example endpoint',
    tags: ['Example'],
    responses: [
        new OA\Response(response: 200, description: 'OK')
    ]
)]
public function index(): JsonResponse
{
    ...
}
```

---

## Laravel Horizon

Queue monitoring dashboard powered by Redis.

| URL | Description |
|-----|-------------|
| http://localhost:8080/horizon | Horizon Dashboard |

Horizon runs as a separate Docker service (`brazy_horizon`) and starts automatically with `docker compose up`.

```bash
# Check Horizon status
docker compose exec app php artisan horizon:status

# Pause / resume queue processing
docker compose exec app php artisan horizon:pause
docker compose exec app php artisan horizon:continue

# View logs
docker compose logs -f horizon
```

---

## Laravel Tinker

Interactive REPL for exploring the application from the command line.

```bash
docker compose exec app php artisan tinker
```

Examples:
```php
>>> User::count()
>>> User::factory()->create()
>>> dispatch(new App\Jobs\ExampleJob())
```

---

## IDE Helper

The project includes [barryvdh/laravel-ide-helper](https://github.com/barryvdh/laravel-ide-helper) for better IDE autocompletion.

Generated files (committed to the repo):

| File | Purpose |
|------|---------|
| `_ide_helper.php` | Facade autocompletion |
| `_ide_helper_models.php` | Model PHPDoc |
| `.phpstorm.meta.php` | PhpStorm metadata |

**Regenerate after adding models or facades:**
```bash
docker compose exec app php artisan ide-helper:generate
docker compose exec app php artisan ide-helper:models --nowrite
docker compose exec app php artisan ide-helper:meta
```

---

## Laravel Workflow — Payment Processing

Durable workflow engine powering the payment lifecycle for PayGateA and PayGateB.

### Architecture

The system provides a unified API over multiple payment providers. Provider-specific logic is isolated behind a common interface — adding a new provider (PayGateC, etc.) requires no changes to the core flow.

```
PaymentController
       │
       ▼
PaymentProviderFactory  ──resolves──►  PayGateAProvider
                                       PayGateBProvider
                                       ...
       │
       ▼
PaymentWorkflow (laravel-workflow)
       │
       ├── CreatePaymentActivity   — calls provider API, persists payment
       └── HandleCallbackActivity  — verifies & processes provider callback
```

### Payment flows

**1. Create payment**
```
POST /api/payments
{
  "provider": "paygate_a",  // or "paygate_b"
  "amount": 100.00,
  "currency": "USD",
  "order_id": "order-123"
}
```

**2. Handle provider callback**
```
POST /api/payments/callback/{provider}
```

### Workflow commands
```bash
# Create a new workflow
docker compose exec app php artisan make:workflow PaymentWorkflow

# Create a new activity
docker compose exec app php artisan make:activity CreatePaymentActivity

# Monitor running workflows via Horizon
# http://localhost:8080/horizon
```

### Adding a new provider

1. Create `app/Payment/Providers/PayGateCProvider.php` implementing `PaymentProviderInterface`
2. Register it in `PaymentProviderFactory`
3. No changes needed in controllers, workflows or activities

> Workflows require Horizon (Redis queue) to be running.

---

## Useful Commands

```bash
# Start containers
docker compose up -d

# Stop containers
docker compose down

# Enter PHP container
docker compose exec app bash

# Artisan commands
docker compose exec app php artisan <command>

# Composer commands
docker compose exec app composer <command>

# Logs
docker compose logs -f
```
