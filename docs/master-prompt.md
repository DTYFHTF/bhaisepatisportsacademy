# Bhaisepati Sports Academy — Master Prompt & Project Context

> Use this document to provide a full AI session context for working on the Bhaisepati Sports Academy platform.

---

## Project Overview

**Bhaisepati Sports Academy (BSA)** is a community sports training facility in Bhaisepati, Kathmandu, Nepal. The primary activity is **Badminton** — the owner's passion and the most popular facility at the academy. Secondary facilities include a Gym & Strength Training area and a Sauna & Recovery room.

**Website goal:** A modern, energetic platform for program enrollment, facility showcase, and community engagement.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Frontend | Nuxt 4.3 (Vue 3, SSR disabled), Tailwind CSS |
| State | Pinia + pinia-plugin-persistedstate |
| Validation | vee-validate + zod v3.x |
| Icons | lucide-vue-next |
| Animation | @vueuse/motion (fade-in-up, 200–300ms, no bounce) |
| Backend | Laravel 12, PHP 8.2+, Filament 3 admin |
| Auth | Laravel Sanctum (API), Filament auth (admin panel) |
| Database | SQLite (dev), PostgreSQL (prod) |
| Payments | Khalti, eSewa, COD (walk-in) |
| SMS | Sparrow SMS — OTP verification |
| Images | Cloudinary |
| Analytics | Umami |
| Hosting | cPanel (babal.host), PM2 for Node |
| CI/CD | GitHub Actions → SSH deploy |

---

## Repository Structure

```
bhaisepatisportacademy/
├── .github/
│   ├── copilot-instructions.md     ← Primary AI coding context
│   └── workflows/
│       ├── deploy-api.yml          ← Laravel CI/CD
│       └── deploy-web.yml          ← Nuxt CI/CD
├── bsa-web/                        ← Nuxt 4 frontend
│   ├── app/
│   │   ├── pages/                  ← Route pages
│   │   ├── components/             ← ui/, layout/, program/, enrollment/
│   │   ├── stores/                 ← Pinia stores
│   │   ├── composables/            ← Vue composables
│   │   ├── utils/                  ← constants, formatters, animations
│   │   └── types/                  ← TypeScript types
│   ├── nuxt.config.ts
│   └── tailwind.config.ts
├── bsa-api/                        ← Laravel 12 backend
│   ├── app/
│   │   ├── Filament/               ← Admin resources and widgets
│   │   ├── Http/                   ← Controllers, middleware, requests
│   │   ├── Models/                 ← Eloquent models
│   │   ├── Enums/                  ← PHP enums
│   │   └── Services/               ← Business logic
│   ├── database/migrations/
│   └── routes/api.php
├── docs/                           ← Project documentation (this folder)
└── scripts/
    └── server-setup.sh
```

---

## Core Data Conventions

### Pricing
- All prices stored as **integers in paisa** (NPR × 100)
- NPR 1,500 = `150000` in code
- Display with: `new Intl.NumberFormat('ne-NP').format(price / 100)`

### Phone Numbers
- 10-digit Nepal format (10xxxxxxxx)
- Stored as HMAC hash for privacy
- Used as the primary identity key (no email required for enrollment)

### Enrollment IDs
- Format: `BSA-YYMM-XXXX` (e.g. `BSA-2603-0042`)
- Generated server-side on confirmation

### Dates & Times
- Nepal Standard Time (NST, UTC+5:45)
- Use `Asia/Kathmandu` timezone

---

## Key Facilities & Programs

### Badminton (Primary)
- Foundation (beginner), Intermediate, Advanced & Competitive
- Youth Academy (under 16)
- Open Play (member court time)

### Gym & Strength Training
- Athletic conditioning for court sports
- Injury prevention and strength development

### Sauna & Recovery
- Post-training muscle recovery
- Available to enrolled members

---

## Frontend Architecture

### Nuxt 4 (app/ directory convention)
```
app/
├── app.vue                ← Root layout
├── pages/                 ← File-based routing
├── components/
│   ├── ui/                ← UiAppButton, UiAppModal, etc.
│   ├── layout/            ← LayoutAppHeader, LayoutAppFooter
│   ├── program/           ← ProgramCard, ProgramGrid
│   └── enrollment/        ← EnrollmentForm, EnrollmentStepper
├── stores/
│   ├── enrollment.ts      ← Active enrollment state
│   └── cart.ts            ← Product cart (if product shop exists)
├── composables/
│   ├── useSettings.ts     ← Site settings from API with SSG fallback
│   └── useProgramMatch.ts ← Deterministic program recommendation quiz
└── utils/
    └── constants.ts       ← BRAND, PROGRAMS, FACILITIES, OTP config
```

