# Rounder API & Admin

Laravel 13 backend and Bootstrap administration dashboard for the Rounder resident app.

## Requirements and local setup

- PHP 8.3+, Composer, and MySQL 8+
- Copy `.env.example` to `.env`; configure the database and set a strong `ROUND_ADMIN_EMAIL` and `ROUND_ADMIN_PASSWORD`.
- Run `composer install`, `php artisan key:generate`, and `php artisan migrate --seed`.
- Run `php artisan serve`; open `/admin/login`.
- The initial administrator is only created when its environment credentials are set. Do not commit `.env`.

## Data and account model

A shared `users` table holds admin, resident and provider accounts, distinguished by required `role_id`. Passwords are hashed by Laravel's model cast. The admin panel lists accounts and can create users or reset passwords; resetting revokes existing mobile tokens. Admin routes require both an authenticated session and the admin role.

MySQL relations include translated role/city/service/status lookup rows, homes and resident memberships, categorized services, bookings, booking status history, providers and payments. Reference values use foreign key IDs and stable codes; translations live in related tables.

## API

- `GET /api/v1/health`
- `POST /api/v1/auth/register`, `POST /api/v1/auth/login`
- `GET /api/v1/me`, `POST /api/v1/auth/logout` (Sanctum bearer token)
- `GET /api/v1/resident/dashboard`
- `GET /api/v1/resident/services`
- `GET|POST /api/v1/resident/bookings`

The mobile client sends the bearer token and `Accept-Language`; Arabic and English are supported in the dashboard, and resident-facing API text uses Arabic/English/Urdu locale codes.

## Admin dashboard

The admin defaults to Arabic and switches between Arabic RTL and English LTR. The About page introduces Rounder. The dashboard KPIs (residents, approved providers, booking totals, pending bookings and paid revenue) are computed from database rows, so an empty installation shows real zeros.

## Verification

GitHub Actions runs Composer validation and Laravel route bootstrap checks. The mobile repository builds a release APK and uploads it as an Actions artifact when its workflow succeeds.
