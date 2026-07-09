# Accessibility

> Target: WCAG 2.1 AA on all public pages. Audit of 2026-07-09.

## Working Today ✅

- Global `:focus-visible` outline (accent, 2px offset) in `main.css`.
- `NuxtRouteAnnouncer` for SPA route changes.
- `aria-label` on icon-only buttons (menu, cart, booking); `aria-live="polite"` on cart/booking count badges.
- Landmarks: `<header>`, `<nav aria-label>`, `<main>`, `<footer>`.
- Form labels present on the testimonial form; decorative images use empty `alt`.
- `loading.vue` respects `prefers-reduced-motion`.

## Gaps (ordered by severity)

### A1 — Motion ignores `prefers-reduced-motion` (except the loader)
The `v-scroll` reveal system, hero parallax, autoplaying hero video, marquee (`scroll-x`), counters and pulse animations all run regardless of OS setting.
**Fix**: one global CSS block —
```css
@media (prefers-reduced-motion: reduce) {
  [data-scroll] { opacity: 1 !important; transform: none !important; transition: none !important; }
  *, ::before, ::after { animation-duration: 0.01ms !important; animation-iteration-count: 1 !important; }
}
```
plus: skip parallax listener and pause/hide the hero video when the media query matches (see [ANIMATION_GUIDELINES.md](ANIMATION_GUIDELINES.md)).

### A2 — Scroll-reveal content is `opacity: 0` until JS runs
If JS fails or an observer never fires, `[data-scroll]` content stays invisible. After prerendering (see [SEO.md](SEO.md)) this worsens: HTML exists but is hidden.
**Fix**: apply the hidden state only when JS is present (e.g. add a `js` class on `<html>` in a tiny inline script and scope `[data-scroll]` styles under it).

### A3 — Autoplaying background video
No pause control, no reduced-motion gate. WCAG 2.2.2 requires a way to stop moving content that lasts > 5s. **Fix**: reduced-motion gate + a small pause toggle (or drop the video — see PERFORMANCE P1).

### A4 — Contrast
- `ink-faint` (#8A8AA5) on white ≈ 3.6:1 — fails AA for normal text; restrict to large/decorative.
- White/70 and white/50 text over photos (hero, gallery, CTA) depends on the image behind it; the gradient overlays mostly save it, but verify with real photos.
- Accent red on white (#E8001E) ≈ 4.6:1 — passes AA for normal text; `accent-light` does not — keep it off text.

### A5 — Keyboard & focus management in overlays
`AppModal`, `AppSheet`, `CartDrawer`, `BookingDrawer`, `MobileNav` need verification: focus trap, `Esc` to close, focus return to trigger, `aria-modal`/`role="dialog"`, and inert background. (Not yet verified — treat as an audit task, likely partial.)

### A6 — Forms
Homepage testimonial form: errors are visual-only (`text-error` paragraph) — associate via `aria-describedby` and use `aria-invalid`; the disabled-submit pattern gives no announced reason. Apply the same standard to booking/checkout/track forms, which use multi-step flows (announce step changes).

### A7 — Misc
- Add a skip-to-content link before the header.
- `html { scroll-behavior: smooth }` should also sit behind the reduced-motion query.
- Uppercase Bebas/tracking-wide text at small sizes hurts readability — keep labels ≥ 12px and don't set body copy in display face.
- Emoji used as icons (🏸 💪 ♨️ from DB) are announced literally by screen readers — replace with SVG + `aria-hidden`.

## Process

- Every PR touching UI: keyboard pass (tab through), one screen-reader spot check (VoiceOver), and axe DevTools scan on changed pages.
- Add `eslint-plugin-vuejs-accessibility` when linting is introduced (see [CONTRIBUTING.md](CONTRIBUTING.md)).
