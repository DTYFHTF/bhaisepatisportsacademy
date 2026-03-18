# Bhaisepati Sports Academy — Copilot Instructions

## Project Overview
Bhaisepati Sports Academy is a community-centered sports training facility website with a two-project monorepo:
- **bsa-web**: Nuxt 4 + Vue 3 + Tailwind CSS frontend
- **bsa-api**: Laravel 12 + Filament 3 backend API + admin panel

The primary identity revolves around **Badminton** — the owner's passion and most popular facility.
Other facilities include Gym & Strength Training, and Sauna & Recovery.

## Architecture
- Browser → Nuxt (SSR/ISR) → REST API → Laravel → SQLite (dev) / PostgreSQL (prod)
- No AI/ML APIs. Zero AI budget. All "smart" features use deterministic JS logic.

## Frontend (bsa-web)
- **Framework**: Nuxt 4.3 with `app/` directory structure (all source in `app/`)
- **Styling**: Tailwind CSS with custom design tokens
- **State**: Pinia stores with pinia-plugin-persistedstate
- **Validation**: vee-validate + zod v3.x
- **Icons**: lucide-vue-next
- **Components**: Auto-imported from `app/components/` — prefix with folder name (e.g. `UiAppButton`, `LayoutAppHeader`)

### Key Conventions
- NPR currency as integers (paisa): 5500_00 = NPR 5,500
- Phone numbers: 10-digit Nepal format, hashed with HMAC for storage
- Enrollment IDs: `BSA-YYMM-XXXX` format

## Backend (bsa-api)
- **Framework**: Laravel 12, PHP 8.2+
- **Auth**: Laravel Sanctum (API), Filament auth (admin)
- **Admin**: Filament 3 at `/admin`
- **Models**: Use `HasUuids`, `$guarded = []`, cast enums

### API Conventions
- All responses are JSON
- Prices stored as integers (paisa)
- OTP sent via Sparrow SMS, verified with HMAC
- Payment: Khalti, eSewa, COD

### Rate Limits
- `otp`: 5 attempts/minute
- `enrollment`: 10 attempts/minute

## Design Direction
- Feel: energetic, dynamic, sporty, competitive, youth-focused
- Visual inspiration from shuttlecock movement and badminton dynamics
- Motion: subtle fade-in-up animations (200-300ms), no bounce
- Typography: strong athletic headings
- Layout: 1440px max, responsive grid

## Key Facilities
- **Badminton Courts**: Professional playing surface, proper lighting
- **Gym & Strength Training**: Conditioning, strength development, injury prevention
- **Sauna & Recovery**: Muscle recovery and relaxation

## File Structure
```
bhaisepatisportacademy/
├── docs/                 # Project documentation
├── bsa-web/app/          # Nuxt frontend source
│   ├── pages/            # Route pages
│   ├── components/       # ui/, layout/, program/, enrollment/
│   ├── stores/           # Pinia stores
│   ├── composables/      # Vue composables
│   ├── utils/            # animations, formatters, constants
│   └── types/            # TypeScript types
└── bsa-api/              # Laravel backend source
    ├── app/Filament/     # Admin resources and widgets
    ├── app/Http/         # Controllers, middleware, requests
    ├── app/Models/       # Eloquent models
    ├── app/Enums/        # PHP enums
    └── app/Services/     # Business logic services
```
