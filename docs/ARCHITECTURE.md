# Architecture

> Current implementation, decisions, risks, and recommended evolution.
> Last audited: 2026-07-09 against commit `8d5684b`.

## System Overview

```
┌─────────────────────────────┐        ┌──────────────────────────────────┐
│  bsa-web (Nuxt 4 SPA)       │  HTTPS │  bsa-api (Laravel 12)            │
│  Static files on LiteSpeed  │──────▶ │  api.bhaisepatisportsacademy...  │
│  bhaisepatisportsacademy    │  JSON  │  + Filament 3 admin at /admin    │
│  .com.np                    │        │  MySQL (prod) / SQLite (dev)     │
└─────────────────────────────┘        └──────────────────────────────────┘
        │                                        │
        ├─ Cloudinary (images, cloud dhknx0eac)  ├─ Sparrow SMS (OTP)
        ├─ Unsplash/Pixabay (placeholder media)  ├─ Khalti / eSewa (payments)
        └─ Umami (analytics, cloud.umami.is)     └─ SMTP (cPanel mail)
```

Monorepo layout:

| Path | What it is |
|---|---|
| `bsa-web/` | Nuxt 4.3 + Vue 3.5 frontend, **SPA (`ssr: false`)**, statically generated with `nuxi generate` |
| `bsa-api/` | Laravel 12 JSON API + Filament 3 admin panel |
| `docs/` | Documentation (this set + legacy design-phase docs `01–16`) |
| `scripts/` | `server-setup.sh` for the shared-hosting server |
| `.github/workflows/` | CI/CD to production on push to `main` |

## Frontend (`bsa-web`)

- **Framework**: Nuxt 4 with `ssr: false`. `nuxi generate` emits a client-only shell; every route serves the same empty `<div id="__nuxt">` and hydrates in the browser. This is the single biggest architectural constraint — see [SEO.md](SEO.md) and [PERFORMANCE.md](PERFORMANCE.md).
- **Routing**: file-based (`app/pages/`). Live routes: `/`, `/programs`, `/facilities`, `/kitchen`, `/shop/[[category]]`, `/p/[slug]` (product), `/book`, `/checkout`, `/order/confirmed`, `/track`, `/about`, `/story`, `/services`, `/faq`, `/privacy`, `/terms`, `/refund-policy`, plus a `[...slug]` catch-all.
- **Styling**: Tailwind (`@nuxtjs/tailwindcss`) with custom tokens in `tailwind.config.ts`; global CSS in `app/assets/css/main.css`. See [DESIGN_SYSTEM.md](DESIGN_SYSTEM.md).
- **State**: Pinia stores — `cart`, `booking`, `checkout` — persisted via `pinia-plugin-persistedstate` (localStorage).
- **Data fetching**: direct `useFetch`/`$fetch` calls against `config.public.apiBase` inside pages. No repository/service layer, no shared API client, no error/retry conventions.
- **Animations**: custom `v-scroll` directive (`app/plugins/scroll-reveal.client.ts`) + CSS keyframes; `@vueuse/motion` is installed but only used in `AppModal`/`AppSheet`. See [ANIMATION_GUIDELINES.md](ANIMATION_GUIDELINES.md).
- **Server hosting**: LiteSpeed shared hosting. `public/.htaccess` provides SPA fallback rewrite, security headers, cache headers, gzip.

## Backend (`bsa-api`)

- **Framework**: Laravel 12, PHP ≥ 8.2. Admin panel: Filament 3 at `/admin` (session auth).
- **Public API** (`routes/api.php`): read endpoints for programs, facilities, schedule, testimonials, kitchen, stats, services, products, settings; write endpoints for bookings, testimonials (moderated), OTP send/verify, checkout, order lookup/tracking, restock alerts. Rate limiting via named throttles (`otp`, `checkout`, `restock`, `6,1` on testimonials).
- **Auth model**: no end-user accounts. Phone-OTP (HMAC-hashed phone + code, Sparrow SMS) mints a short-lived Sanctum token scoped to `checkout`; order tracking uses signed tracking tokens (`TrackingTokenAuth` middleware).
- **Services layer**: `OrderService`, `OtpService`, `SmsService`, `KhaltiService`, `EsewaService`, `StyleExplanationService` (leftover, see below).
- **Data**: MySQL in production, SQLite locally. Migrations reveal the project's history — tables for products/variants/orders/looks/wax types come from the original e-commerce demo this repo was forked from (`2026_03_14_000004_update_products_for_beauty.php` et al.).
- **Responses**: `CamelCaseResponse` middleware converts snake_case to camelCase for the API.

## Deployment

- GitHub Actions on push to `main`, path-filtered per app.
- ⚠️ **There are four deploy workflows for two apps**: `deploy-web.yml` + `deploy-frontend.yml` both trigger on `bsa-web/**`, and `deploy-api.yml` + `deploy-backend.yml` both trigger on `bsa-api/**`. Unless two of them are disabled in the GitHub UI, every push runs two competing deployments — a race condition on the production docroot. **Consolidate to one workflow per app** (tracked in [ROADMAP.md](ROADMAP.md) Phase 1).
- Frontend deploys `.output/public/` to the docroot; API deploys via the workflow in `deploy-api.yml` (tests run on PHP 8.4 with SQLite before deploy).

## Known Architectural Debt

1. **`ssr: false` on a public marketing site.** Crawlers and social scrapers receive an empty shell. Recommended evolution: keep static hosting but switch to `nuxt generate` with SSR enabled (`ssr: true` + `nitro.prerender`), which pre-renders real HTML per route with zero server requirements. This is a contained, high-impact change.
2. **Demo (e-commerce) code carried in production** — see the inventory in [COMPONENTS.md](COMPONENTS.md#demo-leftovers) and the decision list in [ROADMAP.md](ROADMAP.md). Shop sells placeholder products (e.g. seeded Yonex racket) live today.
3. **No API client layer in the frontend** — fetch URLs and response types are re-declared inline per page (e.g. `index.vue` declares five ad-hoc interfaces). Introduce `app/composables/useApi.ts` + shared types generated from one source of truth.
4. **Static data duplicated in `constants.ts` and the database.** Programs, facilities, schedule, kitchen menu exist both as hardcoded arrays and as seeded DB rows served by the API. The DB is the source of truth (admin-editable); the constants copies should be deleted or reduced to types.
5. **No automated tests for the frontend; API tests are the Laravel skeleton only.**
6. **No error monitoring** (no Sentry or equivalent) and no uptime monitoring.

## Assumptions & Open Questions (do not guess — confirm with owner)

- Is the **Shop** meant to launch for real (with real inventory), or should it be hidden until then?
- Is the **Kitchen** ordering flow real or aspirational? The menu is both hardcoded and in the DB.
- `pages/services.vue` and `StyleExplanationService`/`WaxType`/`WardrobeRole` enums are beauty-demo leftovers — is any "services" concept planned for BSA?
- Are Khalti/eSewa merchant accounts actually configured in production?
- `storeLat/storeLng` in `nuxt.config.ts` (27.7172, 85.3240) is central Kathmandu, not Bhaisepati — needs the real coordinates.
