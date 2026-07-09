# Changelog

Format: [Keep a Changelog](https://keepachangelog.com/). Dates are commit dates. From 2026-07 onward, every merged change adds an entry (see [CONTRIBUTING.md](CONTRIBUTING.md) Definition of Done).

## [Unreleased] — 2026-07-09 roadmap execution

### Added
- Production documentation set in `docs/` (full project audit of 2026-07-09).
- **Prerendered static HTML (SSG)**: `ssr: true` + Nitro prerender for all public routes; transactional routes (`/checkout`, `/track`, `/order`, `/p`, `/shop`) stay client-only. Crawlers and link previews now receive real content.
- **SEO layer**: `usePageSeo()` composable (canonical + OG/Twitter per page, applied to every public page), `SportsActivityLocation` JSON-LD sitewide, `FAQPage` JSON-LD, static `sitemap.xml`, robots `Sitemap:` line, www→apex 301.
- **CI gates**: backend deploys now run `php artisan test` + `composer audit`; frontend deploys run `npm audit` (high+); Dependabot for npm/composer/actions.
- Reusable UI: `SectionHeading`, `StatCounter`, `TestimonialForm`, `useCountUp`, `useDialogBehavior`; `AppButton` `ghost-inverse` variant; page transitions.
- Accessibility: `prefers-reduced-motion` honored globally (reveals, parallax, counters, hero video), skip-to-content link, dialog Esc/focus management + `aria-modal`, form error ARIA, JS-gated scroll-reveal styles (content visible without JS).

### Changed
- Fonts self-hosted (Bebas Neue + Inter variable, 57 KB latin woff2, preloaded); Google Fonts CSS and its two third-party connections removed.
- Hero video renders only on desktop viewports with motion allowed; mobile gets the poster image; one source instead of three; pause control added.
- Heading utility classes repointed from the never-loaded Playfair Display serif to Bebas Neue.
- Map coordinates corrected from central Kathmandu to the academy's actual location (from its Google Maps place entry).
- `ink-faint` darkened to meet WCAG AA contrast on white.
- Homepage pillar no longer claims "500+ active members" (unverified; conflicted with the academy's own 200+ stat).
- `.htaccess`: HSTS + Permissions-Policy added, deprecated X-XSS-Protection removed, SPA fallback now targets `200.html`.

### Removed
- Stale deploy workflows (`deploy-web.yml`, `deploy-api.yml`) targeting the retired server — they raced the current workflows on every push.
- Dead code: `constants.old.ts` (empty), `ServiceCard`/`ServiceGrid` (unreferenced), `utils/animations.ts` (unreferenced), `loading.vue` (not a Nuxt convention).
- Unused dependencies: `fuse.js`, `vee-validate`, `@vee-validate/zod`, `zod`, `@vueuse/motion`.

### Security
- OTP codes are no longer returned in API responses outside local environments (previously any env with missing SMS credentials leaked them).
- `composer update` cleared 28 advisories (incl. Filament RichEditor XSS); `npm audit fix` cleared 26 (incl. critical shell-quote).
- API subdomain `robots.txt` now disallows all crawling.
- Still requires owner action: rotate the Google Maps API key leaked in git history ([SECURITY.md](SECURITY.md) S1).

## Pre-changelog history (reconstructed from git)

### 2026-04-09 — `e2754c4`, `8d5684b`
- Fixed program field casing (`isPopular`/`sessionsPerWeek`), order ID prefix `PP→BSA`, hero video sources, track page placeholder.
- Updated phone numbers and Google Maps URL.

### 2026-04-07 — `d58043e`
- **Security**: removed leaked Google Maps API key from `.env.example` (⚠️ key still in git history — rotation tracked as [SECURITY.md](SECURITY.md) S1).

### 2026-04-04 → 04-05 — `6d60ce1`…`c44639b`
- Gallery section; CI/CD deploy workflows; `.htaccess` security/cache headers.
- Production domain switched to `bhaisepatisportsacademy.com.np`; email domain to `.com.np`.
- Fixed production 419 CSRF (trustProxies + file sessions) and CORS for the production domain.

### 2026-03-28 → 04-03 — `0bc5e6c`…`274904e`
- BSA dark-on-white theme; static SPA build; kitchen page; shop restored from demo base.
- Full backend integration: DB seeding, API controllers, Filament admin resources, frontend API wiring.
- Stability fixes: homepage 500s, stats counter, settings casing, scroll reveal, video hero.

### 2026-03-18 — `7bbd7d2`
- Initial commit: Bhaisepati Sports Academy platform (Nuxt 4 SPA + Laravel 12 API), adapted from a prior e-commerce demo codebase.
