# Performance

> Audit of 2026-07-09. Ordered by expected real-user impact (mobile, Nepali networks — assume slow 4G).

## 🔴 P1 — Hero video: three hotlinked Pixabay MP4s
`pages/index.vue` autoplays a full-viewport `<video>` sourced from `cdn.pixabay.com` (multi-MB, third-party, no size control, could be deleted upstream any day). This dominates first-load bandwidth on the most important page.
**Fix**: replace with (a) a self-hosted, compressed (~1–2 MB, 720p, muted, 10–15s loop, H.264 + AV1/WebM) clip of the *actual academy*, `preload="none"` + `poster`, loaded only on `lg+` viewports and when `prefers-reduced-motion` is off; mobile gets the poster image. Or (b) a static hero photo until real footage exists — better branding *and* performance.

## 🔴 P2 — All imagery hotlinked from Unsplash
Every image on the site is an Unsplash URL (`utils/constants.ts`) or Cloudinary URL from the DB. `@nuxt/image` is installed and configured for Cloudinary but **`<NuxtImg>` is used nowhere** — pages use raw `<img>` with no `width/height` (CLS), no `srcset`, no format negotiation.
**Fix**: move real photos to Cloudinary, render via `<NuxtImg>` (`f_auto,q_auto`, responsive `sizes`), always with explicit dimensions. Add `loading="lazy"` below the fold (partially done) and `fetchpriority="high"` on the hero poster.

## 🟠 P3 — Render-blocking Google Fonts CSS
`fonts.googleapis.com` stylesheet in `<head>` blocks first paint and adds a third-party connection. Inter loads 5 weights; audit usage (300 and 500 are likely droppable).
**Fix**: self-host Bebas Neue + Inter (2–3 weights) as woff2 with `font-display: swap` and preload — pairs with the CSP work in [SECURITY.md](SECURITY.md).

## 🟠 P4 — Unused dependencies in the bundle
Confirmed unused in `app/`: **fuse.js**, **vee-validate**, **@vee-validate/zod**, **zod**. `date-fns` is used once (`OrderTimeline`) — replaceable with `Intl.DateTimeFormat` (already the approach in `formatters.ts`). `@vueuse/motion` (a whole motion engine) is used only for modal/sheet enter animations that CSS transitions can do.
**Fix**: uninstall the confirmed-unused four immediately (zero risk); fold `date-fns` and reevaluate `@vueuse/motion` when touching those components. Then measure with `nuxi analyze`.

## 🟠 P5 — SPA architecture costs
With `ssr: false`, users stare at a blank/skeleton page until the JS bundle parses and **five API calls** (`/stats`, `/facilities`, `/programs`, `/testimonials`, `/schedule`) round-trip to the API for the homepage.
**Fix**: prerendering (see [SEO.md](SEO.md)) gives instant first paint; content that changes rarely (facilities, programs) can be fetched at build time. The API already caches settings; add `Cache-Control`/CDN or Laravel response cache on hot read endpoints, and consider collapsing the homepage's five calls into one `/api/home` payload.

## 🟡 P6 — Smaller items
- Parallax uses a `scroll` listener mutating `transform` (passive ✅). Fine, but gate behind `prefers-reduced-motion` and skip on mobile.
- Stat counters create up to 6 IntersectionObservers + rAF loops — harmless, but extract to a composable (`useCountUp`) with a single observer.
- `.htaccess` cache/compression headers are ✅ good (immutable hashed assets, no-cache HTML). Consider brotli if the host supports it.
- Icons: lucide per-icon imports ✅; `@nuxt/icon` server bundle limited to `simple-icons` ✅.
- Hydration: no SSR today so no mismatch risk; after prerendering, watch for `Date`-dependent rendering (the "today's schedule" section keys off `new Date()` — render client-side or normalize).

## Budgets (adopt in CI once measured)

| Metric (mobile, slow 4G) | Budget |
|---|---|
| LCP | < 2.5 s |
| CLS | < 0.1 |
| JS shipped (gzip) | < 180 kB entry |
| Hero media | < 300 kB initial (poster), video lazy |
| Lighthouse perf | ≥ 90 |

Tooling: run Lighthouse CI or PageSpeed on `/`, `/programs`, `/book` per release; `nuxi analyze` on dependency changes.
