# Roadmap

> Incremental, production-first. No big-bang rewrite. Each item ships alone, small commits, docs updated with it.
> Effort: S (<½ day) · M (1–2 days) · L (3–5 days). Impact: ★ low → ★★★ high.

## Phase 1 — Critical fixes & hygiene (this week)

| # | Item | Effort | Impact | Notes |
|---|---|---|---|---|
| 1.1 | Rotate + restrict Google Maps API key (leaked in git history) | S | ★★★ | [SECURITY.md](SECURITY.md) S1 |
| 1.2 | Consolidate duplicate deploy workflows (2 per app → 1) | S | ★★★ | Deployment race today. S2 |
| 1.3 | **Owner decisions**: Shop live or hidden? Kitchen real? Services page? | S | ★★★ | Blocks 1.4/3.x. Documented in [AUDIT.md](AUDIT.md) |
| 1.4 | Remove confirmed dead code: `constants.old.ts`, beauty leftovers (FabricStory, WaxType, Look*, StyleExplanationService), unused deps (fuse.js, vee-validate, @vee-validate/zod, zod) | M | ★★ | Zero-risk deletions first |
| 1.5 | Fix orphaned serif heading classes in `main.css` | S | ★★ | [DESIGN_SYSTEM.md](DESIGN_SYSTEM.md) |
| 1.6 | Gate OTP `dev_otp` fallback on local env | S | ★★ | S5 |
| 1.7 | Fix `storeLat/storeLng` to real Bhaisepati coordinates | S | ★ | |
| 1.8 | `prefers-reduced-motion` global kill switch | S | ★★ | [ACCESSIBILITY.md](ACCESSIBILITY.md) A1 |

## Phase 2 — Performance

| # | Item | Effort | Impact |
|---|---|---|---|
| 2.1 | Replace Pixabay hero videos: self-hosted compressed clip or static hero photo; poster-only on mobile | M | ★★★ |
| 2.2 | Migrate imagery to Cloudinary + `<NuxtImg>` with dimensions/srcset | M | ★★★ |
| 2.3 | Self-host fonts (Bebas + Inter 2–3 weights), preload, `font-display: swap` | S | ★★ |
| 2.4 | Admin: rate-limit Filament login, HSTS header, security header pass | S | ★★ |
| 2.5 | `composer audit` + `npm audit` in CI; Dependabot | S | ★★ |
| 2.6 | Bundle audit with `nuxi analyze`; set budgets in [PERFORMANCE.md](PERFORMANCE.md) | S | ★ |

## Phase 3 — SEO (highest business leverage)

| # | Item | Effort | Impact |
|---|---|---|---|
| 3.1 | Prerender: `ssr: true` + Nitro prerender of public routes (hosting unchanged) | L | ★★★ |
| 3.2 | `useSeoMeta` per page: OG/Twitter + branded OG image | M | ★★★ |
| 3.3 | Real sitemap.xml + robots `Sitemap:` + canonical + www/apex 301 | S | ★★ |
| 3.4 | JSON-LD: SportsActivityLocation sitewide, FAQPage on /faq | S | ★★ |
| 3.5 | Google Business Profile + Search Console, submit sitemap | S | ★★★ |
| 3.6 | Location-aware titles/copy pass ("Bhaisepati", "Lalitpur") | S | ★★ |

## Phase 4 — Content & branding (can run parallel to 2–3; mostly non-code)

| # | Item | Effort | Impact |
|---|---|---|---|
| 4.1 | Photo/video shoot at the academy (shot list in [CONTENT_STRATEGY.md](CONTENT_STRATEGY.md)) | M | ★★★ |
| 4.2 | Verify or replace seeded stats & testimonials with real ones | S | ★★★ |
| 4.3 | Coaches section: names, faces, credentials | M | ★★★ |
| 4.4 | About/Story rewrite: founder story + promise to parents | M | ★★ |
| 4.5 | Program transformation copy + CTA standardization (Book/Join) | S | ★★ |

## Phase 5 — UI modernization

| # | Item | Effort | Impact |
|---|---|---|---|
| 5.1 | Extract `SectionHeading`, `MediaCard`, `StatCounter`/`useCountUp`, `TestimonialForm`; add `AppButton` ghost-inverse variant | M | ★★ |
| 5.2 | Replace emoji icons with SVG; unify elevation levels; resolve `energy`/`court` color roles | S | ★ |
| 5.3 | Footer upgrade: map, hours, quick links, trust signals | S | ★★ |
| 5.4 | Inner-page hierarchy pass (programs, facilities, about) | M | ★★ |

## Phase 6 — Animation polish

6.1 Page transitions (Nuxt `pageTransition`, per [ANIMATION_GUIDELINES.md](ANIMATION_GUIDELINES.md)) — S/★ · 6.2 Replace `@vueuse/motion` in Modal/Sheet with CSS transitions, drop the dep — S/★ · 6.3 Micro-interaction pass on buttons/cards — S/★.

## Phase 7 — Accessibility completion

7.1 Overlay focus-trap/Esc/focus-return verification & fixes — M/★★ · 7.2 Form error announcements (`aria-describedby`/`aria-invalid`) — S/★★ · 7.3 Skip link, contrast token fixes, JS-gated scroll-reveal styles — S/★★ · 7.4 axe + keyboard pass across all pages — M/★★.

## Phase 8 — Advanced features (each needs its own spec before build)

- Real-time court availability + slot-based booking calendar (replaces drawer flow) — L/★★★
- Online payments for programs/memberships (Khalti/eSewa already integrated for shop) — M/★★★
- Member portal: attendance, renewals, progress — L/★★
- Nepali (नेपाली) i18n — L/★★
- Tournaments/events pages + registration — M/★★
- Blog/news for SEO longevity — M/★
- Frontend test suite (Vitest + Playwright smoke on book/checkout) and API feature tests — M/★★

## Sequencing Logic

1→2→3 ordered by risk removal, then user-perceived speed, then discoverability. Content (4) is parallel and mostly the owner's calendar. UI/animation (5–6) after real content exists — polishing stock placeholders is wasted work. 8 only starts once 1–3 are done and stable.
