# 03 — Copilot Commands Reference

This document covers all Copilot commands used in the Bhaisepati Sports Academy development workflow — both built-in GitHub Copilot commands and custom workflow commands defined for this project.

---

## Built-in Copilot Commands

These commands are available in Copilot Chat without any configuration. Each is documented here with BSA-specific context and examples.

---

### `/explain`

**What it does:** Explains selected code in plain English — its purpose, how it works, and any non-obvious design decisions.

**When to use it:**
- When onboarding and reading unfamiliar sections of the codebase
- When reviewing a PR and a piece of code isn't immediately clear
- When a service method or composable has complex logic worth understanding before modifying

**How it fits into the workflow:**
Run `/explain` before editing any code you haven't written. Understanding the existing logic prevents accidentally breaking behaviour that was intentional.

**BSA-specific examples:**

```
/explain

// Explain this method:
public function place(array $input): Order
{
    return DB::transaction(function () use ($input) {
        foreach ($input['items'] as $item) {
            $variant = ProductVariant::lockForUpdate()->findOrFail($item['variant_id']);
            ...
        }
    });
}
```

```
/explain

// Why does this component use v-motion instead of a CSS transition?
<div v-motion :initial="{ opacity: 0, x: 20 }" :enter="{ opacity: 1, x: 0 }">
```

**Expected output:** Plain English explanation of what the code does, why `lockForUpdate()` matters during concurrent drop launches, or why `@vueuse/motion` is the animation approach.

---

### `/fix`

**What it does:** Attempts to fix the selected code — resolving errors flagged by the compiler, linter, type checker, or test suite.

**When to use it:**
- When Pest tests are failing and you want a first-pass fix
- When TypeScript reports a type error in a Vue component
- When PHPStan or Larastan reports a type mismatch
- When ESLint flags a Vue convention violation

**How it fits into the workflow:**
Run `/fix` with the error message included for best results. Review the fix — never apply it blindly. If the fix introduces a new pattern that doesn't match the project conventions, reject it and consult the relevant specialist agent.

**BSA-specific examples:**

```
/fix

Error: Call to undefined method App\Models\Product::active()

public function index(): JsonResponse
{
    $products = Product::active()->with('variants')->get();
    return response()->json(['data' => $products]);
}
```

```
/fix

TypeScript error: Property 'price' does not exist on type 'Product'

const formattedPrice = `NPR ${product.price.toLocaleString()}`
```

**Caution:** If `/fix` suggests switching to a different technology (e.g., changing Pinia to something else, or using Prisma instead of Eloquent), decline it. The fix should be within the existing stack constraints.

---

### `/tests`

**What it does:** Generates unit or feature tests for selected code.

**When to use it:**
- After implementing a new Service method or controller action
- After building a new Vue component with user interaction
- When a PR was opened without sufficient test cover
- When adding edge case tests for discovered bugs

**How it fits into the workflow:**
Write implementation first, then run `/tests` before opening a PR. The generated tests give you a starting point — review them to ensure edge cases (rate limiting, out-of-stock, expired OTP) are covered. Add missing cases manually.

**BSA-specific examples:**

```
/tests

Write Pest feature tests for this endpoint:
POST /api/restock

[paste RestockController@store and the corresponding FormRequest]

Include:
- Valid registration stores alert
- Duplicate registration returns 422 (unique constraint)
- Invalid phone format returns 400
- Missing product_id returns 400
```

```
/tests

Write Vitest tests for the useCart() composable.
Test: addItem, removeItem, clearCart, itemCount computed, total computed.
Mock $fetch for any API calls.
```

**Conventions this command must follow:**
- Mock `SmsService` in all Laravel tests
- Use `Http::fake()` for any Khalti or Sparrow SMS calls in tests
- Test phone: `+977 9800000001`
- Pest syntax, not PHPUnit class methods

---

### `/doc`

**What it does:** Generates PHPDoc or JSDoc comments for selected code.

**When to use it:**
- When adding a new public Service method that other developers will call
- When a complex Eloquent scope or accessor needs explanation
- When a composable has non-obvious return types or side effects

