# SEO

> Audit of 2026-07-09. **Update (same day): prerendering, OG/Twitter tags, canonicals, JSON-LD, sitemap and robots fixes are implemented** — pending deploy. Remaining: branded OG image, GBP/Search Console registration, location copy pass.

## The Core Problem

`ssr: false` means production serves an **empty HTML shell** for every route — verified live: the body is `<div id="__nuxt"></div>`. Consequences:

- Googlebot *can* render JS but ranks slower/less reliably; Bing and smaller engines largely won't.
- **Facebook/WhatsApp/Twitter link previews show nothing** — for a community academy in Nepal whose traffic will overwhelmingly arrive via shared links and Facebook, this is the single most costly gap.
- Per-page `useHead()` titles (which the pages do set, ✅) only apply after hydration — scrapers never see them.

### Fix (Phase: SEO, high impact / medium effort)
Switch to **prerendered static HTML**: set `ssr: true` and prerender all public routes via Nitro (`nitro.prerender.routes` + `crawlLinks`). Hosting doesn't change — output is still static files on LiteSpeed. Pages that fetch API data at runtime keep working (they hydrate and fetch client-side, or move to build-time fetching where content is stable). Highly dynamic routes (`/track`, `/checkout`) can stay client-only via `routeRules`.

## Current State Checklist

| Item | Status | Notes |
|---|---|---|
| Page titles | 🟡 | Set per-page via `useHead`, but client-side only (see above) |
| Meta description | 🟡 | One global description in `nuxt.config.ts`; needs per-page + server-rendered |
| OpenGraph / Twitter cards | ❌ | None anywhere. Add `useSeoMeta` with og:title/description/image/url/type + `twitter:card` per page; create a branded 1200×630 OG image |
| Structured data | ❌ | Add JSON-LD: `SportsActivityLocation` (name, address Bhaisepati/Lalitpur, geo, openingHours Su–Fr 06:00–21:00, telephone) sitewide; `FAQPage` on /faq; `Product`/`Offer` on programs if shop stays |
| Sitemap | ❌ | `sitemap.xml` currently returns the SPA shell via the rewrite fallback (a soft-200 lie to crawlers). Generate a real one at build time (`@nuxtjs/sitemap`) and exclude it from the SPA rewrite |
| robots.txt | 🟡 | Allows all (fine); add `Sitemap:` line; API subdomain should disallow all |
| Canonical URLs | ❌ | None; add per-page canonical, and decide www vs apex (both resolve today) with a 301 |
| Headings | 🟡 | Homepage structure is sound (one h1, sectioned h2/h3); audit other pages |
| Image alt text | 🟡 | Mostly present; decorative background images correctly use `alt=""`; keep enforcing |
| Semantic HTML | 🟡 | `<section>`/`<nav>`/`<main>` used; fine |
| Local SEO | ❌ | No Google Business Profile link strategy, no NAP consistency check; the map coords in `nuxt.config.ts` are central Kathmandu, not Bhaisepati |

## Keyword Reality Check

Target phrases people actually search: *"badminton court Lalitpur"*, *"badminton academy Kathmandu"*, *"gym Bhaisepati"*, *"badminton training near me"*, *"court booking Bhaisepati"*. Current copy rarely says "Lalitpur" or "Kathmandu" outside the hero badge. Page copy and titles should carry location naturally (see [CONTENT_STRATEGY.md](CONTENT_STRATEGY.md)).

## Recommended Title Pattern

```
/            Badminton Courts, Gym & Training in Bhaisepati, Lalitpur | BSA
/programs    Badminton Training Programs & Membership Prices | BSA
/book        Book a Badminton Court in Bhaisepati | BSA
/facilities  Courts, Gym, Sauna & Steam | Bhaisepati Sports Academy
```

## Order of Work

1. Prerender (unlocks everything else).
2. OG/Twitter meta + OG image (link sharing is the dominant channel).
3. Real sitemap + robots `Sitemap:` line + canonical + www/apex 301.
4. JSON-LD LocalBusiness + FAQ.
5. Location-aware copy pass.
6. Google Business Profile + Search Console registration, submit sitemap.
