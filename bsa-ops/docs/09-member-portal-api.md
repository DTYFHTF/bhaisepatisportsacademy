# Member Portal API

Member self-service endpoints — the software side of a future member app or
web portal. Modelled on the [GymMaster Member Portal
API](https://www.gymmaster.com/gymmaster-api/), whose conventions we adopted
deliberately: a versioned `/portal` prefix, token auth with an expiry, a
`{result, error}` envelope on every response, and product purchase billed
straight to the member's account.

Base: `/api/v1/portal`

## Response envelope

Every endpoint — success or failure — returns the same shape:

```json
{ "result": …, "error": null }
```

`result` is an object, an array, or a message string; `error` is a string
when something went wrong (and `result` is then `null`). Money is always
**integer paisa**, consistent with the rest of the system.

## Authentication

`POST /login` with the member's own credentials:

```json
{ "member_code": "BSA-00013", "phone": "9831262590" }
```

```json
{
  "result": "Login successful",
  "token": "1|G2BkDNFrcw4j…",
  "expires": 3600,
  "member_code": "BSA-00013",
  "error": null
}
```

The token is a Sanctum personal access token on the **Member** model carrying
the `portal:member` ability, expiring in one hour (mirroring GymMaster's
1-hour token lifetime). Send it as `Authorization: Bearer <token>`.

Blacklisted members are refused with 403. Login is throttled to 10/min;
authenticated endpoints to 60/min.

> **Planned:** replace code+phone with SMS OTP. `bsa-api` already owns an OTP
> service for the public site; the portal should reuse it rather than
> duplicating one.

## Endpoints

| Method | Path | Purpose | GymMaster analogue |
|---|---|---|---|
| POST | `/login` | Issue a token | `POST /portal/api/v1/login` |
| GET | `/profile` | Member card details | `GET /v1/member/profile` |
| GET | `/memberships` | Subscriptions, dates, sessions left | `GET /v1/member/memberships` |
| GET | `/balance` | Outstanding invoices + total | `GET /v1/member/outstandingbalance` |
| GET | `/history` | Invoice & payment ledger | `GET /v1/member/accounthistory` |
| GET | `/visits` | Monthly check-in counts, last 12 months | `GET /v1/member/visits/monthly` |
| GET | `/products` | Catalog at *this member's* price | `GET /v2/products` |
| POST | `/products` | Buy on account | `POST /v2/products` |

### GET /balance

```json
{
  "result": {
    "outstanding_total": 153000,
    "currency": "NPR (paisa)",
    "invoices": [
      { "invoice_number": "INV-2082-83-0600", "issue_date": "2026-07-20",
        "due_date": "2026-07-27", "total": 100000, "balance": 100000,
        "status": "issued", "source": "pos" }
    ]
  },
  "error": null
}
```

### GET /products

Each item carries both the public `price` and `your_price` — the member price
when the member is active (see [08-inventory-and-pos](08-inventory-and-pos.md)):

```json
{ "id": "019f…", "sku": "KIT-SHAKE", "name": "Banana protein shake",
  "category": "kitchen", "unit": "glass",
  "price": 25000, "your_price": 20000, "in_stock": true }
```

### POST /products

Follows GymMaster's semantics: the member buys, and it is **billed to their
account** rather than taking a card payment.

```json
{ "items": [{ "product_id": "019f…", "quantity": 1 }] }
```

```json
{
  "result": {
    "message": "Purchase placed on your account.",
    "invoice_number": "INV-2082-83-0601",
    "total": 15000,
    "balance": 15000
  },
  "error": null
}
```

It runs through the same `PosService` as the counter terminal, so stock is
decremented, member pricing applies, and the charge appears immediately in
`/balance` and in the member's dues in the admin panel. Out-of-stock or
inactive products are rejected with 422.

## What we did *not* copy from GymMaster

- **Class bookings** (`/booking/classes/schedule`, seats, waitlists) — the
  academy runs open facilities and courts rather than a timetabled class
  programme. The schema has no `classes` concept yet; when group classes are
  introduced, GymMaster's split of *facility-wide schedule* vs *classes this
  member may book* vs *seat availability* is the right shape to follow.
- **Member self-signup and membership purchase** — memberships are sold at
  the desk, where cash and eSewa are reconciled by staff.
- **Questionnaires, measurements, workouts** — out of scope.

## Errors

| Code | Meaning |
|---|---|
| 401 | Missing/invalid token |
| 403 | Token lacks `portal:member`, or member is blacklisted |
| 422 | Validation, unknown product, or insufficient stock |
| 429 | Throttled |

Implementation: `app/Http/Controllers/Api/PortalController.php`, routes in
`routes/api.php`, covered by `tests/Feature/PortalApiTest.php`.
