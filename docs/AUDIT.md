# Full Project Audit — 2026-07-09

> Scored snapshot at commit `8d5684b`, live at https://bhaisepatisportsacademy.com.np/.
> Scores are /10. Detailed remediation lives in the linked docs; prioritization in [ROADMAP.md](ROADMAP.md).

## UI / UX Scorecard

| Area | Score | Assessment |
|---|---|---|
| Visual hierarchy | 7 | Homepage is well-structured: hero → pillars → facilities → programs → stats → schedule → testimonials → gallery → CTA. Eyebrow+heading rhythm is consistent. Weakens on inner pages. |
| Layout consistency | 6 | `.section-container`/`.section-padding` helpers exist but sections also hand-roll wrappers; card paddings/radii mostly consistent (rounded-2xl). |
| Typography | 5 | Bebas + Inter is a strong athletic pairing, but the defined heading utility classes point at an unloaded serif (Playfair leftover) — a live defect. Ad-hoc heading styles everywhere instead of the scale. |
| White space | 7 | Generous section padding; cards breathe. Some dense zones (stats band, schedule rows) acceptable. |
| Responsiveness | 7 | Mobile-first grids collapse sensibly; sticky header + mobile nav present. Hero video is a mobile liability (bandwidth). |
| Mobile experience | 6 | Nav/drawers exist; tap targets adequate. Full-viewport autoplay video and five API calls delay usable content on 4G. |
| CTA placement | 7 | "Book a Court" is correctly the primary CTA in header, hero, and closing section. Ghost buttons bypass `AppButton` (consistency debt). |
| Navigation | 6 | Clear, but includes demo-era items (Shop, Kitchen) that dilute the academy story; About/Story/Services overlap unresolved. |
| Footer | 6 | Functional; not yet a trust surface (no map embed, hours grid, or quick-links hierarchy). |
| Hero section | 6 | Strong copy and motion cascade; undermined by hotlinked stock video and no reduced-motion gate. |
| Forms | 6 | Booking/checkout steps are decent; testimonial form is inline-styled, errors not announced to AT. |
| Card designs | 7 | Facility/program/testimonial cards are cohesive; icon-badge positioning logic (first/middle/last) is cute but arbitrary. |
| Color consistency | 6 | Red accent used with discipline in most places, but three reds/blues defined (`accent`, `energy`, `court`) with unclear roles. |

**UI/UX overall: 6.3/10** — a competent template-grade site; not yet a branded experience.

## Branding — where it feels generic

Target feelings: trust, professionalism, discipline, growth, athletic excellence, community, modern academy, premium coaching.

- **Stock everything**: Unsplash photos labeled "Life at BSA", Pixabay hero video, seeded testimonials, demo shop products. Authenticity is the premium signal; today the site has none of its own pixels. *(Biggest branding issue by far.)*
- **No people**: no coaches, no founder, no members' faces. Sports brands are built on humans in motion.
- **Emoji as iconography** (🏸💪♨️) reads DIY, not premium.
- **Generic superlatives** ("world-class", "elite") without evidence; premium brands show, then understate.
- **What's already on-brand**: red/black/white athletic palette, Bebas display voice, "Train Harder. Move Faster. Grow Stronger.", the badminton-first positioning, court-blue accent waiting to be used deliberately.

**Branding: 4/10** — the skeleton is right; the flesh is placeholder.

## Storytelling

| Question | Answered today? |
|---|---|
| Who are we? | Partially — tagline + location, but no faces or origin |
| Why should parents trust us? | ❌ No coach credentials, no safety/supervision content |
| Why should students join? | Partially — programs list features, not transformations |
| What makes us different? | ❌ Nothing differentiates from any gym template |
| What transformation do we promise? | ❌ Absent |
| What experience should visitors feel? | Energy — the motion/palette carry it |

**Storytelling: 3/10.** Narrative design → [CONTENT_STRATEGY.md](CONTENT_STRATEGY.md).

## Technical Audits (summaries — full findings in linked docs)

| Audit | Score | Top findings | Doc |
|---|---|---|---|
| Performance | 5 | Hotlinked hero videos; Unsplash images; unused deps (fuse.js, vee-validate, zod); `@nuxt/image` configured but unused; render-blocking fonts; 5 API calls before homepage content | [PERFORMANCE.md](PERFORMANCE.md) |
| Security | 6.5 | Rotate git-history-leaked Maps key; duplicate deploy workflows; admin hardening; no HSTS/CSP; OTP dev-fallback. Strong foundations: hashed OTPs, throttles, gitignored secrets, moderated UGC | [SECURITY.md](SECURITY.md) |
| SEO | 2 | `ssr:false` serves an empty shell; no OG/Twitter/schema/sitemap/canonical. Per-page titles exist but client-only | [SEO.md](SEO.md) |
| Accessibility | 5 | Good focus states/labels/announcer; motion ignores `prefers-reduced-motion`; content hidden pre-JS; contrast edge cases; overlay focus management unverified | [ACCESSIBILITY.md](ACCESSIBILITY.md) |
| Code quality | 6 | Clean component structure and naming; but: dead file (`constants.old.ts`), DB-duplicated constants, beauty-demo leftovers (FabricStory, WaxType, Look, StyleExplanationService…), no linting/formatting config, no FE tests, inline API types per page | [CONTRIBUTING.md](CONTRIBUTING.md), [COMPONENTS.md](COMPONENTS.md) |

## Demo Code Still in Production (decision required)

1. **Shop** — live with seeded demo inventory (real Yonex racket listing, purchasable). Hide or stock for real. **Owner decision needed.**
2. **Kitchen** — real feature or aspiration? Menu duplicated in code + DB.
3. **Services page + beauty leftovers** (`WaxType`, `WardrobeRole`, `Look*`, `StyleExplanationService`, `FabricStory`) — safe to remove after confirmation.
4. **Seeded stats/testimonials** — replace with verified facts (see [CONTENT_STRATEGY.md](CONTENT_STRATEGY.md)).
5. **Placeholder media** — Unsplash/Pixabay throughout.
6. **Wrong map coordinates** in `nuxt.config.ts` (central Kathmandu, not Bhaisepati).
