# Rounder API & Admin

Laravel 13 API and Bootstrap dashboard for the Rounder resident app.

## Local setup

Requirements: PHP 8.3+, Composer and MySQL 8+.

1. Copy `.env.example` to `.env` and configure the MySQL connection.
2. Set `ROUND_ADMIN_EMAIL` and a strong `ROUND_ADMIN_PASSWORD` for the first administrator.
3. Run `composer install`, `php artisan key:generate`, then `php artisan migrate --seed`.
4. Run `php artisan serve` and open `/admin/login`.

The seeder creates the administrator only when both environment credentials are set. Do not commit `.env`.

## API surface

All app endpoints are versioned under `/api/v1`.

- Public: `GET /health`, `POST /auth/register`, `POST /auth/login`
- Authenticated: `GET /me`, `POST /auth/logout`
- Resident: `GET /resident/dashboard`, `GET /resident/homes`, `POST /resident/homes/link`
- Resident services: `GET /resident/services`
- Resident bookings: `GET|POST /resident/bookings`, `POST /resident/bookings/{id}/cancel`

Mobile requests use Sanctum bearer tokens and an `Accept-Language` header. Configure Flutter with `--dart-define=API_BASE_URL=https://your-host/api/v1`.

## Accounts, data and security

A shared `users` table serves administrator, resident and provider accounts; `role_id` references `roles`. Laravel's hashed password cast stores no plaintext. Admin dashboard routes require both a web session and the admin role. Residents use Sanctum tokens; resetting a password through the admin revokes their active app tokens.

The MySQL schema includes resident-home membership, cities, categorized services, booking and payment statuses, bookings, booking status history and payments. Lookup rows have numeric IDs and stable codes; localized labels live in related translation tables. API writes validate ownership, unique identifiers, E.164 phone format, allow-listed locale, strong confirmed password, active service IDs, future booking dates and bounded user-entered notes.

## Admin dashboard

Bootstrap pages cover session login, real database KPI totals, account administration, home administration and an About page. Arabic is the default with RTL layout; English switches the dashboard to LTR. KPI sources are residents, approved providers, bookings, pending bookings and paid payment totals.

## Verification

GitHub Actions validates Composer, boots the route table, runs migrations and seeders on SQLite, and runs the API registration/validation tests. See [Project Guide](docs/PROJECT_GUIDE.md) for schema, route, validation and editing details.