**How it fits into the workflow:**
Run `/doc` after finishing implementation. Only add docblocks to public methods that form part of the project's internal API. Do not add docblocks to every private method — that creates noise that obscures the important documentation.

**BSA-specific examples:**

```
/doc

Add PHPDoc to this method:

public function send(string $phone): void
{
    // rate check, generate, hash, store, SMS
}
```

```
/doc

Add JSDoc to this composable:

export function useCart() {
    const store = useCartStore()
    function addItem(product, variantId, quantity) { ... }
    return { addItem, items: computed(...), total: computed(...) }
}
```

**Note:** The `/doc` command generates documentation for code that exists. For writing documentation files in `docs/`, use `@docs` (the DocKeeper agent) instead.

---

### `/new`

**What it does:** Creates a new file with the appropriate boilerplate based on a description.

**When to use it:**
- Scaffolding a new Laravel service class
- Creating a new Nuxt page with the correct structure
- Creating a new Pinia store
- Creating a new Filament resource

**How it fits into the workflow:**
Use `/new` to get the skeleton, then fill in the logic. Always review that the generated skeleton matches the project's conventions before adding business logic.

**BSA-specific examples:**

```
/new Create a new Laravel Service class: ExchangeService

It will handle exchange/return requests.
Methods needed: initiate(Order $order, array $input): Exchange

Follow the existing service pattern from app/Services/OrderService.php
```

```
/new Create a new Nuxt page: pages/looks/[hash].vue

This is a shareable look page (ISR rendering).
It shows 3 product images side by side, a style note, and a "Shop this look" button.
Use <script setup lang="ts"> and follow the project's page conventions.
```

```
/new Create a new Pinia store: stores/drops.ts

Tracks the current active drop: { isActive, publishesAt, productSlugs[] }
Persisted to localStorage.
Follow the pattern from stores/cart.ts
```

---

## Custom Workflow Commands

These are not built-in Copilot commands — they are prompt templates defined for common BSA workflows. Invoke them by pasting the prompt into Copilot Chat or by creating `.github/prompts/` files (see [05-setup.md](05-setup.md)).

---

### `/plan`

**What it does:** Breaks a feature request down into a sequenced task list, assigning each task to the appropriate agent and identifying dependencies.

**When to use it:**
- At the start of any feature that touches more than one file
- When a feature request is ambiguous and needs to be scoped before work begins
- When estimating work before a sprint or milestone

**How to invoke:**

```
Using the Bhaisepati Sports Academy system context (Laravel 11 backend, Nuxt 3 frontend, 
PostgreSQL via Supabase, Filament admin), break down the following feature 
into a sequenced task list.

For each task, specify:
- What needs to be done
- Which layer it affects (backend / frontend / database / admin / tests / docs)
- Which agent should handle it (@backend / @frontend / @schema / etc.)
- Whether it depends on a previous task

Feature: [describe the feature]
```

**Example:**

```
Feature: Allow customers to save a look they've built and share it via WhatsApp

Plan this as a sequenced task list.
```

**Expected output:**
```
Task 1 — Schema (@schema)
  Create looks and look_items tables migration
  No dependencies

Task 2 — Backend (@backend)
  POST /api/looks — save a look, return look_hash
  GET /api/looks/{hash} — retrieve a look
  Depends on: Task 1

Task 3 — Frontend (@frontend)
  "Save this look" button on Wardrobe Builder
  /look/[hash] shareable page with Open Graph metadata
  WhatsApp share button
  Depends on: Task 2

Task 4 — Tests (@tests)
  Feature tests for both API endpoints
  Component test for share button
  Depends on: Tasks 2, 3

Task 5 — Docs (@docs)
  Update docs/10-backend-architecture.md with new endpoints
  Update docs/11-database-schema.md with new tables
  Depends on: Tasks 1, 2
```

---

### `/review`

**What it does:** Runs a full structured code review using the Reviewer agent. Equivalent to invoking `@reviewer` with the review prompt from [02-agent-prompts.md](02-agent-prompts.md).

