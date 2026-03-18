# Bhaisepati Sports Academy — Website Platform

> *Train harder. Move faster. Grow stronger.*

Bhaisepati Sports Academy is a community-centered sports training facility in Bhaisepati, Nepal, dedicated to developing athletic skill, discipline, teamwork, and physical fitness. The academy's primary identity revolves around **Badminton** — the owner's passion and the most popular activity at the facility.

This repository contains the complete codebase and documentation for the Bhaisepati Sports Academy website.

---

## Vision

Build a **modern, energetic, and dynamic** sports academy website that highlights training programs, facilities, and community engagement while supporting **student enrollment and program participation**.

---

## Core Principles

| Principle | What It Means |
|---|---|
| **Badminton first** | Badminton is the heart of the brand and website experience. |
| **Speed is a feature** | Sub-second page loads. Instant interactions. |
| **Energetic & dynamic** | The website should feel fast and alive, like badminton itself. |
| **Mobile-first** | Designed for thumbs — most visitors are on mobile. |
| **Community-centered** | Welcomes beginners, casual players, and competitive athletes. |
| **Youth-focused** | Programs and design appeal to young athletes and their families. |

---

## Documentation Index

| # | Document | Description |
|---|---|---|
| 01 | [Brand Identity](01-brand-identity.md) | Positioning, voice, visual DNA, and brand principles |
| 02 | [User Experience Flows](02-user-experience-flows.md) | User journeys and interaction maps |
| 03 | [Information Architecture](03-information-architecture.md) | Sitemap, content hierarchy, and navigation |
| 04 | [Design System](04-design-system.md) | Typography, color palette, spacing, and components |
| 05 | [Product Page](05-product-page.md) | Program/facility page anatomy and features |
| 06 | [Checkout Flow](06-checkout-flow.md) | Enrollment and payment flow |
| 07 | [Order Tracking](07-order-tracking.md) | Enrollment tracking system |
| 08 | [AI Features](08-ai-features.md) | Smart program recommendations |
| 09 | [Frontend Architecture](09-frontend-architecture.md) | Nuxt app structure, components, and state |
| 10 | [Backend Architecture](10-backend-architecture.md) | API design, services, and integrations |
| 11 | [Database Schema](11-database-schema.md) | Database tables, relationships, and indexing |
| 12 | [Admin Panel](12-admin-panel.md) | Dashboard, program management, and enrollment operations |
| 13 | [Performance Strategy](13-performance-strategy.md) | Caching, image optimization, and loading strategies |
| 14 | [Tech Stack](14-tech-stack.md) | Technology decisions and rationale |
| 15 | [Unique Experiences](15-unique-experiences.md) | Differentiating brand features |
| 16 | [Future Roadmap](16-future-roadmap.md) | Phase-wise feature rollout and growth plan |

---

## Tech Stack (Summary)

| Layer | Technology |
|---|---|
| Frontend | Nuxt 4 (Vue 3) + TypeScript |
| Styling | Tailwind CSS |
| Backend | Laravel 12 + PHP 8.2+ |
| Admin Panel | Filament 3 |
| Auth | Laravel Sanctum |
| Image CDN | Cloudinary |
| Payments | Khalti + eSewa (Nepal) + COD |
| SMS | Sparrow SMS |
| Database | SQLite (dev) / PostgreSQL (prod) |
| State | Pinia |
| Analytics | Umami (privacy-first) |

---

## Quick Start

```bash
# Clone the repository
git clone https://github.com/DTYFHTF/bhaisepatisportacademy.git
cd bhaisepatisportacademy

# Frontend
cd bsa-web
npm install
npm run dev

# Backend (separate terminal)
cd bsa-api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

---

## Monorepo Structure

```
bhaisepatisportacademy/
├── docs/                    # All documentation (you are here)
├── bsa-web/                 # Nuxt frontend
│   ├── app/
│   │   ├── pages/           # Route pages
│   │   ├── components/      # UI, layout, program components
│   │   ├── composables/     # Vue composables
│   │   ├── stores/          # Pinia stores
│   │   ├── utils/           # Helpers, formatters, constants
│   │   └── types/           # TypeScript types
│   └── public/              # Static assets
├── bsa-api/                 # Laravel backend
│   ├── app/
│   │   ├── Filament/        # Admin panel resources
│   │   ├── Http/            # Controllers, middleware, requests
│   │   ├── Models/          # Eloquent models
│   │   ├── Enums/           # PHP enums
│   │   └── Services/        # Business logic services
│   ├── database/            # Migrations, seeders, factories
│   └── routes/              # API and web routes
├── scripts/                 # Deployment scripts
└── .github/                 # CI/CD workflows
```

---

## Key Facilities

- **Badminton Courts** — Professional playing surface, proper lighting, training environment
- **Gym & Strength Training** — Conditioning, strength development, injury prevention
- **Sauna & Recovery** — Muscle recovery and relaxation after training

---

## License

Proprietary — Bhaisepati Sports Academy. All rights reserved.
