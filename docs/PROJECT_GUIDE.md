# Rounder API & Admin Guide

## Module boundaries

Domain routes, FormRequests, controllers, services, models and enums live under `app/Modules/{Domain}`. Services exchange plain data and model identities; HTTP responses are built only by controllers. See [Modular Monolith guide](MODULAR_MONOLITH_AR.md).

## Account model
A single `users` table serves both mobile and dashboard identities. Every account has a `role_id` foreign key to `roles`; seeded roles are `admin`, `resident` and `provider`. Passwords are passed to Laravel's `hashed` cast, so plaintext is not stored or displayed.

- Mobile resident signup/login uses Sanctum personal access tokens.
- Admin dashboard uses a session login and the `admin` middleware.
- Admin user management can create accounts and reset passwords; resetting revokes all active app tokens for that user.
- Initial admin is only seeded when `ROUND_ADMIN_EMAIL` and `ROUND_ADMIN_PASSWORD` are set in the local environment.

## Core database links
- `users` → `roles`; `role_translations` → `roles`
- `resident_homes` joins `users` and `homes` with a unit label and primary-home flag.
- `homes` → `cities`; `city_translations` → `cities`
- `services` → `service_categories`; localized names/descriptions are in translation tables.
- `bookings` link resident, home, service, optional provider, and a booking-status ID.
- `booking_status_history` records each status ID change and optional actor.
- `payments` link bookings and payment-status IDs.
- `personal_access_tokens` stores hashed Sanctum tokens for app sessions.

Lookup data is stored by numeric ID with stable codes and localized labels. Human-entered notes remain text because they are user content, not lookup values.

## Resident API response
`GET /api/v1/resident/dashboard` returns resident name, selected home/unit, association balance, property health, upcoming service and latest booking activity. Other working app endpoints are `POST /auth/register`, `POST /auth/login`, `GET /resident/homes`, `POST /resident/homes/link`, `GET /resident/services`, `GET|POST /resident/bookings` and `POST /resident/bookings/{id}/cancel`. Text labels follow the request `Accept-Language` (ar/en/ur).

## Validation
Write endpoints validate name length, unique email/phone, E.164 phone format, locale allowlists, strong confirmed password, ownership of the requested home, active service IDs, date order, and bounded notes. Laravel returns structured 422 validation errors. Authentication, booking and home-link routes are throttled. The admin middleware restricts dashboard access by role; a separate resident middleware gates app-only endpoints.

## Dashboard KPI source
Residents, approved providers, all/pending bookings, and paid revenue are aggregate queries over the MySQL tables. Empty database records correctly produce zero values; there are no seeded fake totals.

## Admin page map

- `/admin/login`: session-based dashboard login.
- `/admin/dashboard`: MySQL-derived totals for residents, approved providers, bookings, pending bookings and paid revenue.
- `/admin/users`: filter accounts by role/search; create accounts and reset passwords (password hashes are never displayed).
- `/admin/homes`: create properties/codes that residents use to link their units.
- `/admin/about`: bilingual product introduction.

Arabic is the default locale and uses RTL Bootstrap. English uses LTR Bootstrap.

## First administrator and local run

Copy `.env.example` to `.env`, add MySQL credentials and set `ROUND_ADMIN_EMAIL` plus a strong `ROUND_ADMIN_PASSWORD`. Then run `composer install`, `php artisan key:generate`, `php artisan migrate --seed`, and `php artisan serve`. The seeder does not create an admin account unless both environment credentials are present.

GitHub Actions validates Composer, boots the route table, migrates/seeds an isolated MySQL 8.4 database, and runs PHPUnit workflow, architecture, ORM relation and post-commit event coverage.

## Figma follow-up

The current mobile-backed slice covers authentication, home linking, the live dashboard, service catalog, booking and cancellation. Payment/invoice, community/events, rewards, full profile/preferences, notifications, provider journeys, review/aftercare and remaining Resident App frames still need feature APIs and screens. Keep those as explicit pending flows; never substitute hardcoded KPI or service records.


## Resident, provider and plan operations

- Emergency requests use their own `emergency_types`, `emergency_request_statuses`, `emergency_requests`, and status-history tables. The API validates the resident-home link, emergency type ID, callback phone, location, description, explicit confirmation and request rate limit. The request never creates a regular booking.
- Admin assigns only approved providers with a current active contract. When a request is resolved, the final gross amount is multiplied by the contract's emergency commission percentage; the commission record keeps a snapshot of the percentage and contract.
- Standard bookings use `booking_provider_assignments` to pin the provider contract at assignment. Completion calculates commission using the contract's service percentage and final gross amount.
- Providers are registered in the admin area with an account, provider profile, approval state and dated contract. Passwords use Laravel's hashed cast and are never displayed after creation.
- Service/package text is stored in AR/EN/UR translation rows. Plans use numeric billing-cycle IDs and a `service_package_services` pivot with included quantity or unlimited flag. Subscriptions snapshot price and included service entitlements so later catalog edits do not rewrite existing customer terms.
- Paid plan subscriptions are stored pending and signal `payment_required`; payment gateway integration is intentionally not connected yet.
