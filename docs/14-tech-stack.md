# 14 — Tech Stack

## Decision Framework

Every technology was selected against four criteria for a lean, Phase 1 product:

1. **Minimal operational overhead** — managed services where possible
2. **Free or near-free at low volume** — < NPR 5,000/month at launch
3. **Best-in-class for the use case** — not a "good enough" compromise
4. **Well-documented with a strong community** — easy to hire or self-learn

---

## Full Stack Map

```
Browser
  └── Nuxt 3 (Vue 3 — SSG / SSR / SPA modes)
        ├── Vue 3 (Composition API)
        ├── Tailwind CSS (styles)
        ├── @vueuse/motion (animations)
        └── Pinia (state management)

Laravel 11 (API Server)
  ├── Eloquent ORM
  │     └── PostgreSQL (Supabase)
  ├── Laravel Sanctum (API auth)
  ├── Khalti integration (payment)
  ├── eSewa integration (payment)
  ├── Sparrow SMS API (Nepal SMS)
  ├── Spatie Media Library (image handling)
  ├── Cloudinary SDK (image CDN)
  └── Laravel Rate Limiting (built-in)

Filament 3 (Admin Panel — built on Laravel)
  ├── Product resource (CRUD + image upload)
  ├── Order resource (status management)
  ├── Inventory management
  └── Analytics widgets

Infrastructure
  ├── Vercel (Nuxt frontend hosting)
  ├── Railway / Render (Laravel API hosting)
  ├── Supabase (Postgres + connection pooling)
  └── Cloudinary (image CDN)

Monitoring
  ├── Plausible Analytics
  └── Laravel Telescope (local dev debugging)
```

---

## Technology Decisions

### Frontend Framework: Nuxt 3

**Why:** Nuxt is the Vue.js equivalent of Next.js — it delivers SSG, SSR, and ISR-equivalent rendering (`nuxt generate` + Nitro server) with file-based routing and server API routes. Vue 3's Composition API is ergonomically excellent and the ecosystem is mature.

**Alternatives considered:**
- Next.js (React): Excellent, but the team's preference is Vue — ruled out
- Astro: Great for static; awkward for cart/checkout interactivity — ruled out

**Note:** Framer Motion is React-only and **does not work with Nuxt/Vue**. Replaced by `@vueuse/motion`.

### Animation: @vueuse/motion

**Why:** Near-identical API to Framer Motion, built for Vue 3. Handles entrance animations, cart drawer slides, and page transitions. Zero-cost, 4KB gzipped.

For complex gesture-based interactions (swipe-to-dismiss cart on mobile), supplement with pure CSS transitions or GSAP if needed.

### State Management: Pinia

**Why:** The official Vue 3 state management library. Replaces Zustand. Same `persist` plugin available for localStorage cart. DevTools integration built-in.

### Backend: Laravel 11

**Why:** Laravel is a mature, batteries-included PHP framework. For a decoupled Nuxt + API architecture, Laravel handles all backend concerns — OTP, payment callbacks, SMS, order service, rate limiting — with elegant, readable code and no infrastructure setup.

**Architecture:** Nuxt frontend makes REST API calls to Laravel. CORS configured in `config/cors.php`. Laravel Sanctum handles token-based API authentication.

**Alternatives considered:**
- Node.js/Express: More familiar in JS ecosystem but more boilerplate, less out-of-the-box
- Django: Good but PHP/Laravel is a stronger fit for traditional e-commerce patterns

### Admin Panel: Filament 3

**Why:** Filament is a first-class admin panel builder for Laravel. It replaces the entire custom admin panel without writing a single HTML/CSS line. Provides:
- Product CRUD with drag-drop image reordering
- Order management with status workflows
- Inventory table with inline editing
- Analytics widgets (built-in charts)
- Role-based access out of the box

This is a massive development time saving — the custom admin panel (12-admin-panel.md) was one of the most complex things to build; Filament delivers 90% of it configured, not coded.

### Database: PostgreSQL via Supabase

**Why PostgreSQL over SQLite:**

SQLite is a file-based database — fine for a personal project or read-heavy blog. For BSA it fails at one critical point: **inventory reservation during checkout**.

When two customers hit "Place Order" simultaneously during a drop launch:
- SQLite uses file-level locking → the second transaction waits → timeouts → race condition
- PostgreSQL uses row-level locking → both transactions proceed on different rows → correct stock deduction

PostgreSQL also handles concurrent writes cleanly during drops when 30–50 people buy at launch time, supports JSONB columns for product tags and image arrays, and scales to millions of rows without file-size concerns.

Supabase free tier: 500MB storage, 50K rows, PgBouncer connection pooling (required for serverless). No server to manage.

### ORM: Eloquent (Laravel built-in)

**Why:** No choice needed — Eloquent is Laravel's built-in ORM. Replaces Prisma. Migrations are version-controlled PHP files. Relationships are defined as methods on model classes. Type safety via PHPDoc / Laravel IDE Helper.

