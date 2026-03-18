# 06 — Checkout Flow

## Design Philosophy

- **No account required** — ever.
- **Phone number is the identity** — used for OTP and order tracking.
- **3 steps maximum** — Contact, Delivery, Payment.
- **Progress is never lost** — back button works; filled fields remain.
- **Payment failure never clears the cart.**

---

## Step Overview

```
[Step 1: Contact & OTP]  →  [Step 2: Delivery]  →  [Step 3: Payment]
        ●                          ○                       ○
```

Progress indicator is minimal: three dots or numerals. No labels until active.

---

## Step 1: Contact & OTP

```
┌────────────────────────────────┐
│ Checkout                  1/3  │
├────────────────────────────────┤
│                                │
│  Phone Number                  │
│  [  98XXXXXXXX              ]  │
│  We'll send an OTP here        │
│                                │
│  [ SEND OTP ]                  │
│                                │
│  ──────────  or  ──────────    │
│                                │
│  [ Continue as guest » ]       │  ← skip OTP (no tracking later)
│                                │
└────────────────────────────────┘
```

After "Send OTP":

```
┌────────────────────────────────┐
│  OTP sent to 98XXXXXXXX  ✓    │
│  Change  ·  Resend in 45s      │
│                                │
│  [ _ ][ _ ][ _ ][ _ ][ _ ][ _ ] │
│                                │
└────────────────────────────────┘
```

**OTP behavior:**
- 6-digit code via Sparrow SMS
- Expires: 10 minutes
- Resend available after 60 seconds
- Max 3 resends per phone per session
- On successful OTP: auto-advance to Step 2 (no button press needed)
- On "Continue as guest": skip OTP, advance to Step 2, no tracking link will be sent

**Security:**
- OTP tied to session + phone number (no replay across sessions)
- Rate limited: 5 OTP requests per phone per hour
- Phone number stored hashed in OtpCode table

---

## Step 2: Delivery

```
┌────────────────────────────────┐
│ Checkout                  2/3  │
├────────────────────────────────┤
│                                │
│  Full Name                     │
│  [                          ]  │
│                                │
│  Address                       │
│  [                          ]  │
│  e.g., Ward 4, Lazimpat        │
│                                │
│  City                          │
│  [ Kathmandu               ▼ ] │  ← Dropdown: Nepal districts
│                                │
│  Delivery Note (optional)      │
│  [                          ]  │
│  e.g., call when arriving      │
│                                │
│  Delivery: NPR 100 · 1–2 days  │  ← Auto-calculated based on city
│                                │
│  [ CONTINUE TO PAYMENT ]       │
│                                │
└────────────────────────────────┘
```

**Delivery cost rules:**
- Kathmandu, Lalitpur, Bhaktapur: NPR 100 · 1–2 days
- Other cities: NPR 150 · 3–5 days
- Above NPR 7,000 subtotal: free delivery

City dropdown: All 77 districts of Nepal (searchable / filterable).

---

## Step 3: Payment

```
┌────────────────────────────────┐
│ Checkout                  3/3  │
├────────────────────────────────┤
│ Order Summary                  │
│  Field Jacket · Olive · M      │
│  NPR 5,500                     │
│  Essential Tee · Sand · M      │
│  NPR 2,200                     │
│  ─────────────────────────     │
│  Subtotal       NPR 7,700      │
│  Delivery       Free           │
│  Total          NPR 7,700      │
├────────────────────────────────┤
│                                │
│  Payment Method                │
│                                │
│  ○  Khalti                     │
│  ○  eSewa                      │
│  ●  Cash on Delivery           │  ← Default selection
│                                │
│  [ PLACE ORDER ]               │
│                                │
└────────────────────────────────┘
```

---

## Payment Method Flows

### Khalti

```
[Place Order] clicked
   ↓
POST /api/checkout/initiate → creates Order (status: PENDING_PAYMENT)
   ↓
Khalti widget opens (iframe or redirect)
   ↓
User completes payment in Khalti
   ↓
Khalti callback → POST /api/checkout/khalti/callback
   ↓
Verify payment with Khalti API (server-to-server)
   ↓
If verified:
  → Update Order status: PAYMENT_CONFIRMED
  → Deduct stock
  → Send SMS confirmation
  → Redirect to /order/confirmed?id=[orderId]
Else:
  → Show "Payment failed" on Step 3
  → Cart remains unchanged
```

### eSewa

```
Same flow as Khalti but using eSewa's form-post API
POST /api/checkout/esewa/callback
Same verification and confirmation steps
```

### Cash on Delivery

```
[Place Order] clicked
   ↓
POST /api/checkout/place
   ↓
Order created: status = CONFIRMED (no payment step)
   ↓
Deduct stock immediately
   ↓
Send SMS confirmation with order ID
   ↓
Redirect to /order/confirmed?id=[orderId]
```

**COD limit:** NPR 15,000 maximum per order (admin-configurable). Above this, only digital payment.

---

## Order Confirmation Page

```
┌────────────────────────────────┐
│                                │
│  ✓                             │
│                                │
│  Order Placed                  │
│                                │
│  DH-2501-4821                  │  ← Order ID (monospace)
│                                │
│  We're packing your order      │
│  A confirmation has been sent  │
│  to 98XXXXXXXX                 │
│                                │
│  [ Track Your Order ]          │  ← /track?token=[jwt]
│  [ Continue Shopping ]         │  ← /shop
│                                │
│  ─────────────────────         │
│                                │
│  Share the look                │
│  [ ↗ Share on Instagram ]      │
│  [ ↗ Copy link ]               │
│                                │
└────────────────────────────────┘
```

---

## SMS Templates

**On order placed (COD):**
```
Your BSA order DH-2501-4821 is confirmed.
Track it: bsa.example.com/t/[short-token]
```

**On order placed (digital payment):**
```
Payment received. Your BSA order DH-2501-4821 is confirmed.
Track it: bsa.example.com/t/[short-token]
```

**On dispatch:**
```
Your order DH-2501-4821 is on its way.
Expected: 22 Jan · Track: bsa.example.com/t/[short-token]
```

**On delivery:**
```
Your BSA order DH-2501-4821 has been delivered.
Questions? wa.me/977XXXXXXXXXX
```

Short tokens: 8-character alphanumeric, stored in DB, maps to full order JWT.

---

## Edge Cases

| Scenario | Handling |
|---|---|
| Stock runs out between "Add to cart" and "Place Order" | Error on Step 3: "Item XYZ in size M is no longer available. It has been removed from your cart." |
| User navigates back from Step 2 | Fields remain filled; no data lost |
| User navigates back from Step 3 | Returns to Step 2 with filled delivery; payment method resets |
| OTP session expires (10 min) | Return to Step 1, show "OTP expired, please try again" |
| Network failure during order creation | Show "Something went wrong. Your cart is safe. Please try again." |
| Duplicate order attempt (double-tap Place Order) | Server deduplication via idempotency key (generated at Step 1 entry) |

---

## Inventory Reservation

When a user enters checkout, selected items are soft-reserved for 15 minutes:

```sql
UPDATE variants SET reservedStock = reservedStock + 1 WHERE id = $variantId
```

If checkout is abandoned after 15 minutes, reservation is released via a background job (or request-time cleanup check).

This prevents overselling during high-traffic drops.
