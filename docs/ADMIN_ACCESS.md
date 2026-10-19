# Separate administrator authentication and permissions

- Browser administration authenticates against `admins`; resident/provider accounts remain in `users`. No administrator token API is exposed.
- Existing administrator accounts are copied with their IDs and password hashes; their old users rows remain solely to preserve historical foreign keys. Old credentials and API tokens on those legacy rows are revoked. Database rollback does not restore those old passwords.
- New audit entries use explicit `admin_*_by` foreign keys, so equal numeric IDs in the two tables cannot be confused.
- New admin users are created on a full page and assigned a `permission_set_id`. Super administrators are bootstrapped from deployment configuration; HTTP input cannot grant that flag.
- `/admin/permissions` manages Arabic/English named permission groups with view/create/update/delete checkboxes per dashboard section. The backend checks every route and rejects unknown sections by default.
- Non-super administrators cannot grant privileges they do not hold or modify their own permission group. Protected accounts cannot be changed/deleted through the administrative user form.
- Apply migrations with `php artisan migrate --force`. Never use migrate:fresh on a populated deployment.

## Unified sections
Permission checks cover dashboard, about, administrators, providers, realestate, services, packages, comparison features, subscriptions, resident operations, service orders, emergencies and permission groups. Middleware and Blade menus use the same resolver. Default post-login navigation chooses a section the account can view. Mobile API authentication is bearer-token-only and cannot reuse an administrator browser session.
