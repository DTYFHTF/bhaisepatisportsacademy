# Device API

For door controllers, turnstiles, and kiosk hardware. Base: `/api/v1/device`.
All endpoints require a **Sanctum bearer token with the `device:verify`
ability**, issued per device.

## Onboarding a device

1. Settings → Devices → create the device (name, `device_uid`, type,
   department it controls).
2. Row action **API token** — the token is displayed once; configure it on the
   hardware.
3. The device's department determines which door the eligibility check runs
   against. A device with no department cannot verify (422).

Regenerating a token revokes all previous tokens for that device. Deactivating
a device (is_active = false) refuses all its calls (403).

## POST /verify — the door decision

Request:
```json
{ "credential_type": "rfid_card", "identifier": "<raw card/QR value>" }
```

Response (~4 queries, always 200 for a decision):
```json
{
  "decision": "allowed",
  "reason": null,
  "member": {
    "member_code": "BSA-00008",
    "name": "Niraj Basnet",
    "photo_url": null,
    "valid_until": "2026-09-06",
    "session_consumed": false
  }
}
```

`decision: denied` carries a `reason` (see doc 03 for the full enum);
`member` is null for unknown credentials. Every call — including unknown
cards — writes an `access_events` audit row; allowed calls also record the
check-in (and consume a pack session).

## POST /heartbeat

Body optionally `{ "firmware": "v2.1" }`. Updates `last_seen_at`, the
device's IP, firmware. Returns server time. Devices whose heartbeat is >10
min old show red in the Devices list.

## POST /events — offline back-fill

When the network was down, devices flush locally-made decisions:

```json
{ "events": [
  { "identifier": "…", "decision": "denied",
    "occurred_at": "2026-07-20T05:00:00+05:45", "reason": "no_active_subscription" }
] }
```

Max 500 per batch. Idempotent per (device, occurred_at, credential hash) —
replays are skipped; response reports `stored`.

## Errors

- 401 — missing/invalid token.
- 403 — token lacks `device:verify`, or device deactivated.
- 422 — validation, or device not assigned to a department (verify only).
- Throttle: 120 requests/min per device.
