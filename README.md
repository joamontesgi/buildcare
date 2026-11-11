# BuildCare Backend

Laravel 13 — API REST, autenticación Sanctum, Livewire (schedule), exportación PDF, catálogos.

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

## Desarrollo

```bash
php artisan serve
```

API: `http://localhost:8000`

## Credenciales

- Email: `admin@buildcare.com`
- Password: `password`
