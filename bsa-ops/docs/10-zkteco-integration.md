# ZKTeco door integration (M2F-LR Pro and friends)

BSA Ops speaks **two** access-control protocols. Which one a device uses is
set per device in Settings → Devices.

| Protocol | Who decides access | Used by |
|---|---|---|
| `native` | **We do**, in real time — the controller asks our API on every scan | Custom/integrator controllers calling `POST /api/v1/device/verify` |
| `zkteco_adms` | **The device does**, from a user list we keep synced | ZKTeco M2F-LR Pro, K40 Pro, SenseFace 4A, etc. |

## The important difference

ZKTeco hardware is self-contained: it stores users, faces and fingerprints
on the device and decides locally, in milliseconds, without a network round
trip. It never asks us "may this person enter?" at the moment of the scan.

So access is enforced by **syncing the whitelist**, not by answering per
scan:

1. `EligibilityService` decides who should be allowed at each door
   (the same rules as the kiosk — active subscription covering that
   department, not frozen, not expired, sessions left, dues under the
   block threshold).
2. `ZktecoSyncService` turns that into **enrol** / **revoke** commands.
3. The device polls us and applies them.

**Practical consequence:** revocation is not instantaneous. A membership
that lapses at midnight is removed from the door by the `00:20` sweep, and
the sync also runs **hourly** to catch same-day changes (a renewal, a
freeze, a member paying their dues). If the academy needs same-second
revocation, that requires a controller that asks us live — the `native`
protocol above.

Face/fingerprint templates stay on the device. We never receive biometric
data; we only push a numeric PIN and a display name.

## Member identity on the device

ZKTeco users are keyed by a numeric PIN. We derive it from the member code:

```
BSA-00013  →  PIN 13
```

`Member::devicePin()` does this, so no extra column and no separate
enrolment register. Enrol the member's face/finger on the device against
that PIN once, at the desk.

## Endpoints

Registered at the **domain root** (`routes/device.php`), because the
firmware only accepts a server address and port and appends the path
itself:

| Method | Path | Purpose |
|---|---|---|
| GET | `/iclock/cdata?SN=…&options=all` | Handshake — returns the config block, including `TimeZone=5.75` for Nepal and `Realtime=1` |
| POST | `/iclock/cdata?SN=…&table=ATTLOG` | Punches, tab-separated: `PIN⇥timestamp⇥status⇥verify…` |
| GET | `/iclock/getrequest?SN=…` | Device polls; we return `C:<seq>:<command>` or `OK` |
| POST | `/iclock/devicecmd?SN=…` | Result: `ID=<seq>&Return=0&CMD=DATA` |

Every punch writes an append-only `access_events` row; punches from a known
PIN also create a `check_ins` row with source `door_controller`, so device
entries show up in the same reports as kiosk check-ins. Re-sent batches are
deduplicated on (device, timestamp, PIN).

## Security — read this before going live

ADMS has **no authentication**. The device cannot send a token; it
identifies itself only by serial number in a query string. Our mitigations:

- The serial must be **registered in advance** in Settings → Devices, with
  a department. Unknown or deactivated serials get `403`.
- The endpoints are rate-limited (240/min per IP).
- Punches can only ever *create* records for a PIN that already maps to a
  member. There is no path from these endpoints to money or member edits.

What you should also do on the server:

- **Serve over HTTPS** (the M2F-LR Pro supports it on current firmware).
- If the device has a static LAN/WAN IP, restrict `/iclock/*` to it at the
  web-server or firewall level. This is the single biggest hardening win.
- Treat the serial number as semi-secret — anyone who knows it could post
  fake attendance rows (not open a door).

## Setting one up

1. **Settings → Devices → New**: name it, put the **serial number from the
   sticker** in Device UID, set Type `door_controller`, Protocol
   `ZKTeco ADMS (push)`, and pick the department it guards.
2. On the device: *Comm → Ethernet/WiFi*, then *Comm → Cloud Server /ADMS*:
   - Server address: `ops.bhaisepatisportsacademy.com.np`
   - Port: `443` (HTTPS) or `80`
   - Enable domain name / HTTPS as the firmware offers.
3. Back in the panel, use the row action **Sync users** — it queues
   enrol/revoke for every member. The device applies them within a minute.
4. Enrol faces/fingers at the device against each member's PIN.
5. Confirm: Access → Access Events shows the handshake and punches;
   Access → Check-ins shows the entries.

`php artisan ops:sync-access-devices` does the same sweep from the CLI and
runs on the schedule described above.

## Answering the buying question

**Yes — the M2F-LR Pro works with BSA Ops.** It ticks the boxes that
matter: ADMS auto-push (so it reaches our server without polling software
on a PC), TCP/IP + WiFi, and face + finger + card + PIN. The 1,000-face /
1,000-finger capacity is comfortable for a 60–500 member club, and the
4-hour battery keeps the door governed through load-shedding.

The one expectation to set with the academy: **eligibility changes reach
the door within the hour, not instantly** (see above). For a sports academy
that is normally fine — memberships lapse at date boundaries, not
mid-session.
