# 03 — Information Architecture

## Sitemap

```
/ (Homepage)
│
├── /shop
│   ├── /shop/jackets
│   ├── /shop/tops
│   ├── /shop/bottoms
│   └── /shop/new-arrivals
│
├── /p/[slug]                   ← Product Detail Page
│
├── /wardrobe                   ← AI Wardrobe Builder
│   └── /wardrobe/[id]          ← Saved look (shareable)
│
├── /look/[id]                  ← Sharable combination (public)
│
├── /track                      ← Order tracking (login-free)
│   └── /track?token=[jwt]      ← Direct tracking link from SMS
│
├── /story                      ← About BSA
│
├── /care                       ← Fabric care guide
│
├── /sizing                     ← Size guide (static)
│
├── /faq                        ← FAQ
│
├── /shipping                   ← Shipping & returns policy
│
├── /contact                    ← Contact (WhatsApp / email)
│
└── /admin                      ← Admin panel (protected)
    ├── /admin/orders
    ├── /admin/products
    ├── /admin/products/new
    ├── /admin/products/[id]
    ├── /admin/analytics
    └── /admin/restock
```

---

## URL Design

### Product URLs
`/p/[slug]` — short, readable, SEO-friendly

**Slug construction:** `[style-name]-[color]` (lowercase, hyphened)

Examples:
- `/p/field-jacket-olive`
- `/p/field-jacket-black`
- `/p/essential-tee-sand`
- `/p/cargo-trousers-charcoal`

**Rule:** Never include category, season, or SKU in the URL. These change; the slug must be permanent.

### Category URLs
`/shop/[category]` — flat category structure

| URL | Category |
|---|---|
| `/shop/jackets` | All outerwear |
| `/shop/tops` | Tees and shirts |
| `/shop/bottoms` | Trousers, joggers |
| `/shop/new-arrivals` | Dynamic — latest 20 products |

No subcategories in Phase 1. Collection pages added in Phase 3.

### Order Tracking
`/track?token=[jwt]` — stateless, no login

JWT contains: `{ orderId, phone (hashed), exp }`

---

## Navigation Structure

### Primary Navigation (Desktop)

```
[BSA]                    [Shop] [Wardrobe] [Story] [Track Order]   [🛒 2]
```

### Primary Navigation (Mobile)

Top bar:
```
[☰]        [BSA]        [🛒 2]
```

Bottom drawer menu (slides in from left):
```
Shop
 ↳ Jackets
 ↳ Tops
 ↳ Bottoms
 ↳ New Arrivals
Wardrobe
Story
Track Order
──────────
Care
FAQ
Shipping
Contact
```

### Footer

```
Column 1: BSA
  © 2025 · Kathmandu, Nepal
  Instagram · WhatsApp

Column 2: Quick Links
  Shop · Wardrobe · Story
  New Arrivals

Column 3: Support
  Track Order · FAQ
  Shipping & Returns · Contact

Column 4: The Brand
  About BSA · Care Guide
  Size Guide
```

---

## Content Model

### Product

| Field | Type | Notes |
|---|---|---|
| `id` | UUID | Internal only |
| `slug` | String | Permanent, URL slug |
| `name` | String | e.g., "Field Jacket" |
| `colorName` | String | e.g., "Olive" |
| `price` | Number | NPR |
| `compareAtPrice` | Number? | Original price (if on sale) |
| `category` | Enum | `JACKET \| TOP \| BOTTOM \| ACCESSORY` |
| `description` | Text | Short product description (1–3 sentences) |
| `fabricStory` | Text | Longer fabric + care narrative (accordion content) |
| `images` | Image[] | Ordered: front, back, detail, worn |
| `variants` | Variant[] | Size + stock quantity |
| `tags` | String[] | e.g., `["new-arrival", "bestseller"]` |
| `isActive` | Boolean | Hidden if false |
| `seoTitle` | String | Optional override |
| `seoDescription` | String | Optional override |

### Variant

| Field | Type | Notes |
|---|---|---|
| `id` | UUID | |
| `productId` | UUID | FK to Product |
| `size` | Enum | `XS \| S \| M \| L \| XL \| XXL` |
| `sku` | String | e.g., `DH-FJ-OLV-M` |
| `stock` | Int | Current quantity |
| `reservedStock` | Int | Reserved during checkout (15 min) |

### Order

See full schema in [11-database-schema.md](11-database-schema.md).

---

## Search Architecture

### Phase 1 (Launch): Client-side fuzzy search with Fuse.js

- Product catalog exported as JSON at build time
- Fuse.js loaded lazily when search is triggered
- Searches: name, colorName, description, tags
- Weights: name (1.0), tags (0.7), description (0.4)
- Results appear in a floating dropdown under the search bar
- Max results: 8

### Search Index Structure

```json
{
  "slug": "field-jacket-olive",
  "name": "Field Jacket",
  "colorName": "Olive",
  "description": "A four-pocket work jacket...",
  "tags": ["jacket", "outerwear", "olive", "new-arrival"],
  "price": 5500
}
```

### Phase 3+: Algolia or Typesense

When catalog exceeds 100 products, migrate to a hosted search solution.

---

## Page Hierarchy & Rendering Strategy

| Page | Rendering | Revalidation |
|---|---|---|
| Homepage (`/`) | ISR | 1 hour |
| Shop page (`/shop`) | ISR | 1 hour |
| Category page | ISR | 1 hour |
| Product page | ISR | 10 minutes |
| Wardrobe page | SSG | On deploy |
| Order tracking | SSR | Per request |
| Story page | SSG | Manual |
| FAQ, Sizing, Care | SSG | Manual |
| Admin pages | CSR (client-only) | N/A |

---

## Metadata Strategy

### Default Metadata (layout.tsx)

```
title: "Bhaisepati Sports Academy — Train Harder, Move Faster, Grow Stronger"
description: "Badminton training, gym, and recovery programs in Bhaisepati, Nepal. Enroll today."
og:image: /og-default.jpg
og:type: website
twitter:card: summary_large_image
```

### Product Page Metadata

```
title: "[Product Name] in [Color] — Bhaisepati Sports Academy"
description: "{fabricStory, first 155 chars}"
og:image: {product.images[0].cloudinaryUrl}
og:type: product
product:price:amount: {price}
product:price:currency: NPR
```

### Dynamic OG Images

Generated via Vercel OG (`@vercel/og`) — product image + name + price overlaid on brand-template background. Renders at 1200×630px.

---

## Redirects

| From | To | Reason |
|---|---|---|
| `/products/[slug]` | `/p/[slug]` | Old URL format |
| `/collections/all` | `/shop` | Old URL format |
| `/order/[id]` | `/track?order={id}` | Old tracking URL |

All redirects: 301 permanent.
