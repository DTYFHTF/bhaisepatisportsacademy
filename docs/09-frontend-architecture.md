# 09 — Frontend Architecture

## Stack

| Tool | Version | Role |
|---|---|---|
| Nuxt | 3.x | Full-stack Vue framework (SSG / SSR / SPA) |
| Vue | 3.x | UI (Composition API) |
| Tailwind CSS | 3.x | Utility styles |
| @vueuse/motion | 2.x | Animations (replaces Framer Motion) |
| Pinia | 2.x | Global state (cart) |
| Vee-Validate + Zod | 4.x | Form state + validation |

> **Note:** Framer Motion is React-only and does not work with Vue/Nuxt. `@vueuse/motion` provides near-identical API and covers all animation requirements.

---

## Directory Structure

```
├── nuxt.config.ts                # Nuxt configuration
├── app.vue                       # Root Vue component
├── pages/                        # File-based routing
│   ├── index.vue                 # Homepage
│   ├── shop/
│   │   ├── index.vue             # All products
│   │   └── [category].vue        # Category page
│   ├── p/
│   │   └── [slug].vue            # Product detail page
│   ├── wardrobe/
│   │   ├── index.vue             # Wardrobe Builder
│   │   └── [id].vue              # Saved wardrobe
│   ├── look/
│   │   └── [id].vue              # Shareable look
│   ├── track.vue                 # Order tracking
│   ├── story.vue                 # About page
│   ├── checkout.vue              # Checkout (client-side)
│   └── order/
│       └── confirmed.vue         # Order confirmation
├── components/
│   ├── ui/                       # Primitives
│   │   ├── AppButton.vue
│   │   ├── AppInput.vue
│   │   ├── AppModal.vue
│   │   ├── AppSheet.vue
│   │   ├── AppToast.vue
│   │   ├── AppSpinner.vue
│   │   ├── AppSkeleton.vue
│   │   ├── AppBadge.vue
│   │   └── OTPInput.vue
│   ├── layout/
│   │   ├── AppHeader.vue
│   │   ├── AppFooter.vue
│   │   ├── MobileNav.vue
│   │   └── CartDrawer.vue
│   ├── product/
│   │   ├── ProductCard.vue
│   │   ├── ProductGrid.vue
│   │   ├── ProductGallery.vue
│   │   ├── SizeSelector.vue
│   │   ├── ColorSelector.vue
│   │   ├── FabricStory.vue
│   │   └── RelatedProducts.vue
│   ├── checkout/
│   │   ├── ContactStep.vue
│   │   ├── DeliveryStep.vue
│   │   ├── PaymentStep.vue
│   │   └── OrderSummary.vue
│   ├── ai/
│   │   ├── SizeFinder.vue
│   │   ├── WardrobeBuilder.vue
│   │   ├── LookCard.vue
│   │   └── StyleExplanation.vue
│   └── tracking/
│       ├── OrderTimeline.vue
│       ├── OrderItems.vue
│       └── ExchangeForm.vue
├── composables/                   # Vue composables (equiv. to React hooks)
│   ├── useCart.ts
│   ├── useToast.ts
│   ├── useOTP.ts
│   ├── useSizing.ts
│   └── useRecentlyViewed.ts
├── stores/                        # Pinia stores
│   ├── cart.ts
│   └── checkout.ts
├── utils/
│   ├── cloudinary.ts
│   ├── animations.ts              # @vueuse/motion variants
│   ├── formatters.ts
│   └── constants.ts
├── types/
│   ├── product.ts
│   ├── order.ts
│   └── cart.ts
└── assets/
    └── css/
        ├── main.css               # Tailwind directives
        └── tokens.css             # CSS custom properties
```

---

## Rendering Strategy

