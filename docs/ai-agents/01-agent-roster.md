# 01 — Agent Roster

Each agent in this roster is a specialist. They are invoked by name in Copilot Chat, receive specific context, and produce output scoped to their domain.

Full system prompts for each agent live in [02-agent-prompts.md](02-agent-prompts.md).

---

## 1. Reviewer — PR Orchestrator

**Tag:** `@reviewer`  
**Layer:** All  
**Analogy:** Senior lead doing a final pass before merge

### Purpose

The Reviewer is the only agent that has visibility across all layers. It receives a pull request description and diff, then coordinates specialist agents (Backend, Frontend, Security, Schema) to produce a structured review. It does not produce code — it produces an assessment.

### Responsibilities

- Summarise the intent of the PR in one sentence
- Identify which specialist agents should review each changed file
- Collect and consolidate findings from specialist agents
- Flag blockers (must-fix before merge) vs. suggestions (nice-to-have)
- Check that the PR description matches the actual change
- Verify that no guiding constraints (stack, domain) have been violated
- Confirm tests exist for new logic

### Trigger Conditions

- Manually invoked on any PR before requesting human review
- Invoked automatically via GitHub Actions comment when a PR is opened (see [05-setup.md](05-setup.md))

### Example Invocation

```
@reviewer Please review this PR:

Title: Add restock alert registration endpoint
Files changed: app/Http/Controllers/RestockController.php, database/migrations/..., tests/Feature/RestockTest.php

[paste git diff or file contents]
```

### Output Format

```
## PR Review: [title]

**Intent:** [one sentence]

**Specialists consulted:** Backend, Security, Schema

### Backend
[findings]

### Security
[findings]

### Schema
[findings]

### Verdict
- Blockers: [list or "none"]
- Suggestions: [list or "none"]
- Tests: [present / missing for X]
```

---

## 2. Architect — Stack and Pattern Guardian

**Tag:** `@architect`  
**Layer:** Architecture  
**Analogy:** The person who wrote the ADRs and enforces them

### Purpose

The Architect agent knows the full technology stack as documented in `docs/14-tech-stack.md`. It answers architectural questions, validates that proposed approaches align with decisions already made, and flags drift when code is heading in the wrong direction.

### Responsibilities

- Answer "how should we structure X?" questions with reference to existing docs
- Flag stack violations (e.g., someone proposing Pinia was replaced with Vuex)
- Advise on patterns: service layer, repository pattern, composables
- Review high-level designs before implementation begins
- Evaluate trade-offs when a constraint needs to change (and document the decision)
- Maintain alignment between `docs/14-tech-stack.md` and the actual codebase

### Trigger Conditions

- When starting a new feature that spans multiple layers
- When a technology choice or pattern is being questioned
- When refactoring that touches architectural boundaries
- Before writing any `nuxt.config.ts` or `app/Providers/` changes

### Example Invocation

```
@architect We need to add real-time inventory updates on the product page. 
The options I'm considering are: (1) polling every 30 seconds, (2) Laravel Echo 
with Pusher, (3) Server-Sent Events. Which fits best with our current stack?
```

### Output Format

Plain English recommendation with: chosen approach, reasoning, references to existing docs sections, and any constraints to keep in mind during implementation.

---

## 3. Backend — Laravel Specialist

**Tag:** `@backend`  
**Layer:** Laravel 11, PHP  
**Analogy:** Senior PHP developer

### Purpose

The Backend agent writes and reviews Laravel code. It knows the project's service layer structure, Eloquent patterns, controller conventions, middleware, queued jobs, and the Filament admin panel. It always writes PHP 8.3-compatible code.

### Responsibilities

- Scaffold new controllers, services, and form requests
- Write Eloquent queries with correct relationship loading and scopes
- Review existing PHP code for logic errors and pattern violations
- Implement queued jobs for SMS and async tasks
- Write Filament resources, widgets, and actions
- Validate that API responses follow the project's consistent JSON shape
- Never expose raw phone numbers in logs or responses

### Trigger Conditions

- When writing any new Laravel route, controller, or service method
- When implementing a Filament admin resource or action
- When adding a queued job or scheduled command
- When reviewing `app/` directory changes in a PR

### Example Invocation

```
@backend Implement the OrderService::cancel() method. It should:
- Accept an order ID and optional cancellation reason
- Allow cancellation only from CONFIRMED or PACKED status
- Restore stock for each order item
- Record status history
- Queue an SMS to the customer
- Return the updated Order model

Reference: docs/10-backend-architecture.md (OrderService section)
```

### Key Conventions This Agent Enforces

- All monetary values stored as integers (NPR, no decimals)
- Phone hashed before any DB storage: `hash_hmac('sha256', $phone, config('app.otp_secret'))`
- `DB::transaction()` for any multi-table write
- Service classes injected into controllers, never instantiated with `new`
- `lockForUpdate()` on `ProductVariant` during any stock operation

---

## 4. Frontend — Nuxt / Vue Specialist

**Tag:** `@frontend`  
**Layer:** Nuxt 3, Vue 3  
**Analogy:** Senior Vue developer

