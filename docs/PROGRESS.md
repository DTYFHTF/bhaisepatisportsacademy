# Bhaisepati Sports Academy — Implementation Progress

> Auto-generated progress tracker. Updated after each implementation phase.

## Frontend (bsa-web) — Nuxt 4 + Vue 3 + Tailwind

### Infrastructure
- [x] Scaffolded Nuxt 4.3.1 via `npx nuxi@latest init`
- [x] Configured Tailwind CSS with custom design tokens
- [x] Configured Pinia + pinia-plugin-persistedstate
- [x] Configured @vueuse/motion, @nuxt/image
- [x] Created `nuxt.config.ts` with route rules, runtime config

### Types & Utils
- [x] `types/product.ts` — Product, ProductVariant, ProductImage
- [x] `types/order.ts` — Order, OrderItem, OrderStatusHistory
- [x] `types/cart.ts` — CartItem, Cart
- [x] `utils/animations.ts` — fadeInUp, stagger animations
- [x] `utils/formatters.ts` — formatNPR, formatDate, formatPhone
- [x] `utils/constants.ts` — delivery fees, categories, sizes

### Stores
- [x] `stores/cart.ts` — Cart management with persistence
- [x] `stores/checkout.ts` — Multi-step checkout state

### Composables
- [x] `composables/useToast.ts` — Toast notifications
- [x] `composables/useOTP.ts` — OTP send/verify flow
- [x] `composables/useRecentlyViewed.ts` — Recently viewed products
- [x] `composables/useSizing.ts` — Deterministic size finder
- [x] `composables/useGoogleMaps.ts` — Google Maps + Places + Geocoding for delivery location

### Components (29 total)
- [x] UI: AppButton, AppInput, AppModal, AppSheet, AppToast, AppSkeleton, AppBadge, OTPInput, AppSpinner
- [x] Layout: AppHeader, MobileNav, AppFooter, CartDrawer
- [x] Product: ProductCard, ProductGrid, ProductGallery, SizeSelector, ColorSelector, FabricStory, RelatedProducts
- [x] Checkout: ContactStep, DeliveryStep, PaymentStep, OrderSummary, LocationPicker
- [x] Tracking: OrderTimeline, OrderItems, ExchangeForm
- [x] AI: SizeFinder, WardrobeBuilder

### Pages (12 total)
- [x] `index.vue` — Homepage with hero, featured products
- [x] `shop/[[category]].vue` — Product listing with filters
- [x] `p/[slug].vue` — Product detail page
- [x] `checkout.vue` — 3-step checkout flow
- [x] `order/confirmed.vue` — Order confirmation
- [x] `track.vue` — Order tracking with OTP/JWT
- [x] `wardrobe.vue` — Wardrobe builder
- [x] `story.vue` — Brand story
- [x] `care.vue` — Care instructions
- [x] `sizing.vue` — Size guide + AI size finder
- [x] `faq.vue` — FAQ with accordion
- [x] `[...slug].vue` — 404 catch-all

### Plugins
- [x] `plugins/umami.client.ts` — Umami analytics script injection

### Dev Server
- [x] Verified: `npm run dev` serves on port 3000, all pages return 200

---

## Backend (bsa-api) — Laravel 12 + Filament 3

### Infrastructure
- [x] Scaffolded Laravel 12.54.0 via `composer create-project`
- [x] Installed Laravel Sanctum 4.3
- [x] Installed Guzzle 7.10
- [x] Configured `.env` (FRONTEND_URL, OTP_SECRET, Khalti, eSewa, Sparrow)
- [x] Configured `config/services.php`, `config/app.php`
- [x] Configured `bootstrap/app.php` (exception handling, stateful API)

### Enums (5)
- [x] Category, Size, WardrobeRole, PaymentMethod, OrderStatus

### Migrations (11 custom + 4 default)
- [x] products, product_variants, product_images, product_pairs
- [x] orders, order_items, order_status_histories
- [x] otp_codes, tracking_tokens
- [x] looks, look_items, restock_alerts
- [x] add_location_fields_to_orders (latitude, longitude, formatted_address, nearest_landmark)

