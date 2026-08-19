# Eligibility & access

`EligibilityService::check(Member, Department, ?at)` is the **single source of
truth** for "may this member enter this department right now?". The kiosk, the
member profile's eligibility strip, and the door-controller API all call the
same method — there is exactly one answer.

It is a pure read: it never writes. Recording the attempt (allowed or denied)
is `CheckInService::checkIn()`'s job.

## Decision order

Checks run in fixed order so the reported reason is the most fundamental
problem:

1. `member_blacklisted` — trumps everything.
2. `no_active_subscription` — no live (active/frozen, started) subscription.
3. `department_not_covered` — live subscriptions exist, none covers this door.
4. `subscription_frozen`
5. `subscription_expired` — date check as belt-and-braces between midnight and
   the nightly status flip.
6. `no_sessions_remaining` — session packs only.
7. `outside_off_peak_hours` — off-peak plans admit only inside their window.
8. `outstanding_dues` — balance > threshold **and** oldest open invoice past
   grace (doc 02).
9. **Allowed.** When several subscriptions qualify, time-based is preferred
   over packs so unlimited plans don't burn pack sessions.

Denial reasons the API can also produce: `unknown_credential`,
`credential_revoked` (resolved before eligibility runs).

## Check-in recording

Every attempt writes a `check_ins` row (`was_allowed`, `denial_reason`).
Pack sessions decrement atomically
(`WHERE sessions_remaining > 0`) so simultaneous door + kiosk check-ins can't
double-spend a session.

## The kiosk

`/admin/check-in-kiosk` — front-desk daily driver. Search by phone / member
code / name, or scan a card or QR (raw identifiers ≥ 6 chars are hashed and
matched against credentials — a hit selects the member instantly). The member
card shows a per-department chip: green "tap to check in" or red with the
denial reason. One tap records the visit.

## Credentials

Raw card/QR identifiers are **never stored** — only `sha256(raw)` plus a
4-char hint for humans. Issue and revoke from the member profile
(Credentials relation manager). Card deposits are tracked on the credential.