| Route | Strategy | Why |
|---|---|---|
| `/` | ISR (1hr) | Dynamic promotions, new arrivals |
| `/shop` | ISR (1hr) | Product availability changes |
| `/shop/[category]` | ISR (1hr) | Same |
| `/p/[slug]` | ISR (10min) | Stock changes must reflect quickly |
| `/wardrobe` | SSG | Static UI, data fetched client-side |
| `/track` | SSR | OTP requires request-time auth |
| `/checkout` | SPA (client-only) | No server state; all form interactions |
| `/story` | SSG | Never changes |

In Nuxt 3, rendering mode is set per-route in `nuxt.config.ts`:
```ts
export default defineNuxtConfig({
  routeRules: {
    '/': { isr: 3600 },
    '/shop/**': { isr: 3600 },
    '/p/**': { isr: 600 },
    '/wardrobe': { prerender: true },
    '/story': { prerender: true },
    '/track': { ssr: true },
    '/checkout': { ssr: false },
  }
})
```

---

## Cart Store (Pinia)

```ts
// stores/cart.ts
import { defineStore } from 'pinia'

interface CartItem {
  productId: string
  slug: string
  name: string
  colorName: string
  size: string
  variantId: string
  price: number
  image: string
  quantity: number
}

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [] as CartItem[],
    isOpen: false,
  }),
  getters: {
    total: (state) => state.items.reduce((sum, i) => sum + i.price * i.quantity, 0),
    itemCount: (state) => state.items.reduce((sum, i) => sum + i.quantity, 0),
  },
  actions: {
    addItem(item: Omit<CartItem, 'quantity'>) {
      const existing = this.items.find(i => i.variantId === item.variantId)
      if (existing) {
        existing.quantity++
      } else {
        this.items.push({ ...item, quantity: 1 })
        this.isOpen = true
      }
    },
    removeItem(variantId: string) {
      this.items = this.items.filter(i => i.variantId !== variantId)
    },
    updateQuantity(variantId: string, quantity: number) {
      if (quantity === 0) return this.removeItem(variantId)
      const item = this.items.find(i => i.variantId === variantId)
      if (item) item.quantity = quantity
    },
    clearCart() { this.items = [] },
    toggleDrawer() { this.isOpen = !this.isOpen },
  },
  persist: true, // via @pinia-plugin-persistedstate/nuxt — uses localStorage
})
```

---

## Animation Reference (@vueuse/motion)

Defined in `utils/animations.ts` — imported into components as motion variants:

```ts
// utils/animations.ts

export const fadeIn = {
  initial: { opacity: 0, y: 8 },
  enter: { opacity: 1, y: 0, transition: { duration: 200 } },
}

export const slideInRight = {
  initial: { x: '100%' },
  enter: { x: '0%', transition: { duration: 280 } },
  leave: { x: '100%', transition: { duration: 180 } },
}

export const scaleIn = {
  initial: { opacity: 0, scale: 0.96 },
  enter: { opacity: 1, scale: 1, transition: { duration: 200 } },
}
```

Usage in Vue components:
```vue
<div v-motion="fadeIn">
  Product content fades in
</div>
```

---

## Lazy Loading

Heavy components loaded on-demand in Nuxt using the built-in `<LazyComponent>` prefix convention:

```vue
<!-- Nuxt auto-imports lazy-loaded versions with Lazy prefix -->
<LazyWardrobeBuilder v-if="showWardrobe" />
<LazySizeFinder v-if="showSizeFinder" />
```

Fuse.js loaded only when search is triggered:
```ts
const { initSearch } = await import('~/utils/search')
```

---

## Environment Variables

```bash
# API
NUXT_PUBLIC_API_BASE=https://api.bsa.example.com

# Cloudinary
NUXT_PUBLIC_CLOUDINARY_CLOUD_NAME=

# Analytics
NUXT_PUBLIC_PLAUSIBLE_DOMAIN=bsa.example.com
```

All server-side secrets (payment keys, SMS tokens) live in the Laravel `.env` — never in the Nuxt frontend.