**When to use it:**
- Before requesting a human code review on any PR
- Before merging any code that touches auth, payments, or stock

**How to invoke:**

```
Review the following code change for the Bhaisepati Sports Academy project.
Stack: Laravel 11 backend, Nuxt 3 frontend, PostgreSQL, Filament admin.
Cover: patterns, security, performance, test coverage.

[paste the git diff or the relevant files]
```

**Expected output:** Structured review with section headers for each specialist domain and a final Blockers / Suggestions verdict.

---

### `/migrate`

**What it does:** Generates a complete Laravel migration file from a plain English description of a new table or schema change.

**When to use it:**
- When adding a new table to the database
- When adding columns to an existing table
- When adding or removing an index

**How to invoke:**

```
Using the Bhaisepati Sports Academy schema conventions (docs/11-database-schema.md):
- UUID primary keys with gen_random_uuid()
- phone_hash not plaintext phone
- string columns for enum values, cast to PHP 8.1 backed enum in Model

Generate a Laravel migration for:
[describe the table or change]
```

**Example:**

```
Generate a Laravel migration for a drop_products pivot table.
A drop has many products; a product can belong to many drops.
Columns: drop_id (uuid), product_id (uuid), display_order (integer, default 0)
```

---

### `/component`

**What it does:** Scaffolds a new Vue component following BSA's frontend conventions.

**When to use it:**
- When adding any new reusable UI component
- When building a new section for a page

**How to invoke:**

```
Create a Vue 3 component for Bhaisepati Sports Academy following project conventions:
- <script setup lang="ts">
- Tailwind CSS only
- @vueuse/motion for any animations
- Props typed with defineProps<{...}>()
- Emit typed with defineEmits<{...}>()

Component: [component name]
Purpose: [what it does]
Props: [list props and types]
Emits: [list any emits]
Interactions: [describe any click / hover / animation behaviour]
```

---

### `/brand`

**What it does:** Reviews or rewrites copy using the BrandVoice agent to ensure tone consistency with the BSA brand.

**When to use it:**
- Before any user-visible string is committed
- When reviewing error messages or empty states in a PR
- When writing SMS templates

**How to invoke:**

```
Review / rewrite the following copy for Bhaisepati Sports Academy.
Brand rules: minimal, direct, no exclamation marks in errors, 
no apologies, NPR not $, reads like a real person not a bot.

[paste the copy to review or generate]
```

---

## Command Summary Table

| Command | Built-in | Primary use | Relevant agent |
|---|---|---|---|
| `/explain` | Yes | Understand unfamiliar code | Any |
| `/fix` | Yes | Resolve errors / linting | Any |
| `/tests` | Yes | Generate test cases | `@tests` |
| `/doc` | Yes | Add docblocks to methods | Any |
| `/new` | Yes | Scaffold new files | Depends on type |
| `/plan` | Custom | Break down a feature | `@architect` |
| `/review` | Custom | Pre-merge code review | `@reviewer` |
| `/migrate` | Custom | Generate migration files | `@schema` |
| `/component` | Custom | Scaffold Vue components | `@frontend` |
| `/brand` | Custom | Review copy for tone | `@brand` |

---

## Command Anti-Patterns

Avoid these common misuses:

**Do not use `/fix` to avoid reading the error.** Always read the error message first. `/fix` can introduce changes that resolve the symptom but create a new problem elsewhere.

**Do not use `/tests` as a substitute for thinking about edge cases.** The generated tests are a starting point. You are responsible for ensuring coverage of edge cases like rate limiting, concurrency issues, and payment callback replay.

**Do not use `/new` and accept the output unchecked.** Generated boilerplate may not match BSA's project conventions. Always compare the output against an existing file in the same category before committing.

**Do not use `/explain` as a substitute for documentation.** If you find yourself explaining the same code repeatedly, that code should be documented in `docs/` instead.

**Do not run `/review` and treat it as a human review.** The Reviewer agent finds patterns and security issues — it does not understand business context. A human reviewer still decides whether the feature is correct.