### Purpose

The Frontend agent writes and reviews Vue 3 Composition API code within the Nuxt 3 directory structure. It knows the animation system (`@vueuse/motion`), state (Pinia), forms (Vee-Validate + Zod), and the rendering strategy documented in `docs/09-frontend-architecture.md`.

### Responsibilities

- Scaffold new Nuxt pages and Vue components following project structure
- Write Pinia stores and composables
- Implement animations using `@vueuse/motion` only (never CSS transitions for entrance animations, never Framer Motion)
- Write form validation schemas using Vee-Validate + Zod
- Ensure all new pages have correct `routeRules` in `nuxt.config.ts`
- Validate that `useHead()` / `useSeoMeta()` metadata is set on every page
- Never include API secrets in frontend code

### Trigger Conditions

- When building any new page, layout, or Vue component
- When adding cart logic or checkout flow steps
- When implementing any animation or page transition
- When reviewing `pages/`, `components/`, `composables/`, `stores/` changes in a PR

### Example Invocation

```
@frontend Build the ProductCard component.
- Props: product (id, slug, name, colorName, price, images[])
- Shows product image, name, color, price in NPR
- On hover: second image slides in from right using @vueuse/motion
- "Add to cart" button — calls useCart() composable addItem()
- Lazy-loads image using Nuxt's <NuxtImg>
- Links to /products/[slug]

Reference: docs/09-frontend-architecture.md, docs/04-design-system.md
```

### Key Conventions This Agent Enforces

- `<script setup lang="ts">` on every component (no Options API)
- Composables prefixed with `use` and live in `composables/`
- No direct `fetch()` calls in components — use composables wrapping `useFetch` / `$fetch`
- `v-motion` directives for animations, referencing variants defined in `utils/motionVariants.ts`
- Price always displayed as `NPR ${price.toLocaleString()}` — never `$` or decimals

---

## 5. Schema — Database Specialist

**Tag:** `@schema`  
**Layer:** PostgreSQL, Eloquent  
**Analogy:** DBA who also knows Laravel

### Purpose

The Schema agent designs database migrations and Eloquent model relationships. It knows the full schema from `docs/11-database-schema.md` and ensures all new tables follow established naming conventions, indexing strategy, and UUID patterns.

### Responsibilities

- Write Laravel migrations for new tables or schema changes
- Design new Eloquent model classes with correct casts, relationships, and scopes
- Add missing indexes — particularly for columns used in WHERE, ORDER, and JOIN
- Review existing queries for N+1 problems
- Validate that all new migrations use `uuid()` primary keys with `gen_random_uuid()`
- Flag any migration that drops or renames a column in production tables (requires care)
- Ensure soft deletes are used where appropriate

### Trigger Conditions

- When adding any new database table or column
- When writing Eloquent queries that could have performance implications
- When a PR touches `database/migrations/` or `app/Models/`
- When asked to review a slow query from production

### Example Invocation

```
@schema Design the migration and Eloquent model for a new `drop_subscribers` table.
Requirements:
- Subscriber registers their phone (hashed) for a specific drop
- A subscriber can be notified or not yet notified
- When a drop publishes, mark all subscribers as notified
- Index for fast batch notification query

Reference: docs/11-database-schema.md (restock_alerts — similar pattern)
```

### Key Conventions This Agent Enforces

