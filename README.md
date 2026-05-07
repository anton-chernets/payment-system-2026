# Payment System

Laravel 12 + PHP 8.2 + Docker

## Requirements

- [Docker Desktop](https://www.docker.com/products/docker-desktop/)
- Git

## Quick Start

**1. Clone the repository**
```bash
git clone https://github.com/anton-chernets/payment-system-2026.git
cd payment-system-2026
```

**2. Copy the env file**
```bash
cp src/.env.example src/.env
```

**3. Configure `src/.env`**
```
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=brazy
DB_USERNAME=brazy
DB_PASSWORD=secret
```

**4. Start containers**
```bash
docker compose up -d --build
```

**5. Install dependencies and run migrations**
```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

Application is available at: **http://localhost:8080**

---

## API

### `POST /api/payments` — unified format

```json
{
    "provider":     "paygate_a",
    "order_id":    "ORD-10001",
    "amount":      100.00,
    "currency":    "USD",
    "description": "optional"
}
```

Response `201`:
```json
{
    "id":          1,
    "provider":    "paygate_a",
    "external_id": "<provider payment id>",
    "status":      "pending",
    "payment_url": "https://paygate-a.test/pay/<uuid>"
}
```

---

### `POST /api/external/paygate-a/create` — PayGateA native

Request:
```json
{ "merchant_order_id": "ORD-10001", "amount": "100.00", "currency": "USD" }
```

Response `201`:
```json
{ "payment_id": "<uuid>", "payment_url": "https://paygate-a.test/pay/<uuid>", "status": "new" }
```

---

### `POST /api/external/paygate-b/payments` — PayGateB native

Request:
```json
{ "order": "ORD-10001", "total": 10000, "currency_code": "USD", "note": "optional" }
```

Response `201`:
```json
{ "id": "<uuid>", "redirect_url": "https://paygate-b.test/checkout/<uuid>", "state": "created" }
```

---

### `POST /api/callbacks/paygate-a`

Accepts two formats — detected by the presence of `payment_id`:

**Format 1 (PayGateA native):**
```json
{ "payment_id": "<uuid>", "merchant_order_id": "ORD-10001", "status": "paid" }
```
Valid `status` values: `new`, `paid`, `rejected`

**Format 2:**
```json
{ "id": "<uuid>", "order": "ORD-10001", "state": "success" }
```
Valid `state` values: `created`, `success`, `error`

---

### `POST /api/callbacks/paygate-b`

```json
{ "id": "<uuid>", "order": "ORD-10001", "state": "success" }
```
Valid `state` values: `created`, `success`, `error`

---

### Callback responses

**Success:**
```json
{ "success": true }
```

**Payment not found** `404`:
```json
{ "error": "Payment not found" }
```
Returned when `order_id` + `transaction_id` don't match any stored payment, or `transaction_id` is not a valid UUID.

**Transition not allowed** `422`:
```json
{ "error": "Transition from success to success is not allowed" }
```
Returned when the current payment status does not allow the incoming status.

---

### Payment status transitions

```
pending    → pending, processing, success, failed   ✓
processing → processing, success, failed            ✓
success    → (terminal, all transitions blocked)    ✗
failed     → (terminal, all transitions blocked)    ✗
```

---

## Architecture

### Module structure

All payment logic lives in `app/PaymentSystem/` as a self-contained module with its own namespace `App\PaymentSystem\*`.

```
app/PaymentSystem/
  Actions/          — single-responsibility business operations
  Activities/       — laravel-workflow activity jobs
  Contracts/        — interfaces (PaymentProviderInterface)
  Controllers/      — HTTP layer only, no business logic
  DTO/              — immutable data transfer objects
  Enums/            — PaymentStatus with transition rules
  Exceptions/       — domain exceptions with render() for automatic HTTP responses
  Mappers/          — format conversion between internal DTOs and provider wire format
  Middleware/       — ResolvePaymentProvider
  Models/           — Eloquent models (Payment, PaymentProvider, Currency)
  Providers/        — gateway implementations (PayGateAProvider, PayGateBProvider)
  Repositories/     — data access layer
  Requests/         — FormRequests with per-provider validation rules
  Resources/        — API response formatting (PaymentResource)
  Workflows/        — ProcessPaymentCallbackWorkflow
  routes.php        — module routes, included from routes/api.php
```

### Request flow

**Payment creation (`POST /api/payments`)**
```
Request → CreatePaymentRequest
        → PaymentController::create()
        → PaymentProviderFactory::make() → provider::createPayment()
        → PaymentRepository::createFromDTOs()
        → PaymentResource
```

**External provider format (`POST /api/external/{provider}`)**
```
Request → ResolvePaymentProvider (middleware)
        → ExternalCreateRequest — delegates rules to provider::externalRequestRules()
        → PaymentController::createExternal()
        → provider::parseExternalRequest() → CreatePaymentDTO
        → provider::createPayment() + PaymentRepository::createFromDTOs()
        → provider::formatExternalResponse()
```

**Callback (`POST /api/callbacks/{provider}`)**
```
Request → ResolvePaymentProvider (middleware)
        → CallbackRequest — per-provider + per-format validation
        → CallbackController
              ├─ provider::handleCallback() → CallbackPayloadDTO
              ├─ PaymentRepository::findForCallback(orderId, transactionId)
              │     returns null if not found OR transactionId is not a valid UUID
              ├─ payment.status.canTransitionTo(dto.status) — 422 if blocked
              └─ ProcessPaymentCallbackWorkflow::execute()
                    ├─ CheckStatusTransitionActivity
                    └─ UpdatePaymentStatusActivity
```

### Payment lookup on callback

`findForCallback` matches by **both fields simultaneously**:
```sql
WHERE order_id = ? AND transaction_id = ?
```
`transaction_id` stores the provider's own payment ID (returned at creation time), so every callback can be verified against both the internal order reference and the provider-side ID.

---

## Adding a new provider

**1. Create the mapper** — `app/PaymentSystem/Mappers/PayGateCMapper.php`

Implements `toProviderRequest`, `fromProviderResponse`, `fromCallback`.

**2. Create the provider** — `app/PaymentSystem/Providers/PayGateCProvider.php`

Implement `PaymentProviderInterface`:

| Method | Purpose |
|--------|---------|
| `createPayment(CreatePaymentDTO)` | Call provider API, return `PaymentResponseDTO` |
| `validateCallback(array)` | Basic payload check |
| `handleCallback(array)` | Map payload → `CallbackPayloadDTO` via mapper |
| `externalRequestRules()` | Validation rules for the external endpoint |
| `parseExternalRequest(array)` | Normalise to `CreatePaymentDTO` |
| `formatExternalResponse(PaymentResponseDTO)` | Return provider-native response array |

**3. Register in the factory** — `app/PaymentSystem/PaymentProviderFactory.php`

```php
'paygate_c' => PayGateCProvider::class,
```

**4. Add callback validation** — `app/PaymentSystem/Requests/CallbackRequest.php`

```php
'paygate-c' => [
    'transaction_id' => ['required', 'string'],
    'order_id'       => ['required', 'string'],
    'status'         => ['required', Rule::in(['pending', 'completed', 'failed'])],
],
```

**5. Add route** — `app/PaymentSystem/routes.php`

```php
Route::post('/external/paygate-c/payments', [PaymentController::class, 'createExternal'])
    ->defaults('provider', 'paygate-c');
```

**6. Seed the provider record** — `database/seeders/PaymentProviderSeeder.php`

```php
['slug' => 'paygate_c', 'name' => 'PayGate C'],
```

No changes needed in controllers, workflows, activities, or repositories.

---

## Design decisions

**`PaymentProviderInterface` owns its own format**
Each provider knows how to parse its own requests and format its own responses. Controllers have no knowledge of provider-specific field names.

**Mappers as a separate layer**
Mappers translate between provider wire format and internal DTOs. A provider API change only touches its mapper.

**`transaction_id` stores the provider's payment ID**
When a payment is created the provider returns its own ID. That value is stored as `transaction_id` so incoming callbacks can be matched against both the internal order reference and the provider-side ID.

**Callback validation is synchronous**
The controller checks payment existence and status transition validity before dispatching the workflow. Invalid callbacks are rejected with 404 or 422 immediately — no workflow is started for a request that cannot be processed.

**Repositories over direct Eloquent**
Controllers and activities call typed repository methods rather than building raw queries inline.

**Workflow for callback processing**
`ProcessPaymentCallbackWorkflow` provides reliable, retryable execution for the status validation + update step. If a worker fails mid-flight, Horizon retries without re-processing the HTTP request.

**Providers and currencies in DB**
Validation uses `Rule::in(repository->allSlugs())` — adding a provider requires only a DB record and a code class.

---

## Docker Services

| Service | Image | Port |
|---------|-------|------|
| app | PHP 8.2-FPM | 9000 (internal) |
| nginx | nginx:alpine | 8080 |
| db | PostgreSQL 16 | 5432 |
| redis | Redis 7 | 6379 |
| horizon | PHP 8.2-FPM | — |

---

## Laravel Horizon

Queue monitoring at **http://localhost:8080/horizon**

```bash
docker compose exec app php artisan horizon:status
docker compose exec app php artisan horizon:pause
docker compose exec app php artisan horizon:continue
docker compose logs -f horizon
```

> Workflows (laravel-workflow) require Horizon to be running.

---

## Useful Commands

```bash
# Containers
docker compose up -d
docker compose down
docker compose exec app bash

# Database
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan migrate:fresh --seed

# Artisan / Tinker
docker compose exec app php artisan tinker
docker compose exec app php artisan <command>
docker compose logs -f
```
