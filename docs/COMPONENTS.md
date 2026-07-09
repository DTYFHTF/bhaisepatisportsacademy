# Components

> Inventory of the frontend component hierarchy, reusable patterns, and gaps.
> Paths relative to `bsa-web/app/`.

## Layout Shell

`app.vue` renders: `NuxtLoadingIndicator` → `LayoutAppHeader` → `<NuxtPage>` → `LayoutAppFooter` → `LayoutCartDrawer` → `LayoutBookingDrawer` → `UiAppToast`.

| Component | Role | Notes |
|---|---|---|
| `layout/AppHeader.vue` | Sticky nav, logo, cart/booking badges, Book Court CTA | Nav links hardcoded; includes **Shop** and **Kitchen** (demo-era decisions) |
| `layout/MobileNav.vue` | Mobile slide-down menu | |
| `layout/AppFooter.vue` | Footer | |
| `layout/CartDrawer.vue` | Shop cart side panel | e-commerce flow |
| `layout/BookingDrawer.vue` | Court-booking side panel | core academy flow |

## UI Primitives (`components/ui/`)

`AppButton`, `AppInput`, `AppModal`, `AppSheet`, `AppSkeleton`, `AppSpinner`, `AppToast`, `AppBadge`, `OTPInput`.

These are the design-system building blocks. `AppModal`/`AppSheet` are the only consumers of `@vueuse/motion`.

## Domain Components

| Domain | Components | Status |
|---|---|---|
| Checkout | `ContactStep`, `DeliveryStep`, `PaymentStep`, `OrderSummary`, `LocationPicker` | e-commerce flow (shop + kitchen orders) |
| Product | `ProductCard`, `ProductGrid`, `ProductGallery`, `SizeSelector`, `ColorSelector`, `RelatedProducts`, **`FabricStory`** | `FabricStory` is a leftover from the clothing/beauty demo this repo was forked from |
| Service | `ServiceCard`, `ServiceGrid` | beauty-demo leftover; `/services` page is not in the nav |
| Tracking | `DeliveryMap`, `OrderTimeline`, `OrderItems`, `ExchangeForm` | order tracking flow |

## Stores, Composables, Utilities

- **Stores**: `stores/cart.ts`, `stores/booking.ts`, `stores/checkout.ts` (persisted).
- **Composables**: `useSettings` (site settings from API), `useToast`, `useOTP`, `useGoogleMaps`, `useRecentlyViewed`, `useUmami`.
- **Utils**: `utils/formatters.ts` (NPR/date/phone), `utils/animations.ts`, `utils/constants.ts` (383 lines — brand info **plus** hardcoded copies of programs/facilities/schedule/kitchen data that also live in the DB).
- **Dead code**: `utils/constants.old.ts` is an **empty file** — delete.

## Reusable Patterns That Exist Only as Copy-Paste

These appear repeatedly (mostly in `pages/index.vue`, 626 lines) and should become components before further page work:

1. **Section heading** — eyebrow label (`text-xs uppercase tracking-[0.2em] text-accent`) + display heading. Repeated ~6× on the homepage alone. → `SectionHeading.vue`.
2. **Ghost/outline hero button** — raw `<button>` with `border-white/30 bg-white/10 backdrop-blur` markup duplicated in hero and CTA sections, bypassing `AppButton`. → add a `ghost-inverse` variant to `AppButton`.
3. **Image card with gradient overlay + hover zoom** — facilities grid, program cards, gallery tiles all re-implement it. → `MediaCard.vue`.
4. **Feature list with red dot bullets** — repeated in facility and program cards. → small `FeatureList.vue` or a CSS utility.
5. **Stat counter** — the IntersectionObserver + rAF counter logic (~50 lines in `index.vue` script) should be a `useCountUp` composable or `StatCounter.vue`.
6. **Testimonial form** — inline in `index.vue` with its own field markup instead of `AppInput`. Extract `TestimonialForm.vue`.

## Demo Leftovers

Inventory of code inherited from the pre-BSA demo (decide keep/remove per [ROADMAP.md](ROADMAP.md) Phase 1; confirm with owner before deleting):

- **Frontend**: `FabricStory.vue`, `ServiceCard/ServiceGrid` + `pages/services.vue`, `types/product.ts` size/color machinery, `useRecentlyViewed`, shop pages with placeholder inventory, `constants.old.ts` (empty).
- **Backend**: enums `WaxType`, `WardrobeRole`, `Size`, `ServiceCategory`; models `Look`, `LookItem`; `StyleExplanationService`; migrations named for the beauty pivot; `LookSeeder`; demo `ProductSeeder` data (currently **live on production** — a Yonex racket at NPR 8,500 is purchasable).
- **Content**: Unsplash placeholder images (`utils/constants.ts` `IMAGES`), Pixabay hero videos hotlinked in `index.vue`, seeded stats ("6 Years Experience") and testimonials whose factual accuracy is unverified.

## Conventions

- Components are namespaced by directory (`LayoutAppHeader`, `UiAppButton`) via Nuxt auto-import.
- Icons: `lucide-vue-next` (tree-shaken per-icon imports) — keep; avoid adding a second icon system for the same purpose (`@nuxt/icon` is used only for `simple-icons` brand glyphs).
- New components must: use design tokens (no hex literals), accept `class` passthrough, and keep data-fetching out of presentational components.
