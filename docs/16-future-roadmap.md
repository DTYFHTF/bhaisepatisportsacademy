# 16 — Future Roadmap

## Overview

Bhaisepati Sports Academy is built in phases. Each phase is shippable and complete on its own. No phase requires the next to exist. Revenue from each phase funds the next.

---

## Phase 0 — Foundation (Weeks 1–4)

**Goal:** A working e-commerce site that can take orders and get paid.

### Tasks

**Infrastructure**
- [ ] Set up Next.js 14 project with TypeScript
- [ ] Configure Tailwind CSS with design tokens
- [ ] Set up Supabase PostgreSQL
- [ ] Configure Prisma schema and migrations
- [ ] Deploy to Vercel with custom domain
- [ ] Set up Cloudinary account and upload workflow
- [ ] Configure Sparrow SMS account with branded sender

**Core Shopping**
- [ ] Product listing page (`/shop`)
- [ ] Product detail page (`/p/[slug]`)
- [ ] Image gallery (swipe on mobile)
- [ ] Size selector
- [ ] Cart (Zustand + localStorage)
- [ ] Cart drawer

**Checkout**
- [ ] 3-step checkout form (Contact/OTP → Delivery → Payment)
- [ ] OTP send + verify
- [ ] COD order placement
- [ ] Order confirmation page
- [ ] Confirmation SMS with tracking link

**Tracking**
- [ ] Order status page (`/track`)
- [ ] Short tracking token (`/t/[token]`)
- [ ] Order status timeline

**Admin (minimal)**
- [ ] Admin login (Supabase Auth)
- [ ] Order list view
- [ ] Order detail + status update
- [ ] Manual stock update

**Content**
- [ ] Homepage with hero + new arrivals
- [ ] About page (`/story`)
- [ ] Size guide page
- [ ] Care guide page
- [ ] FAQ page

### Launch Checklist

- [ ] All 77 Nepal districts in delivery dropdown
- [ ] Delivery pricing rules (KTM Valley vs. outside)
- [ ] At least 3 products with full images loaded
- [ ] Test OTP flow end-to-end with Sparrow SMS
- [ ] Test COD order flow end-to-end
- [ ] Mobile layout tested on iPhone SE (375px) and Samsung Galaxy A52 (360px)
- [ ] Lighthouse score ≥ 95 on product page
- [ ] robots.txt and sitemap.xml configured
- [ ] HTTPS + www redirect active
- [ ] Custom 404 page
- [ ] Privacy policy page (linked in footer)

---

## Phase 1 — Payment & Growth (Weeks 5–8)

**Goal:** Accept digital payments. Reduce COD dependency.

### Tasks
- [ ] Khalti payment integration
- [ ] eSewa payment integration
- [ ] Payment failure recovery flow (cart preserved)
- [ ] Payment confirmation SMS
- [ ] Admin: payment status indicators
- [ ] Restock alert registration
- [ ] Admin: trigger restock SMS batch
- [ ] Product pairs ("Complete the look")
- [ ] "New Arrival" badge + category filter
- [ ] Search (Fuse.js client-side)
- [ ] Recently viewed products (localStorage)

### KPIs to hit before Phase 2
- 50 orders placed
- Digital payment adoption > 40%
- Return visitor rate > 20%

---

## Phase 2 — AI Sizing + Wardrobe (Weeks 9–14)

**Goal:** Differentiate from generic stores with smart features.

### Tasks
- [ ] Size Finder modal (heuristic engine)
- [ ] AI size suggestion on product page
- [ ] Measurements saved to localStorage
- [ ] Wardrobe Builder (`/wardrobe`)
- [ ] Color harmony + look generation engine
- [ ] Look share URL (`/look/[hash]`)
- [ ] Shareable look page with OG image
- [ ] Open Graph image generation (Vercel OG)
- [ ] Saved wardrobe (phone-linked, `/w/[hash]`)
- [ ] Limited drop countdown timer
- [ ] Drop archive pages

### KPIs to hit before Phase 3
- Wardrobe Builder usage: > 15% of product page visitors
- Look sharing: > 5% of wardrobe sessions
- Average order value increase: > 10% (from wardrobe upsell)

---

## Phase 3 — Intelligence & Operations (Months 4–6)

**Goal:** Let data make decisions. Reduce manual admin work.

### Tasks
- [ ] Order analytics dashboard (revenue, top products, geographic distribution)
- [ ] Automatic delivery fee calculation (courier API integration)
- [ ] Courier dispatch tracking (webhook from Blue Dart / Pathao)
- [ ] 30-minute priority window for restock subscribers
- [ ] GPT-4o-mini style explanations for Wardrobe Builder
- [ ] Style affinity engine (session-based product sorting)
- [ ] Email collection opt-in (for drops newsletter)
- [ ] Admin: product bulk upload via CSV
- [ ] Admin: two-factor authentication
- [ ] Exchange: automated collection scheduling

---

## Phase 4 — Scale & Community (Months 7–12)

**Goal:** Turn customers into community members.

### Tasks
- [ ] Lookbook editorial page (`/look`) — tap dots on photos to add to cart
- [ ] Customer reviews (phone-verified, post-delivery prompt)
- [ ] "Styled by customers" UGC section on product pages
- [ ] Gift cards
- [ ] Referral program (share code, get NPR 200 off next order)
- [ ] Multi-admin with roles (admin + packer + fulfillment)
- [ ] Inventory history and forecasting
- [ ] Return portal (self-service)
- [ ] Loyalty points system (simple: 1% back as store credit)

---

## Phase 5 — International (Year 2)

**Goal:** Sell to Nepali diaspora internationally.

### Tasks
- [ ] Stripe payment integration (international cards)
- [ ] USD + NPR dual currency display
- [ ] International shipping calculator
- [ ] DHL / FedEx integration for international fulfillment
- [ ] Multi-language: English + Nepali (i18n via `next-intl`)
- [ ] React Native mobile app (iOS + Android)

---

## Phase 6 — Platform (Year 2–3)

**Goal:** BSA becomes a platform, not just a store.

### Tasks
- [ ] Multi-brand capability: onboard 2–3 other Nepali minimal brands
- [ ] Shared wardrobe engine across brands
- [ ] Marketplace for independent Nepali designers
- [ ] Wholesale portal for retailers

---

## Weekly Metrics Checklist

Review these every Monday morning:

| Metric | Tool | Target |
|---|---|---|
| Orders placed | Admin dashboard | ↑ week-over-week |
| Revenue | Admin dashboard | ↑ week-over-week |
| Product page conversion rate | Plausible | > 3% |
| Cart abandonment rate | Plausible funnel | < 60% |
| Checkout completion rate | Plausible funnel | > 70% |
| Digital payment share | Admin | > 50% |
| Top traffic source | Plausible | Track Instagram share |
| Page load time (LCP) | Vercel Speed Insights | < 1.8s |

---

## Monthly Strategic Review

Every month:

1. **Product:** What's selling? Reorder. What's not? Understand why.
2. **Operations:** Where are orders being cancelled or exchanged? Fix the cause.
3. **Experience:** Any friction points found from customer messages? Ship a fix.
4. **Growth:** What drove the most orders this month? Double down.
5. **Technical debt:** Any shortcuts taken? Schedule cleanup in the next sprint.

---

## Guiding Principle for Roadmap

> *Ship the smallest thing that moves the needle. Measure. Ship the next thing.*

No feature is added because it seems cool. Every feature is added because it solves a real problem or captures a real opportunity — measured by data, not intuition.

The roadmap is a compass, not a contract.
