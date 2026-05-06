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