### Image CDN: Cloudinary

**Why:** Unchanged from original spec. Free tier covers launch needs. Spatie Media Library (Laravel package) integrates directly for backend upload handling.

### Payments: Khalti + eSewa

**Why:** Unchanged. The two dominant mobile wallets in Nepal. COD remains essential.

**Stripe:** Not available for Nepal-issued cards as a merchant. Deferred to Phase 6.

### SMS: Sparrow SMS

**Why:** Most widely used bulk SMS provider in Nepal with REST API. Branded sender ID (`BSAWears`) available. Laravel HTTP client handles the API call natively.

### AI Features: Rule-Based (Free) + Groq (Free Tier)

**Why no paid AI subscription:** The vast majority of documented "AI" features are rule-based algorithms that require zero AI:
- Smart Sizing = lookup table + height/weight formula
- Wardrobe Builder = hardcoded color harmony rules + role matching
- Style Affinity = client-side JavaScript scoring

The only LLM-dependent feature is natural-language style explanations (Phase 3+). Options:
- **Groq API** — free tier, 14,400 req/day, Llama 3.1 quality
- **Google Gemini** — free tier, 1,500 req/day, Gemini 1.5 Flash
- **Hand-crafted templates** — 30 pre-written explanations keyed by color pair (most brand-consistent, recommended for Phase 1)

### Search: Fuse.js

**Why:** Unchanged. Client-side fuzzy search from a JSON catalog built at deploy time. Zero server cost for < 200 products.

### Analytics: Plausible

**Why:** GDPR-compliant (no cookies, no consent banner). < 1KB script. Clean dashboard. Not blocked by ad blockers unlike GA4.

---

## Package List

### Nuxt 3 Frontend

```json
{
  "dependencies": {
    "nuxt": "^3.0.0",
    "vue": "^3.0.0",
    "@nuxtjs/tailwindcss": "^6.0.0",
    "@vueuse/motion": "^2.0.0",
    "@vueuse/core": "^10.0.0",
    "pinia": "^2.0.0",
    "@pinia-plugin-persistedstate/nuxt": "^1.0.0",
    "vee-validate": "^4.0.0",
    "@vee-validate/zod": "^4.0.0",
    "zod": "^3.0.0",
    "fuse.js": "^7.0.0",
    "date-fns": "^2.30.0",
    "lucide-vue-next": "^0.300.0"
  }
}
```

### Laravel 11 Backend

```json
{
  "require": {
    "laravel/framework": "^11.0",
    "laravel/sanctum": "^4.0",
    "filament/filament": "^3.0",
    "spatie/laravel-medialibrary": "^11.0",
    "cloudinary-labs/cloudinary-laravel": "^2.0",
    "guzzlehttp/guzzle": "^7.0"
  }
}
```

---

## Cost Estimate

### Year 1 (< 500 orders/month)

| Service | Plan | Cost |
|---|---|---|
| Vercel (Nuxt frontend) | Hobby | $0 |
| Railway / Render (Laravel API) | Starter | $0–$5/mo |
| Supabase (PostgreSQL) | Free tier | $0 |
| Cloudinary | Free (25 credits) | $0 |
| Plausible | Starter (10K pageviews/mo) | $9/mo |
| Domain | bsa.example.com | ~$15/year |
| Sparrow SMS | Per SMS (~NPR 0.55/SMS) | ~NPR 500–2,000/mo |
| AI (Groq/Gemini) | Free tier | $0 |
| **Total** | | **~$9–14/mo** |

### Year 2 (Scaling)

| Service | Plan | Cost |
|---|---|---|
| Vercel | Pro | $20/mo |
| Railway (Laravel) | Pro | $20/mo |
| Supabase | Pro | $25/mo |
| Cloudinary | Plus | $89/mo |
| Plausible | Growth | $19/mo |
| Sparrow SMS | Volume | ~NPR 3,000–5,000/mo (~$25/mo) |
| **Total** | | **~$173/mo + SMS** |

---

## What's Deliberately Not Included

| Tool | Why not |
|---|---|
| Docker (local dev) | Laravel Sail handles it; optional |
| Kubernetes | Massively overengineered for this scale |
| Prisma | Eloquent is built into Laravel — no additional ORM needed |
| Redis for sessions | Laravel's built-in rate limiter + file/DB cache is sufficient at launch |
| Elasticsearch | Fuse.js is sufficient for < 200 products |
| Microservices | Single Laravel app + Nuxt frontend is simpler to develop and debug |
| GraphQL | REST is simpler, better cached, and Filament works natively with REST/Eloquent |
| React Native app | Mobile web covers 95% of need; native app in Phase 5 |
| Paid AI (OpenAI) | Phase 1–2 features are rule-based; Phase 3+ uses Groq/Gemini free tier |