### Component Naming
Auto-imported, prefixed with folder name:
- `ui/AppButton.vue` → `<UiAppButton />`
- `layout/AppHeader.vue` → `<LayoutAppHeader />`
- `program/ProgramCard.vue` → `<ProgramCard />` (no prefix needed if unambiguous)

### Animation Standard
```ts
// All page/section entrances use this pattern
{ initial: { opacity: 0, y: 20 }, enter: { opacity: 1, y: 0, transition: { duration: 200 } } }
// No bounce. No spring. No delay > 300ms.
```

---

## Backend Architecture

### API Conventions
- All responses: JSON
- Auth: Sanctum bearer token for protected routes
- OTP rate limit: 5 attempts/minute
- Enrollment rate limit: 10 attempts/minute

### Key API Endpoints (planned)
```
POST   /api/otp/send              — Send OTP to phone
POST   /api/otp/verify            — Verify OTP, return token
GET    /api/programs              — List active programs
GET    /api/facilities            — List facilities
POST   /api/enrollments           — Submit enrollment
GET    /api/enrollments/{id}      — Track enrollment (authenticated)
GET    /api/settings              — Site settings (public)
```

### Filament Admin (`/admin`)
- Program management (CRUD)
- Enrollment management
- Student records
- Site settings (phone, address, tagline)
- Analytics dashboard

---

## Environment Configuration

### bsa-web/.env.example
```env
NUXT_PUBLIC_API_BASE=https://api.bsa.example.com
NUXT_PUBLIC_GOOGLE_MAPS_KEY=
NUXT_PUBLIC_UMAMI_WEBSITE_ID=
NUXT_PUBLIC_KHALTI_PUBLIC_KEY=
NUXT_PUBLIC_CLOUDINARY_CLOUD_NAME=
```

### bsa-api/.env.example
Key variables:
- `APP_URL` — Laravel app URL
- `FRONTEND_URL` — Nuxt frontend URL (for CORS)
- `SPARROW_TOKEN` / `SPARROW_FROM=BSA` — SMS service
- `KHALTI_SECRET_KEY` / `ESEWA_SECRET_KEY` — Payment gateways
- `CLOUDINARY_*` — Image uploads

---

## Design Direction

### Feel
Energetic, dynamic, sporty, competitive, youth-focused. Inspired by the speed and precision of badminton. Not a gym. Not a spa. An academy.

### Colors (to be finalized in tailwind.config.ts)
- Strong primary accent — energetic, action-oriented
- Clean backgrounds — good contrast for readability
- No peach/blush palette — that was the previous scaff old

### Typography
- Strong athletic display headings (bold, confident)
- Clean body copy (Inter or equivalent)
- No decorative serifs

### Layout
- 1440px max-width
- Mobile-first — most visitors are on Android, Nepali networks
- Sub-second loading critical — no heavy animations on first paint

---

## Development Notes

### Current Status
The codebase was scaffolded from a previous project. Pages and components are being progressively rewritten for BSA. Some files may still contain placeholder content from the scaffold — update as you go.

### Priority Order for Frontend Work
1. Homepage (`pages/index.vue`) — hero, facilities section, CTA
2. Programs page (`pages/programs.vue`) — program cards, enrollment CTA
3. Enrollment flow (`pages/enroll.vue`) — multi-step form with OTP
4. About page (`pages/about.vue`) — founder story, facility photos
5. Facilities page (`pages/facilities.vue`) — courts, gym, sauna

### Do Not Break
- OTP flow (`stores/auth.ts` / `composables/useOtp.ts`)
- Phone hashing (HMAC) — privacy requirement
- Payment gateway integrations (Khalti, eSewa)
- Admin panel auth (Filament)

---

## AI Assistant Guidelines

When working on this codebase:

1. **BSA is a sports academy** — not a waxing studio, not a clothing brand. Any reference to "waxing", "beauty treatments", or "clothing" is a scaffold artifact to be removed.

2. **Badminton is the primary identity** — all design decisions, copy, and features should serve badminton first.

3. **Nepal-first** — NPR pricing (paisa integers), Nepali phone numbers (10-digit), Sparrow SMS, Khalti/eSewa payments, Nepal Standard Time.

4. **No AI features** — zero AI/ML budget. All "smart" features use deterministic JS logic (program quiz, schedule matching, etc.).

5. **Performance matters** — Nepali mobile networks. Optimize aggressively. Prefer static data over API calls where possible.

6. **Private repository** — do not share credentials, API keys, or production URLs.
