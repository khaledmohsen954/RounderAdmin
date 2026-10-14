# Rounder API & Admin Guide

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

Lookup data is stored by numeric ID with stable codes and localized labels. Human-entered notes remain text because they are user content, not lookup values.

## Resident API response
`GET /api/v1/resident/dashboard` returns resident name, selected home/unit, association balance, property health, upcoming service and latest booking activity. Text labels are resolved for the request locale; request language is controlled by `Accept-Language` unless the account has a saved locale.

## Validation
Write endpoints validate name length, unique email/phone, E.164 phone format, locale allowlists, strong confirmed password, ownership of the requested home, active service IDs, date order, and bounded notes. Laravel returns structured 422 validation errors. Authentication routes are throttled.

## Dashboard KPI source
Residents, approved providers, all/pending bookings, and paid revenue are aggregate queries over the MySQL tables. Empty database records correctly produce zero values; there are no seeded fake totals.
