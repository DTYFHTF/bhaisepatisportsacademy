# Changelog

Format: [Keep a Changelog](https://keepachangelog.com/). Dates are commit dates. From 2026-07 onward, every merged change adds an entry (see [CONTRIBUTING.md](CONTRIBUTING.md) Definition of Done).

## [Unreleased]

### Added
- Production documentation set in `docs/`: ARCHITECTURE, COMPONENTS, DESIGN_SYSTEM, SECURITY, SEO, PERFORMANCE, ACCESSIBILITY, ANIMATION_GUIDELINES, CONTENT_STRATEGY, AUDIT, ROADMAP, CONTRIBUTING, CHANGELOG (full project audit of 2026-07-09; no code changes).

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
