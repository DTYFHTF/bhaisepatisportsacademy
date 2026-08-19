# BSA Ops

Operations & membership platform for **Bhaisepati Sports Academy** —
member management, subscriptions & billing, front-desk check-in, door-access
integration, inventory & POS (kitchen + pro shop), a member self-service API,
and managerial reporting. Laravel 12 + Filament 3, standalone from the public
website (`bsa-api` / `bsa-web`).

```bash
composer install && cp .env.example .env && php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve --port=8090        # → http://localhost:8090/admin
php artisan test
```

Demo login: `admin@bsa.com` / `TermsofService1!2@` (see [docs/07-seed-data.md](docs/07-seed-data.md)).

## Documentation

| Doc | Contents |
|---|---|
| [00-overview](docs/00-overview.md) | What it does, stack, layout |
| [01-domain-model](docs/01-domain-model.md) | ERD + table dictionary |
| [02-billing-and-fiscal-year](docs/02-billing-and-fiscal-year.md) | Money, VAT, payment lifecycle, sequences, freeze rules |
| [03-eligibility-and-access](docs/03-eligibility-and-access.md) | The eligibility engine + kiosk |
| [04-device-api](docs/04-device-api.md) | Door-hardware API (verify / heartbeat / events) |
| [05-admin-guide](docs/05-admin-guide.md) | Per-role staff workflows |
| [06-deployment](docs/06-deployment.md) | cPanel deploy, cron, env |
| [07-seed-data](docs/07-seed-data.md) | Demo dataset + trying the device API |
| [08-inventory-and-pos](docs/08-inventory-and-pos.md) | Stock ledger, POS, kitchen member pricing |
| [09-member-portal-api](docs/09-member-portal-api.md) | Member self-service API (GymMaster-style) |
