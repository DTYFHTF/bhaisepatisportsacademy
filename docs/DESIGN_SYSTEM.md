# Design System

> Source of truth: `bsa-web/tailwind.config.ts` + `bsa-web/app/assets/css/main.css`.
> This documents what exists today and what to converge on.

## Brand Foundations

- **Identity**: energetic, disciplined, community sports academy. Badminton first.
- **Tagline**: *Train Harder. Move Faster. Grow Stronger.*
- **Design principles** (adopted, not copied, from Nike/Apple/Olympic-tier brands): confident typography, generous whitespace, one accent color used sparingly, motion that implies athletic speed, photography over decoration.

## Color

| Token | Value | Use |
|---|---|---|
| `canvas` | `#FFFFFF` | Page background |
| `surface` / `surface-2` / `surface-3` | `#F7F7F9` / `#EFEFF3` / `#E7E7EE` | Cards, sections, hover |
| `border`, `border-light` | `#DCDCE6`, `#EAEAF0` | Hairlines |
| `ink` | `#0A0A0F` | Primary text; also used as dark section bg |
| `ink-muted` | `#4A4A65` | Secondary text |
| `ink-faint` | `#8A8AA5` | Tertiary text — **fails WCAG AA on white for body sizes; use for decorative/large text only** |
| `accent` (50–900) | `#E8001E` core | BSA red. The single brand accent |
| `energy` | `#FF3B3B` | Secondary red — **overlaps with accent; candidate for removal** |
| `court` | `#1E90FF` | Court blue — currently almost unused; either adopt deliberately (e.g. links/secondary CTAs) or remove |
| `success` / `error` / `warning` | `#22C55E` / `#EF4444` / `#F59E0B` | Feedback |

**Rule**: accent red is for emphasis and CTAs only. If everything is red, nothing is.

## Typography

- **Display**: Bebas Neue (`font-display`), uppercase, tight tracking — headlines, stats, section titles.
- **Body/UI**: Inter (`font-sans`), weights 300–700.
- Type scale: `2xs` (11px) → `8xl` (128px) with tuned line-heights (see config).

### ⚠️ Known defect: orphaned serif classes
`main.css` defines `.text-display-*` / `.text-heading-*` with `font-serif` and a comment referencing **Playfair Display — a font this project does not load** (leftover from the demo this repo was forked from). Any usage falls back to the browser serif and looks broken. **Fix**: repoint these classes to `font-display` (Bebas) or delete them; pages currently style headings ad-hoc with `font-display text-3xl…` instead.

**Convergence rule**: headings use the utility classes (once fixed), not ad-hoc stacks; the eyebrow-label + heading pattern becomes `SectionHeading.vue` (see [COMPONENTS.md](COMPONENTS.md)).

## Spacing & Layout

- Tailwind default scale + `18`, `88`, `128` extensions.
- Layout helpers: `.section-padding` (`py-16 lg:py-24`), `.section-container` (`max-w-7xl px-4 lg:px-8`). Use these — do not hand-roll section wrappers.
- Breakpoints: `sm 480 / md 768 / lg 1024 / xl 1280 / 2xl 1440`.

## Radius & Elevation

- Radius: `sm 4 / DEFAULT 8 / md 12 / lg 16 / xl 20 / 2xl 28`. Cards standardize on `rounded-2xl`; buttons on `rounded-xl`.
- Elevation is currently ad-hoc (`shadow-xl shadow-accent/5`, etc.). Define 3 named levels (rest / raised / overlay) and stop inventing per-component shadows.

## Motion Tokens

- Durations: `fast 120ms / base 200ms / slow 320ms / enter 280ms / exit 180ms`.
- Easings: `ease-out cubic-bezier(0.16,1,0.3,1)` (signature), `ease-in`, `spring`.
- Keyframes registered: `shuttle-fly`, `pulse-glow`, `float`, `slide-up`, `count-up`, `scroll-x`, `fade-in`, `fade-in-up`.
- Usage rules live in [ANIMATION_GUIDELINES.md](ANIMATION_GUIDELINES.md).

## Components (canonical set)

Buttons (`AppButton` variants/sizes), inputs (`AppInput`, `OTPInput`), overlays (`AppModal`, `AppSheet`), feedback (`AppToast`, `AppSpinner`, `AppSkeleton`, `AppBadge`). Gaps and copy-paste patterns to promote into components are listed in [COMPONENTS.md](COMPONENTS.md#reusable-patterns-that-exist-only-as-copy-paste).

## Iconography

- Functional icons: **lucide-vue-next**, default `h-4 w-4`/`h-5 w-5`, `text-accent` when decorative-emphasis.
- Brand glyphs: `@nuxt/icon` with `simple-icons` collection.
- Emoji icons (🏸 💪 ♨️ in `constants.ts` / DB rows) read as unpolished — replace with lucide or custom SVG when touching those surfaces.

## Recommended Next Steps

1. Fix the serif heading classes (5-minute change, visible bug).
2. Decide the fate of `energy` and `court` colors; document link color.
3. Add named elevation levels; audit shadows to them.
4. Real photography of the actual facility to replace Unsplash placeholders — the single highest-impact brand upgrade available (see [CONTENT_STRATEGY.md](CONTENT_STRATEGY.md)).
5. Replace emoji icons in DB-seeded facility rows.
