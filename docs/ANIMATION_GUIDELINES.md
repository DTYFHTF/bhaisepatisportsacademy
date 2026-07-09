# Animation Guidelines

> Motion should feel like the sport: quick, precise, never fussy. If an animation doesn't communicate hierarchy, feedback, or energy, cut it.

## Current Implementation

Three coexisting systems (one too many):

1. **`v-scroll` directive** (`plugins/scroll-reveal.client.ts`) + CSS in `main.css` — IntersectionObserver-driven reveals: `fade-up/down/left/right`, `scale-in`, `zoom-in`, with per-element ms delays (`v-scroll:[150]`). This is the workhorse. ✅ Keep as the standard.
2. **Tailwind keyframes** (`tailwind.config.ts`) — `fade-in-up` (hero entrance), `pulse-glow`, `float`, `scroll-x` marquee, `shuttle-fly` (unused?), `count-up`. Partially used.
3. **`@vueuse/motion`** — only in `AppModal`/`AppSheet`. Candidate for removal in favor of Vue `<Transition>` + tokens (saves a dependency; see PERFORMANCE P4).

Plus bespoke JS: hero parallax (scroll listener), stat count-up (rAF).

## Rules

1. **Respect `prefers-reduced-motion` — currently not honored.** This is the #1 motion fix (see [ACCESSIBILITY.md](ACCESSIBILITY.md) A1). All guidance below assumes the global reduced-motion kill switch is in place.
2. **Animate only `transform` and `opacity`.** Never top/left/width/height/box-shadow loops on scroll.
3. **Use the tokens**: durations `fast 120 / base 200 / slow 320 / enter 280 / exit 180`; signature easing `cubic-bezier(0.16,1,0.3,1)`. Scroll reveals: 0.7s with the signature easing (already standardized in CSS).
4. **One reveal per element, once.** Observers unobserve after reveal (already correct). No re-triggering on scroll-up.
5. **Stagger caps**: 150ms steps, max ~4 items (600ms). Longer lists reveal as a block.
6. **Distance discipline**: reveals travel ≤ 40px. Anything bigger reads as decoration, not confidence.
7. **`will-change` only where measured** — it's currently on every `[data-scroll]` element; acceptable at today's scale, revisit if reveal count grows.

## Motion Vocabulary (approved patterns)

| Pattern | Where | Spec |
|---|---|---|
| Hero entrance | h1/subhead/CTAs on load | `fade-in-up` cascade, 0.1–0.7s delays (exists) |
| Scroll reveal | section headings, cards | `v-scroll` fade-up, stagger ≤ 4 |
| Image reveal | media cards | `scale-in`; imagery zooms `scale-105/110` over 700ms on hover |
| Micro-interactions | buttons, links | 120–200ms color/transform; buttons may press `scale-[0.98]` on active |
| Count-up stats | stats band | rAF ease-out cubic, 2s, once (exists; extract to `useCountUp`) |
| Overlay enter/exit | modal, sheet, drawers | 280ms in / 180ms out, fade + 20px translate |
| Page transitions | route changes | **Not implemented.** When added: 150ms fade-out / 200ms fade-in via Nuxt `pageTransition`; nothing fancier |
| Loading | data fetch | `AppSkeleton` blocks, no spinners for content areas |

## Anti-patterns (remove/avoid)

- Parallax on mobile or under reduced-motion (currently unconditional — fix).
- Infinite attention-seekers (`pulse-glow`, `float`) on more than one element per viewport.
- Autoplay video without a reduced-motion gate and pause affordance.
- Adding GSAP/Lottie/etc. — the current CSS+observer system covers every approved pattern. New dependencies for motion require a documented case.
