# Security

> Audit of 2026-07-09 against commit `8d5684b` and the live deployment.
> Severity: 🔴 act now · 🟠 soon · 🟡 planned · ✅ already good

## Findings

### 🔴 S1 — Rotate the Google Maps API key leaked in git history
Commit `d58043e` removed a leaked key from `.env.example`, but **removal does not un-leak it** — it remains in git history (and a key of the same shape sits in the local `bsa-web/.env`). Action: rotate the key in Google Cloud Console, then restrict the new key by HTTP referrer (`bhaisepatisportsacademy.com.np`) **and** by API (Maps JavaScript, Places, Geocoding only). Note: any `NUXT_PUBLIC_*` key ships in the JS bundle by design — referrer restriction is the actual protection, not secrecy.

### ✅ S2 — RESOLVED (2026-07-09): duplicate deploy workflows removed
`deploy-web.yml` + `deploy-frontend.yml` (and `deploy-api.yml` + `deploy-backend.yml`) trigger on the same paths. Two pipelines deploying the same docroot concurrently can leave production in a mixed state. Consolidate to one per app. (Also an availability/integrity issue, hence listed here.)

### 🟠 S3 — Filament admin exposed with defaults
`https://api.bhaisepatisportsacademy.com.np/admin/login` is publicly reachable. Recommended hardening, in order of value:
1. Rate-limit the login route (Filament doesn't throttle by default).
2. Enforce strong password + enable 2FA (Filament breezy/2FA plugin or custom).
3. Optionally move the panel path off `/admin` and/or IP-allowlist via `.htaccess` if admin access is from known networks.
4. Ensure `SESSION_SECURE_COOKIE=true` and appropriate `SESSION_DOMAIN` in production.

### 🔶 S4 — HSTS added (2026-07-09); CSP still pending
Live response has `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy` (✅) but no `Strict-Transport-Security` and no `Content-Security-Policy`. Add to `bsa-web/public/.htaccess`:
- `Strict-Transport-Security: max-age=31536000; includeSubDomains` (after confirming all subdomains serve HTTPS).
- A CSP. Realistic starting policy given current external deps (Cloudinary, Unsplash, Pixabay video, Google Fonts, Umami, Maps): start in `Content-Security-Policy-Report-Only`, tighten as placeholder media is replaced with self-hosted assets (which also shrinks the policy).
- Drop `X-XSS-Protection` (deprecated; harmless but noise).

### ✅ S5 — RESOLVED (2026-07-09): OTP dev fallback gated to local env
`OtpService::send()` returns `dev_otp` in the JSON response whenever `SPARROW_TOKEN` is unset. If SMS credentials ever lapse in production, OTP verification silently becomes self-service. Gate this on `app()->environment('local')` instead of "SMS not configured".

### 🟡 S6 — CORS and proxy breadth
- `allowed_headers: ['*']` and `allowed_methods: ['*']` — tighten to what the SPA sends (`Content-Type`, `Authorization`, `X-Tracking-Token`…).
- `allowed_origins_patterns` accepts `http://` as well as `https://` for the production domain — drop the `http?` optionality.
- `trustProxies(at: '*')` was needed for the hosting setup (commit `c44639b`); acceptable behind the host's LB, but document it and revisit if hosting changes, since it lets any client-supplied `X-Forwarded-For` be trusted → affects rate-limit keying by IP.

### ✅ S7 — RESOLVED (2026-07-09): audits in CI, Dependabot, all advisories cleared
No automated vulnerability scanning. Add to CI: `composer audit` and `npm audit --omit=dev` (fail on high), plus Dependabot/Renovate for both lockfiles.

### 🟡 S8 — Operational gaps
- No error monitoring (Sentry or similar) — security incidents would be invisible.
- Confirm production `APP_DEBUG=false` and `LOG_LEVEL=error` (`.env.production.example` sets this; verify the real server env).
- Database backups: define schedule + restore test (cPanel cron + `mysqldump`).
- `robots.txt` on the API subdomain should `Disallow: /` (only the SPA domain should be crawled).

## What's Already Good ✅

- **Secrets**: `.env` files are gitignored in both apps; only `.example` files are committed; CI uses GitHub secrets.
- **OTP design**: phone numbers stored only as HMAC-SHA256 hashes (`OTP_SECRET`), codes hashed, 10-min expiry, single-use, 5/hour/phone cap **plus** route-level `throttle:otp`.
- **Rate limiting**: named throttles on OTP, checkout, restock; `throttle:6,1` on testimonial submission.
- **Input validation**: FormRequests / `$request->validate()` on write endpoints; testimonials are held for admin approval (`is_active = false`) — no unmoderated user content is rendered.
- **XSS**: Vue escapes interpolations; no `v-html` usage found in `app/`.
- **CSRF**: the public API is token/stateless; Filament (session-based) has Laravel CSRF; `trustProxies` fix for the 419 issue is in place.
- **Order tracking** uses signed tracking tokens via middleware rather than guessable order IDs.
- **Transport**: HTTPS with HTTP/2 + HTTP/3 on the host; sane cache headers; SPA fallback doesn't leak directory listings (`-Indexes`).

## Standing Policy

- Never commit `.env*` (except `*.example` with empty secrets). If a secret lands in git history: **rotate first**, scrub second.
- All new write endpoints get a named throttle + FormRequest validation.
- Review this document whenever adding an external service or a new form.
