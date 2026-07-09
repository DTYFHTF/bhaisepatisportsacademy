# Roadmap

> Incremental, production-first. No big-bang rewrite. Each item ships alone, small commits, docs updated with it.
> Effort: S (<½ day) · M (1–2 days) · L (3–5 days). Impact: ★ low → ★★★ high.

## Phase 1 — Critical fixes & hygiene (this week)

| # | Item | Effort | Impact | Notes |
|---|---|---|---|---|
| 1.1 | Rotate + restrict Google Maps API key (leaked in git history) | S | ★★★ | ⏳ **owner action** — needs Google Cloud Console access |
| 1.2 | ✅ Consolidate duplicate deploy workflows | S | ★★★ | Done — stale pair deleted, test/audit gates added |
| 1.3 | **Owner decisions**: Shop live or hidden? Kitchen real? Services page? | S | ★★★ | Blocks 1.4/3.x. Documented in [AUDIT.md](AUDIT.md) |
| 1.4 | ✅ Dead code + unused deps removed (constants.old, ServiceCard/Grid, animations.ts, loading.vue, fuse.js, vee-validate, zod, @vueuse/motion). Backend beauty leftovers (WaxType, Look*, FabricStory, StyleExplanationService) await owner shop/kitchen decision | M | ★★ | Partially done |
| 1.5 | ✅ Orphaned serif heading classes fixed | S | ★★ | Done |
| 1.6 | ✅ OTP dev fallback gated to local env | S | ★★ | Done |
| 1.7 | ✅ Real coordinates set (from the academy Maps place) | S | ★ | Done |
| 1.8 | ✅ Reduced-motion respected globally | S | ★★ | Done |

## Phase 2 — Performance

| # | Item | Effort | Impact |
|---|---|---|---|
| 2.1 | 🔶 Hero video gated (desktop + motion-ok only, single source, pause control); self-hosted real footage still pending photography | M | ★★★ |
| 2.2 | Migrate imagery to Cloudinary + `<NuxtImg>` with dimensions/srcset | M | ★★★ |
| 2.3 | ✅ Fonts self-hosted (57 KB, variable Inter), preloaded, swap | S | ★★ |
| 2.4 | 🔶 HSTS + Permissions-Policy done; Filament v3 has built-in login throttling — verify config; 2FA still recommended | S | ★★ |
| 2.5 | ✅ Audit gates in both deploy workflows + Dependabot; all current advisories cleared | S | ★★ |
| 2.6 | Bundle audit with `nuxi analyze`; set budgets in [PERFORMANCE.md](PERFORMANCE.md) | S | ★ |

## Phase 3 — SEO (highest business leverage)

| # | Item | Effort | Impact |
|---|---|---|---|
| 3.1 | ✅ Prerendering live for all public routes | L | ★★★ |
| 3.2 | 🔶 usePageSeo() with OG/Twitter on all pages; branded OG image pending photography | M | ★★★ |
| 3.3 | ✅ Done | S | ★★ |
| 3.4 | ✅ Done | S | ★★ |
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
| 5.1 | 🔶 SectionHeading, StatCounter/useCountUp, TestimonialForm, ghost-inverse done; MediaCard pending | M | ★★ |
| 5.2 | Replace emoji icons with SVG; unify elevation levels; resolve `energy`/`court` color roles | S | ★ |
| 5.3 | Footer upgrade: map, hours, quick links, trust signals | S | ★★ |
| 5.4 | Inner-page hierarchy pass (programs, facilities, about) | M | ★★ |

## Phase 6 — Animation polish

6.1 ✅ Page transitions done · 6.2 ✅ @vueuse/motion replaced with CSS transitions and removed · 6.3 Micro-interaction pass on buttons/cards — S/★ (pending).

## Phase 7 — Accessibility completion

7.1 🔶 Esc/focus-return/aria-modal done (full focus *trap* still pending) · 7.2 ✅ done for testimonial form; apply pattern to booking/checkout — S/★★ · 7.3 ✅ done · 7.4 axe + keyboard pass across all pages — M/★★ (pending).

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
