# Inventory, POS & the Kitchen

Three connected pieces: a stock ledger that tracks every single item, a
point-of-sale terminal that sells from it, and the Kitchen / Pro Shop as
revenue-and-cost centers inside the existing department P&L.

## One money ledger — no separate POS books

A POS sale is **an ordinary Invoice** (`source = pos`) in the same table as
membership invoices. That keeps revenue reports, dues aging, and the
department P&L working from a single source. Two columns make it fit:

- `invoices.member_id` is **nullable** — walk-in sales have no member.
- `invoices.source` — `membership` or `pos`.
- `payments.member_id` is nullable for the same reason.

| Sale type | Member | Invoice | Payment |
|---|---|---|---|
| Walk-in cash / wallet | none | `pos`, paid | completed immediately |
| Member cash / wallet | attached | `pos`, paid | completed immediately |
| Member **on account** | required | `pos`, outstanding | none — becomes dues |

POS wallet payments (eSewa/Khalti) complete instantly rather than entering
`pending_verification`: the customer shows the confirmation at the counter.
Membership invoices keep the old behaviour — `BillingService::applyPayment()`
takes an `$instant` flag, and only the POS passes it.

## The stock ledger

`stock_movements` is **append-only** and is the truth. Every row carries a
signed quantity:

| Type | Sign | Written by |
|---|---|---|
| `purchase` | + | receiving a supplier delivery |
| `sale` | − | a POS line for a tracked product |
| `consumption` | − | issuing stock internally (shuttlecocks to a court) |
| `adjustment` | ± | stocktake reconciliation |
| `write_off` | − | damage / loss |

`products.stock_on_hand` is a **cache** of that ledger, maintained inside the
same transaction. The invariant `stock_on_hand == SUM(stock_movements.quantity)`
is asserted by `InventoryTest`.

All outbound movements use a **guarded decrement**
(`WHERE stock_on_hand >= quantity`), the same technique as pack sessions in
`CheckInService` — so a single shuttlecock can never be sold twice by two
simultaneous terminals. A shortfall throws, rolling back the whole sale.

Everything goes through `InventoryService`:

- `receivePurchase($supplier, $lines, …)` — creates the purchase + items,
  `+qty` movements, and refreshes each product's `cost_price`.
- `consume($product, $qty, $department, …)` — internal issue, valued at cost
  and attributed to a department.
- `adjust($product, $countedQty, …)` — writes the signed delta after a count.
- `sellStock(...)` — called by `PosService` inside its transaction.

## Tracked vs untracked products

`track_stock` is off for made-to-order kitchen dishes (a plate of momo isn't
inventory) and on for anything countable — shuttlecocks, grips, t-shirts,
bottled drinks, pool chemicals. Untracked products skip the ledger entirely.

`reorder_level` drives the red stock figure in the Products table and the
**Low stock** dashboard widget.

## The Kitchen: open to all, better for Club members

Every product may carry a `member_price` alongside its walk-in `price`.
`Product::priceFor(?Member)` returns the member price **only for members whose
status is `active`** — lapsed and blacklisted members pay the walk-in rate.
The POS shows both prices on every tile and totals the saving in the cart.

Kitchen and Pro Shop are Departments with `is_access_controlled = false`, so:

- they never appear in the check-in kiosk, the per-member eligibility strip,
  or door-device checks (`Department::accessControlled()` scope), and
- their sales and costs still flow into the Department P&L like any other
  cost center.

## Consumption feeds the P&L

Issuing 3 shuttlecocks to the badminton courts writes a `consumption`
movement valued at `cost_price`. The Department P&L adds
`Σ(|quantity| × unit_cost)` of consumption movements to each department's
**direct expenses**, alongside recorded `expenses` rows. Product sales are
attributed **in full** to the product's department (unlike membership plans,
whose revenue is split across the departments they cover).

## Where things live

| Concern | File |
|---|---|
| Stock mutations | `app/Services/InventoryService.php` |
| Selling | `app/Services/PosService.php` |
| Terminal UI | `app/Filament/Pages/PosTerminal.php` + `resources/views/filament/pages/pos-terminal.blade.php` |
| Catalog & issuing | `app/Filament/Resources/ProductResource.php` |
| Receiving | `app/Filament/Resources/PurchaseResource.php` |
| Audit trail | `app/Filament/Resources/StockMovementResource.php` (read-only) |

Purchases and stock movements are **not editable** — corrections are made by
recording an adjustment, so the ledger stays a true history.
