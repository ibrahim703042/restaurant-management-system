# Audit log & sessions

## Audit

- After each **POST / PUT / PATCH / DELETE** while logged in, a row is written to `audit_logs` (user, route, path, method, HTTP status, non-sensitive payload summary).
- **Skipped:** `GET …/list` JSON endpoints (UI loading only), viewing the audit page itself, Telescope/HORIZON paths.
- **Admin → Audit log** requires permission `admin.audit.view` (super_admin / owner after seeder).

Run after deploy:

```bash
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
```

## Sessions

- **Idle timeout:** `SESSION_LIFETIME` (minutes) in `.env`. Laravel refreshes the timer on every request.
- **Production:** set `SESSION_SECURE_COOKIE=true` (HTTPS only) and keep `SESSION_SAME_SITE=lax` unless you need cross-site cookies.
- **Database sessions** (optional): `SESSION_DRIVER=database` + `php artisan session:table` + migrate — allows invalidating sessions server-side.

Login already regenerates the session ID after successful authentication (Laravel default).
