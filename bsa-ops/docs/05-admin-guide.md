# Admin guide — per-role workflows

Sign in at `/admin`. What you can see and do depends on your role.

## Roles

| | front_desk | accountant | manager | super_admin |
|---|---|---|---|---|
| Members: create/edit, check-in, issue cards | ✓ | ✓ | ✓ | ✓ |
| Record payments | ✓ | ✓ | ✓ | ✓ |
| Verify / bounce / refund payments | | ✓ | ✓ | ✓ |
| Expenses (record) | | ✓ | ✓ | ✓ |
| Void invoices, approve expenses | | | ✓ | ✓ |
| Plans, discounts, reports, org settings | | | ✓ | ✓ |
| Staff users, devices, departments (create) | | | | ✓ |
| Delete members (soft) | | | ✓ | ✓ |

Invoices and payments are **immutable ledger rows** for everyone — money moves
only through the action buttons (Record payment, Verify, Refund, Void), which
run the audited service layer.

## Front desk — daily flow

- **Check-in kiosk** (Access → Check-in kiosk): scan the member's card or
  search phone/code/name → tap the green department chip. Red chips show why
  entry is refused — read the reason to the member (frozen, expired, dues…).
- **New member**: Members → New member. Code is assigned automatically.
  Guardian details are required in practice for minors; collect emergency
  contact at signup.
- **Sell a subscription**: on the member row (or profile) → **Subscribe** →
  pick plan / optional discount / start date. The invoice is raised
  automatically (admission fee only on their first-ever subscription).
- **Take money**: row action **Payment** → amount is prefilled with the
  balance; pick the method. Cheques and wallet transfers go to *pending
  verification* — accounts will confirm them.
- **Issue a card**: member profile → Access credentials → Issue credential →
  scan the card. Only a hash is stored.

## Accountant

- **Payments tab "Pending verification"**: confirm eSewa/Khalti/bank/cheque
  receipts (Verify) or Bounce them — bouncing reopens the invoice balance.
- **Refunds**: from the payment row; reason required; reopens the balance.
- **Expenses**: Operations → Expenses → record with category + department
  (leave department empty for shared overhead — it feeds the P&L allocation).

## Manager

- **Renewals**: dashboard "Expiring within 14 days" table has inline Renew;
  the Subscriptions list has status tabs + Freeze/Cancel actions.
- **Void an invoice**: only when nothing is paid on it; reason is stored and
  audited.
- **Reports** (all CSV-exportable): Revenue by month × method; Dues aging by
  member; Department P&L (direct expenses + overhead allocated pro-rata by
  revenue share); Member demographics.
- **Org settings**: VAT rate, fiscal year roll (each Shrawan!), dues policy,
  receipt footer.

## Super admin

- **Staff users**: Settings → Staff Users. Deactivating a user blocks panel
  login immediately.
- **Devices**: Settings → Devices — see doc 04 for hardware onboarding.

## Notifications

Nightly jobs post database notifications (bell icon): subscriptions expiring
within 7 days go to managers and front desk each morning at 08:00.
