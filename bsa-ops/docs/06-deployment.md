# Deployment

Targets the same cPanel-managed VPS as `bsa-api`, as its own application with
its own database and subdomain (e.g. `ops.bhaisepatisportacademy.com.np` →
`bsa-ops/public`).

## Environment

```dotenv
APP_NAME="BSA Ops"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ops.example.com.np

DB_CONNECTION=mysql
DB_DATABASE=bsa_ops          # SEPARATE database from the website
DB_USERNAME=…
DB_PASSWORD=…

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

## First deploy

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=SettingsSeeder     # org profile defaults
php artisan db:seed --class=DepartmentSeeder
php artisan db:seed --class=ExpenseCategorySeeder
php artisan db:seed --class=UserSeeder         # then CHANGE THE PASSWORDS
php artisan filament:assets
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan storage:link
```

Do **not** run the full `DatabaseSeeder` in production — it creates 60 demo
members with invoices and payments.

## Cron (required)

The nightly jobs (expiry, freeze release, overdue marking, reminders) run via
the scheduler:

```
* * * * * cd /home/USER/bhaisepatisportacademy/bsa-ops && php artisan schedule:run >> /dev/null 2>&1
```

Queue worker (database notifications): either
`* * * * * php artisan queue:work --stop-when-empty` or a supervisor entry.

## CI/CD (follow-up)

Mirror `.github/workflows/deploy-backend.yml` as `deploy-ops.yml` with path
filter `bsa-ops/**`: test job on sqlite `:memory:` (`php artisan test`), then
rsync over SSH excluding `.env`, `storage/`, sqlite files; post-deploy
`migrate --force` + cache rebuild. Not yet created — deliberate follow-up so
the first prod deploy is manual and observed.

## Security notes

- `/admin` is session-authenticated; every inactive staff user is locked out
  by `canAccessPanel`. Role policies gate money actions server-side.
- Device API tokens carry only the `device:verify` ability; rotate from the
  Devices screen (regenerating revokes old tokens).
- Fiscal-year roll each Shrawan: Settings → Organisation settings — invoice /
  receipt / voucher sequences restart under the new label.
