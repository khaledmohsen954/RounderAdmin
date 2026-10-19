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

## Full pages, editing and deletion
- Real estate is now `/admin/realestate`, with full add page `/admin/realestate/add`. Existing MySQL `homes` IDs stay stable.
- `home_units` stores each apartment's number, floor, area in square metres and room count. `resident_homes.home_unit_id` links residents to physical units; existing matching unit labels are linked when the property is saved. Unknown measurements are not invented for old buildings.
- Property form saves the building and apartments atomically; deleting occupied apartments or referenced buildings is rejected.
- Catalog pages: `/admin/services`, `/admin/packages`, `/admin/package-features`, `/admin/subscriptions`. Create forms have their own pages; service and package editing URLs are retained.
- Administrators, companies, properties, services, packages, comparison definitions, announcements, initiatives, service orders and emergencies have protected DELETE routes. The shared dialog requires Yes/No confirmation. Financial/assigned requests and referenced records cannot be deleted.
- Provider contracts can be edited with the HTML editor. Historical accrued commission rows remain unchanged. Approved draft contracts become active on edit.
- Sidebar is shared, permission-filtered and grouped into submenus. Desktop sidebar follows the document direction; mobile uses Bootstrap offcanvas. Tajawal and Font Awesome styles are loaded via CDNs, alongside the existing Bootstrap CDN.
- Dashboard uses 1/2/4 cards per row, real database metrics, and only shows metrics for sections the administrator can view.
