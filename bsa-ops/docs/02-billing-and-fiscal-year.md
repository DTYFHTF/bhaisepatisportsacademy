# Billing & fiscal year

## Money

Integer paisa end-to-end. Rounding happens **once per invoice line**, never on
running totals, so header totals always equal the sum of lines
(`BillingMathTest` asserts this invariant).

## Tax (VAT 13%, configurable in Settings)

Per plan, two switches:

- `is_taxable` — VAT applies at all.
- `price_includes_tax` — how it's computed:
  - **Inclusive** (default; how gyms quote prices in Nepal): the charged gross
    stays as-is; VAT is backed out: `net = gross × 10000 / (10000 + rate_bp)`,
    `tax = gross − net`.
  - **Exclusive**: `tax = base × rate_bp / 10000` added on top.

Discounts apply **pre-tax** to the plan line only (never the admission fee).
Percent discounts are stored in basis points (1000 = 10%); fixed discounts in
paisa, capped at the line amount.

## Payment lifecycle

```
cash, card            → completed  (counts toward paid_total immediately)
esewa, khalti,
bank_transfer, cheque → pending_verification  (does NOT count)
      verify →  completed          bounce → bounced (reverses if counted)
refund (accountant+) → payment_refunds row, invoice balance reopens,
                       payment → refunded once fully refunded
```

Invoice status derives from money: `issued → partially_paid → paid`, with
`overdue` set by the nightly `ops:mark-overdue-invoices` when past due date.
`void` requires zero paid_total (refund first), manager+, with reason —
enforced in `BillingService::voidInvoice`.

Overpayment is rejected at the service layer, not the UI.

## Freeze semantics (business rule — confirm with the academy)

Freezing pauses the clock: subscription status becomes `frozen` (door + kiosk
deny with `subscription_frozen`). On lift — manual or by
`ops:release-freezes` — `ends_on` extends by the days *actually* frozen.
Cumulative frozen days per subscription are capped by the plan's
`freeze_allowance_days`.

## Fiscal year & numbering

Nepali fiscal year label (e.g. `2082-83`) lives in Settings and is **rolled
manually each Shrawan** — no Bikram Sambat calendar math in code. Sequences
are scoped per fiscal year:

- Invoices `INV-2082-83-0001`
- Receipts `RCP-2082-83-0001`
- Vouchers `VCH-2082-83-0001`
- Member codes `BSA-00001` (not year-scoped)

`NumberSequenceService::next()` allocates inside a transaction with
`lockForUpdate`; the unique index on the formatted number is the backstop.

## Dues policy (Settings)

- `dues_grace_days` — also sets invoice due dates (issue + grace).
- `dues_block_threshold` — entry is denied (`outstanding_dues`) only when the
  member's open balance exceeds this **and** the oldest open invoice is past
  due date + grace. Small or fresh dues never lock a member out.
