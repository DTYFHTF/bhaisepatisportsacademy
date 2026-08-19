# BSA Ops — Overview

Operations & membership platform for Bhaisepati Sports Academy. A standalone
Laravel 12 + Filament 3 application, deliberately separate from the public
website (`bsa-api` / `bsa-web`): different audience (staff, not visitors),
different risk profile (money and door access, not content), different release
cadence.

## What it does

- **Membership** — full member profiles (Nepal-format addresses, guardians,
  emergency contacts, government IDs, medical notes) with unique member codes
  (`BSA-00001`), status lifecycle, and soft deletes so financial history
  survives.
- **Plans & subscriptions** — time-based terms and session packs, per-department
  coverage (gym / pool / sauna / badminton / futsal), freeze with allowance,
  renewal chains, age gating, off-peak windows.
- **Billing** — VAT-aware invoices (inclusive or exclusive), fiscal-year
  numbered sequences, payments across cash / eSewa / Khalti / bank / cheque /
  card with verification workflow, refunds, voids, dues aging.
- **Access control** — the eligibility engine answers "may member X enter
  department Y right now?" for the front-desk kiosk, the admin panel, and door
  hardware via the device API. Credentials stored as hashes only.
- **Reporting** — dashboard KPIs, revenue trend, dues aging, department P&L
  with overhead allocation, member demographics; all reports CSV-exportable.
- **Operations** — expense tracking by category and cost center for the P&L.

## Stack

| Layer | Choice |
|---|---|
| Framework | Laravel 12 (PHP 8.2+) |
| Admin | Filament 3.3 (pinned — v4 is breaking) |
| Auth | Session (staff panel), Sanctum tokens (devices) |
| Audit | spatie/laravel-activitylog on money-touching models |
| DB | MySQL (prod), sqlite (dev/tests) |
| Queue/cache/session | database driver |

## Layout

```
app/
  Enums/           21 string-backed enums (Filament HasLabel/HasColor)
  Models/          19 models, uuid PKs, integer-paisa money
  Services/        EligibilityService, BillingService, SubscriptionService,
                   CheckInService, NumberSequenceService
  Support/         FiscalYear, EligibilityResult DTO, Money helpers
  Filament/        Resources, kiosk page, report pages, dashboard widgets
  Policies/        Role matrix: front_desk < accountant < manager < super_admin
  Http/Controllers/Api/DeviceAccessController — door hardware endpoints
  Console/Commands/ four nightly ops:* commands
docs/              this documentation set
```

## Development

```bash
composer install && cp .env.example .env && php artisan key:generate
php artisan migrate:fresh --seed   # rich demo dataset
php artisan serve --port=8090      # /admin
php artisan test                   # 51 tests
```

Seeded staff logins (password `TermsofService1!2@`): `admin@bsa.com`
(super admin), `manager@bsa.com`, `accounts@bsa.com`, `desk1@bsa.com`,
`desk2@bsa.com`.