### Models (11 + User)
- [x] Product, ProductVariant, ProductImage
- [x] Order, OrderItem, OrderStatusHistory
- [x] OtpCode, TrackingToken, RestockAlert
- [x] Look, LookItem

### Services (7)
- [x] OtpService, SmsService, OrderService
- [x] KhaltiService, EsewaService
- [x] SizingService, StyleExplanationService

### Controllers (6)
- [x] ProductController, OtpController, CheckoutController
- [x] OrderController, RestockController, ExchangeController

### Routes (11 API endpoints)
- [x] All registered and verified

### Seeder
- [x] ProductSeeder with 6 products, variants, images, product pairs

### Admin Panel (Filament 3)
- [x] Installed Filament 3
- [x] Configured AdminPanelProvider (BSA brand, Amber primary)
- [x] Created admin user (admin@bsa.example.com)
- [x] StatsOverview widget (Orders 7-day, Revenue 7-day, Low Stock)
- [x] LatestOrdersWidget (recent orders table)
- [x] OrderResource (list, view, change_status action)
- [x] ProductResource (CRUD, form with all fields)
- [x] VariantsRelationManager (inline stock management)
- [x] Analytics page (Umami embed)

### Dev Server
- [x] Verified: `php artisan serve --port=8000`, API returns data

---

## Project Meta
- [x] `.github/copilot-instructions.md` created
- [x] `docs/PROGRESS.md` created (this file)

---

## Deployment & Infrastructure
- [x] Git monorepo initialized, pushed to GitHub (DTYFHTF/bsa-wears)
- [x] `.github/workflows/deploy-api.yml` — CI/CD for Laravel (lint, test, SSH deploy to `~/bsa-wears/bsa-api`)
- [x] `.github/workflows/deploy-web.yml` — CI/CD for Nuxt (build, SCP to `~/bsa-wears/bsa-web`, PM2 reload)
- [x] `bsa-web/ecosystem.config.cjs` — PM2 config (port 3001, full env vars incl. Umami, Cloudinary)
- [x] `.env.example` files for both projects
- [x] `bsa-api/.env.production.example` — production template with known values pre-filled
- [x] `bsa-web/.env.production` — frontend public env values committed
- [x] `scripts/server-setup.sh` — one-shot first-time server setup script
- [x] Architecture: bsa.example.com (Nuxt, port 3001) + api.bsa.example.com (Laravel PHP)
- [x] Google Maps integration (composable, LocationPicker, migration, admin display)
- [x] Umami analytics (client plugin `plugins/umami.client.ts`, admin embed page, website ID: `cffc5868-c5ec-4824-81fb-34b170c74051`)

## Production Checklist
### Must-do before first deploy (user action required)
- [ ] **cPanel MySQL** — Create database `bsa` → becomes `rishipa2_bsa`; create user → becomes `rishipa2_bsa_user`
- [ ] **cPanel Subdomain** — Create `api.bsa.example.com`, Document Root: `~/bsa-wears/bsa-api/public`
- [ ] **cPanel Node.js App** — Create app for `bsa.example.com`, root: `~/bsa-wears/bsa-web`, startup: `.output/server/index.mjs`, port 3001
- [ ] **SSH: Run setup** — `bash scripts/server-setup.sh` (handles clone, composer, migrate, npm build, PM2 start)
- [ ] **GitHub Secrets** — Add: `VPS_HOST`, `VPS_USER`, `VPS_PORT`, `VPS_SSH_KEY`, `NUXT_PUBLIC_GOOGLE_MAPS_KEY`
- [ ] **Deploy key** — `cat ~/.ssh/bsa_github.pub` → add to GitHub repo → Settings → Deploy keys

## Remaining (Phase 2+)
- [ ] Server-side setup on babal.host (MySQL DB, subdomain, clone repo)
- [ ] Generate bsa_github deploy key
- [ ] Set GitHub Actions secrets
- [ ] Drop management system
- [ ] Filament Shield (role-based access)
- [ ] Two-factor auth for admin
- [ ] Email notifications
- [ ] SEO meta tags optimization
- [ ] Performance optimization (lighthouse audit)
