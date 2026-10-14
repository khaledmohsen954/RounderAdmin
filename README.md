# Rounder API & Admin

Laravel 13 API and Bootstrap dashboard for the Rounder resident app.

## Architecture

Rounder is a **modular monolith**: one Laravel application and one MySQL database.
Domain code lives in `app/Modules/{Domain}/` with Routes, Requests, Controllers,
Services, Models, Enums, Events and Observers directories. Controllers only
handle transport; FormRequests validate and authorize; services own transactions
and dispatch events after commit. Shared actions and infrastructure live at
the top level.

See [Arabic architecture guide](docs/MODULAR_MONOLITH_AR.md).

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

GitHub Actions validates Composer, boots the route table, runs migrations and seeders on an isolated MySQL 8.4 database, checks PHP syntax, and runs workflow, architecture, relationship and post-commit event tests. See [Project Guide](docs/PROJECT_GUIDE.md) for schema, route, validation and editing details.


## Resident journeys and commerce

The schema also includes standalone emergency requests and their translated type/status lookups, facility reports, visitor passes, announcements, community posts/comments/reactions, initiatives, resident notifications, booking media, additional work, ratings and warranties. Emergency requests are deliberately separate from scheduled service bookings.

Providers are created from the admin provider screen with an account, approval state, and a contract. Contracts store separate service and emergency commission percentages. Admin order and dispatch screens assign providers against an active contract; completion records a commission entry with the contract and percentage snapshot plus the gross amount used for calculation.

Admin catalog screens create services with Arabic, English and Urdu labels and create customer packages with billing cycle, term, price and a per-service included-use count (or unlimited usage). Package/service names and descriptions are stored in locale translation tables. Resident package endpoints expose the translated catalog and allow an authenticated resident to request a subscription for a home they own. Paid subscriptions remain pending until payment processing is connected; free subscriptions activate immediately.

Additional API routes include resident profile updates, association account/dues, facility reports, visits, announcements, community posts/comments/reactions, initiatives, notifications, service ratings, package catalog/subscriptions, and emergency request create/list/cancel. Admin pages include emergency dispatch, service order assignment, provider contracts, catalog and resident content operations.
