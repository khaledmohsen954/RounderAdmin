# Rounder Admin and API

Laravel 13 application providing the Rounder REST API and responsive administration dashboard.

## Product scope

- API consumed by the Flutter application in [Rounderapplication](https://github.com/khaledmohsen954/Rounderapplication).
- MySQL schema with normalized reference data, foreign keys, indexes, and explicit relationships.
- Bootstrap administration UI in Arabic (default) and English with RTL/LTR layout.
- Role and permission protected administration.
- Dashboard KPIs and charts are computed from persisted application data.

## Backend conventions

- Form Requests validate every write field.
- API Resources define stable response shapes.
- Policies authorize record access.
- Reference and status values use related tables and IDs; free text is reserved for genuine notes/descriptions.
- Database credentials, third-party secrets, and environment-specific values are supplied via `.env`, never committed.

## Product modules

Residence and membership, authentication, service catalog, bookings, providers, order lifecycle, payments, association operations, rewards, notifications, ratings, and aftercare are modeled from the Resident App design.
