# Provider and service forms

- `GET /admin/providers`: list only, Add provider and Edit company links.
- `GET /admin/providers/create`, `POST /admin/providers`: account, company and initial contract creation in one transaction.
- `GET /admin/providers/{provider}/edit`, `PUT /admin/providers/{provider}`: company/contact/language/approval updates; passwords, roles and financial contract history are not editable through this form.
- Contract terms use an HTML editor with headings, emphasis and lists. Plain textarea fallback works without JavaScript. The server rebuilds HTML from an allowlist, strips all attributes and active content before MySQL persistence.
- `GET /admin/services/create`, `POST /admin/packages/services`: category ID, code, three translations, image, pricing, discount and activation. Existing edit URL and API contract are retained.
- `GET /api/v1/resident/services` (resident Sanctum token): active services from the same records, including service/category IDs, localized text, image URL and effective price. No mobile API wiring is changed.
- Deploy the existing Laravel app normally; public image delivery requires `php artisan storage:link`.
- Tests: ProviderFormsTest and ServiceCreationIntegrationTest use the MySQL CI database; existing workflow, architecture and pricing tests also run.

## Saudi cities
Run `php artisan migrate --force`, then `php artisan db:seed --class=SaudiCitiesSeeder --force` on an existing deployment. Do not use migrate:fresh on production. Fresh databases seed the same dataset through DatabaseSeeder. Existing Cairo-linked homes keep their IDs and location; Cairo is excluded from new property choices. `/api/v1/resident/cities?search=...&page=1&per_page=50` returns local IDs with paging (maximum 100). Homes reference cities through existing foreign keys; other property-related records derive the city through their home relationship instead of duplicating it.