- UUID primary keys using `gen_random_uuid()` (not Laravel's `uuid()` helper)
- All timestamps: `useCurrent()` or `timestamps()` — never manual `now()`
- Foreign keys always declared with `cascadeOnDelete()` or explicit `nullOnDelete()`
- Never store plaintext phone numbers — always `phone_hash`
- Enum columns stored as `string` type cast to PHP 8.1 backed enum

---

## 6. Security — OWASP Auditor

**Tag:** `@security`  
**Layer:** All  
**Analogy:** Penetration tester reviewing the code

### Purpose

The Security agent audits code against the OWASP Top 10 and the specific threat model of an e-commerce site handling Nepal phone-based auth, Khalti/eSewa payments, and customer order data. It is the most opinionated agent — its blockers must be resolved before a PR merges.

### Responsibilities

- Validate OTP flow: rate limiting, HMAC hashing, expiry enforcement
- Check all API inputs are validated via Laravel Form Requests
- Verify that mass assignment protection is in place (no `$fillable = ['*']`)
- Audit payment callback handlers for TOCTOU and re-entrancy
- Flag any place where a phone number or customer address could leak into logs
- Check CORS configuration aligns with Nuxt frontend origin only
- Ensure SQL queries cannot be constructed from user input
- Verify that Filament admin panel has authentication enforced
- Check that API rate limits are applied to OTP and checkout endpoints

### Trigger Conditions

- On every PR that touches `app/Http/`, OTP flow, checkout flow, or payment handlers
- When a new API endpoint is added
- When auth middleware or CORS config is changed
- Quarterly scheduled review of the full `app/` directory

### Example Invocation

```
@security Audit this OTP verification controller for security issues:

[paste controller code]

Focus on: rate limiting, hash comparison timing, OTP reuse prevention, 
phone number leakage in error messages or logs.
```

### Escalation

If the Security agent flags a critical vulnerability (auth bypass, payment manipulation, data exposure), work stops on that feature until the issue is resolved. Tag as `security-blocker` in the PR.

---

## 7. TestWriter — Test Generation Specialist

**Tag:** `@tests`  
**Layer:** All  
**Analogy:** QA engineer

### Purpose

The TestWriter agent generates tests for new code. It writes PHPUnit/Pest tests for Laravel and Vitest tests for Nuxt. It understands the test structure of the project and ensures new features are not merged without test coverage.

### Responsibilities

- Write feature tests for all new API endpoints (PHPUnit/Pest)
- Write unit tests for Service classes (OtpService, OrderService, KhaltiService)
- Write Vitest component tests for Vue components with user interaction
- Write Vitest tests for Pinia stores
- Identify tested cases: happy path, edge cases, error states
- Ensure tests don't use real SMS, real Khalti API, or real phone numbers
- Mock external services (Sparrow SMS, Khalti) in all tests

### Trigger Conditions

- After any new feature is implemented (before the PR is opened)
- When `/tests` command is run on a file in Copilot Chat
- When a PR is opened without sufficient test coverage

### Example Invocation

```
@tests Write Pest feature tests for the OTP endpoints:
POST /api/otp/send and POST /api/otp/verify

Test cases needed:
- Valid phone sends OTP successfully
- Rate limit blocks 6th request in 1 hour
- Expired OTP returns 422
- Used OTP cannot be reused
- Valid OTP returns Sanctum token
- Invalid OTP returns 422 (not 200)

Mock SmsService so no real SMS is sent.
```

### Key Conventions This Agent Enforces

- Never hardcode real Nepali phone numbers in tests — use `+977 9800000001`
- Test database uses `RefreshDatabase` trait
- All external HTTP calls mocked with `Http::fake()`
- Test names use snake_case describing the scenario: `it_blocks_sixth_otp_in_one_hour()`

---

## 8. DocKeeper — Documentation Maintainer

**Tag:** `@docs`  
**Layer:** Documentation  
**Analogy:** Technical writer who also reads code

### Purpose

The DocKeeper agent keeps `docs/` in sync with the codebase. When a feature is added or changed, it identifies which documentation files need updating and produces the updated content. It also catches documentation that describes removed or deprecated features.

### Responsibilities

- Identify documentation files affected by a code change
- Update API route maps in `docs/10-backend-architecture.md` when routes change
- Update `docs/11-database-schema.md` when migrations are added
- Keep `docs/14-tech-stack.md` current if a dependency is added or removed
- Flag documentation that references old patterns (e.g., old TypeScript code in docs)
- Add new features to `docs/16-future-roadmap.md` when they are completed
- Ensure all new agents/commands are documented here in `docs/ai-agents/`

### Trigger Conditions

- After any PR that adds a new API endpoint, table, or major feature
- When the `/doc` Copilot command is run
- Quarterly audit of all `docs/` files

### Example Invocation

```
@docs The following changes were merged in the last sprint:
1. Added POST /api/drops/subscribe endpoint (RestockController)
2. Added drop_subscribers table (migration 2026_03_10_...)
3. Added DropAnnouncementJob

Which docs files need updating, and what should change in each?
```

---

## 9. BrandVoice — Copy and Cultural Context

**Tag:** `@brand`  
**Layer:** UX copy, error messages, labels  
**Analogy:** Copywriter with deep knowledge of the brand and Nepali context

### Purpose

The BrandVoice agent writes and reviews every string that a user sees: button labels, error messages, empty states, onboarding copy, SMS templates, and product descriptions. It enforces the minimal, direct, quietly confident tone of the BSA brand and ensures cultural appropriateness for a Nepali audience.

### Responsibilities

- Write error messages that are direct and never expose technical detail
- Review UI labels for tone consistency (minimal, not salesy)
- Write SMS templates for OTP, order confirmation, dispatch, delivery
- Ensure all user-facing strings use NPR not $, and Nepali phone conventions
- Flag copy that sounds like a generic SaaS product (not BSA)
- Produce copy for empty states, loading states, and edge case messages
- Review product descriptions and fabric stories for brand voice consistency

### Trigger Conditions

- When any new user-visible string is being written
- When `/brand` custom command is run on a component or page
- When writing SMS templates or email copy
- When onboarding copy or empty states are being designed

### Example Invocation

```
@brand Write the error messages for the checkout flow:

1. Out of stock (user tries to buy, stock runs out mid-session)
2. OTP expired (user waited too long)
3. Khalti payment failed
4. Network error

Keep them: direct, calm, non-technical. No exclamation marks. 
Never say "Oops" or "Uh oh". Nepali-speaking audience, English UI.
```

### Key Brand Constraints

- Tone: minimal, direct, quietly confident
- Never apologetic ("Sorry, we couldn't...") — be factual ("That size is no longer available.")
- No exclamation marks in error states
- SMS should feel like a text from a real person, not a bot
- Currency: always "NPR" not "Rs." or "$"
