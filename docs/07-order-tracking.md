# 07 — Order Tracking

## Design Philosophy

Customers should be able to track their order **without logging in, without remembering a password, without friction**. All they need is the link from their SMS — or their phone number and order ID.

---

## Order ID Format

```
DH-YYMM-XXXX
```

- `DH` — Bhaisepati Sports Academy prefix
- `YYMM` — Year + Month (e.g., `2501` = January 2025)
- `XXXX` — 4-digit sequential counter, resets monthly

Examples:
- `DH-2501-0001` — First order of January 2025
- `DH-2501-4821` — A mid-month order

**Customer-facing:** Always written in full, monospace font.  
**Internal database:** UUID primary key. `orderId` field is indexed separately.

---

## Access Methods

### Method 1: SMS Link (Primary)

When an order is placed, the customer receives:
```
Your BSA order DH-2501-4821 is confirmed.
Track it: bsa.example.com/t/[8-char-token]
```

The token maps to a signed JWT in the database. Clicking the link:
1. Looks up token in `TrackingToken` table
2. Validates: not expired (30 days), not revoked
3. Redirects to `/track?jwt=[full-jwt]`
4. Page loads with full order details — no OTP required

### Method 2: Manual Lookup (Fallback)

```
/track

┌────────────────────────────────┐
│  Track Your Order              │
├────────────────────────────────┤
│  Phone Number                  │
│  [ 98XXXXXXXX               ]  │
│                                │
│  Order ID (optional)           │
│  [ DH-2501-XXXX             ]  │
│  Leave blank to see all orders │
│                                │
│  [ SEND OTP ]                  │
└────────────────────────────────┘
```

After OTP verification, shows all orders linked to the phone number. If order ID is provided, goes directly to that order.

---

## Order Status Timeline

```
┌─────────────────────────────────────────────┐
│  Order DH-2501-4821                         │
│  Field Jacket Olive M + Essential Tee Sand M │
├─────────────────────────────────────────────┤
│                                             │
│  ●  Order Placed         22 Jan · 3:14 PM   │  ← filled dot = done
│  │                                          │
│  ●  Payment Confirmed    22 Jan · 3:15 PM   │
│  │                                          │
│  ●  Packed               23 Jan · 10:30 AM  │
│  │                                          │
│  ●  Dispatched           23 Jan · 2:00 PM   │
│  │  With Blue Dart Express                  │
│  │                                          │
│  ○  Out for Delivery                        │  ← empty = upcoming
│  │                                          │
│  ○  Delivered                               │
│                                             │
│  Estimated delivery: 24 Jan                 │
│                                             │
│  [ Exchange or Return ]                     │  ← shown for 7 days post-delivery
└─────────────────────────────────────────────┘
```

### Order Status Enum

| Status | Customer-facing label | Trigger |
|---|---|---|
| `PENDING_PAYMENT` | "Payment Pending" | Order created, awaiting payment |
| `PAYMENT_CONFIRMED` | "Payment Confirmed" | Payment verified |
| `CONFIRMED` | "Order Confirmed" | COD order placed |
| `PACKED` | "Packed" | Admin marks packed |
| `DISPATCHED` | "Dispatched" | Admin adds tracking info |
| `OUT_FOR_DELIVERY` | "Out for Delivery" | Courier API webhook (future) |
| `DELIVERED` | "Delivered" | Admin marks delivered |
| `CANCELLED` | "Cancelled" | Admin or system cancel |
| `EXCHANGE_REQUESTED` | "Exchange Requested" | Customer requests exchange |
| `RETURNED` | "Returned" | Return processed |

---

## Tracking Token Architecture

### Short Token (`/t/[token]`)

8-character alphanumeric URL ID. Stored in `TrackingToken` table:

```
TrackingToken {
  token     String  @id  (8 chars, e.g., "k2mPzR4x")
  orderId   UUID    FK
  createdAt DateTime
  expiresAt DateTime  (30 days from creation)
}
```

Short-token resolves to: redirect to `/track?jwt=[signed-jwt]`

### JWT Payload

```json
{
  "sub": "DH-2501-4821",
  "phone_hash": "sha256(phone + secret)",
  "iat": 1706000000,
  "exp": 1708592000,
  "scope": "track"
}
```

- `sub`: Order ID (human-readable)
- `phone_hash`: prevents token sharing from exposing phone numbers
- Signed with `TRACKING_JWT_SECRET` (env var)
- Expiry: 30 days

---

## Order Items Summary

Shown on the tracking page:

```
┌─────────────────────────────────────────────┐
│  ITEMS IN THIS ORDER                        │
├─────────────────────────────────────────────┤
│  [img]  Field Jacket · Olive · M   NPR 5,500 │
│  [img]  Essential Tee · Sand · M   NPR 2,200 │
│  ──────────────────────────────────────────  │
│  Subtotal                          NPR 7,700 │
│  Delivery                          Free      │
│  Total                             NPR 7,700 │
│  Payment                           Khalti ✓  │
│                                             │
│  Deliver to: Aashish Sharma                 │
│              Ward 4, Lazimpat, Kathmandu    │
└─────────────────────────────────────────────┘
```

---

## Exchange & Return Flow

Available for 7 days after `DELIVERED` status.

```
[ Exchange or Return ] button →

┌────────────────────────────────┐
│  Exchange or Return            │
├────────────────────────────────┤
│  Reason                        │
│  ○ Wrong size                  │
│  ○ Defective or damaged        │
│  ○ Changed my mind             │
│  ○ Other                       │
│                                │
│  [For size exchange only]      │
│  Current: M  → New size: [  ▼] │
│                                │
│  Pickup at same address? [Yes] │
│  Or different address: [     ] │
│                                │
│  [ SUBMIT REQUEST ]            │
└────────────────────────────────┘
```

On submit:
- `exchangeRequested: true` set on order
- Admin SMS alert sent
- Customer SMS: "Your exchange request for DH-2501-4821 is received. We'll contact you within 24 hours."

**Return policy (communicated on page):**
- Must be within 7 days of delivery
- Item must be unworn with original tags
- Exchange: new size dispatched after collection
- Refund: via Khalti / eSewa only (no COD reversal)

---

## SMS Notifications Reference

| Event | Message |
|---|---|
| Order confirmed (COD) | `Your BSA order DH-XXXX-YYYY is confirmed. Track: bsa.example.com/t/[token]` |
| Order confirmed (Paid) | `Payment received. Your order DH-XXXX-YYYY is confirmed. Track: bsa.example.com/t/[token]` |
| Packed | `Your order DH-XXXX-YYYY has been packed and will ship tomorrow.` |
| Dispatched | `Your order DH-XXXX-YYYY is on its way. Estimated: [date]. Track: bsa.example.com/t/[token]` |
| Delivered | `Your BSA order DH-XXXX-YYYY has been delivered. Need help? wa.me/977[number]` |
| Cancelled | `Your order DH-XXXX-YYYY has been cancelled. Refund (if applicable) in 3–5 days.` |
| Restock alert | `Your size M Field Jacket is back: bsa.example.com/p/field-jacket-olive` |

All SMS sent via **Sparrow SMS** (Nepal), using the Bulk SMS API.

---

## Privacy

- Phone numbers are **never displayed** on the tracking page, only referenced as "confirmed to ...98XX"
- Order address is shown in partial form: "Ward 4, Lazimpat [...]" — not the full street
- JWT tracking token is scoped to `track` scope only — cannot be used for any write operations
- All tracking API endpoints are rate-limited: 30 req/min per IP
