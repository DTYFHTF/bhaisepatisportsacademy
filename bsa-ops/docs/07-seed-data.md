# Seed data

`php artisan migrate:fresh --seed` builds a coherent demo dataset. The
billing history is generated **through the real services**
(`SubscriptionService`, `BillingService`) under `Carbon::setTestNow()`, so
invoices, receipts, sequences, and renewal chains are internally consistent —
not random rows.

## What you get

- **Settings** — org profile, VAT 13%, FY 2082-83, dues grace 7 days,
  block threshold NPR 2,000.
- **5 departments** — Gym, Pool, Sauna, Badminton, Futsal with cost centers.
- **5 staff** — one per role (+2nd front desk). Password `TermsofService1!2@`.
- **16 plans** — gym 1/3/6/12-month, student off-peak, pool monthly + packs,
  sauna pack, badminton, futsal, all-access combos. Realistic NPR pricing.
- **4 discounts** — STUDENT10, CORP15, FAMILY500, NEWYEAR2082.
- **60 members** — deterministic (seeded RNG): Newar/Nepali names, Lalitpur
  addresses, mixed demographics, minors with guardians, one blacklisted.
- **~170 invoices / ~150 payments** over up to 14 months of renewal chains:
  ~82% paid in full, partials and unpaid for dues aging, cash-heavy method
  mix with eSewa/Khalti txn ids, 2 bounced cheques, pending verifications.
- **~1,500 check-ins** over 90 days, weekday-morning weighted, a few denials.
- **98 expenses** over 8 months — overhead (rent, wages, utilities) plus
  per-department costs, scaled to the 60-member cohort so P&L margins read
  sensibly.
- **3 devices + 40 RFID credentials.** Raw card identifiers follow
  `BSA-CARD-{member_code}` (e.g. `BSA-CARD-BSA-00008`) so you can exercise
  the verify API; only hashes are stored.

## Trying the device API locally

```bash
php artisan tinker --execute="
  \$d = App\Models\AccessDevice::where('device_uid','BSA-DOOR-GYM-01')->first();
  echo \$d->createToken('device', ['device:verify'])->plainTextToken;"

curl -X POST http://localhost:8090/api/v1/device/verify \
  -H "Authorization: Bearer <token>" -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"credential_type":"rfid_card","identifier":"BSA-CARD-BSA-00008"}'
```

Reseeding truncates sessions (database session driver) — you'll be logged out
of `/admin`.
