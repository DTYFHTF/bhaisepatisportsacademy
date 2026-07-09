# Contributing

> Production-first: `main` deploys to the live site on every push. Work accordingly.

## Local Setup

```bash
# Frontend
cd bsa-web
cp .env.example .env        # fill NUXT_PUBLIC_* as needed
npm install
npm run dev                  # http://localhost:3000

# Backend
cd bsa-api
cp .env.example .env         # local: DB_CONNECTION=sqlite, APP_ENV=local
composer install
php artisan key:generate
php artisan migrate --seed
composer dev                 # serve + queue + logs + vite
# Admin panel: http://localhost:8000/admin
```

## Workflow Rules

1. **No direct pushes of risky changes to `main`** — it auto-deploys. Branch, open a PR, let CI run.
2. **Small, logically grouped commits.** One concern per commit; `type: summary` style already in use (`fix:`, `chore:`, `security:`) — keep it.
3. **Every change explains why** in the PR/commit body: rationale + expected impact.
4. **Update docs with the change** — if a change contradicts anything in `docs/`, the same PR updates the doc.
5. **Preserve backward compatibility** in the API where practical: the deployed SPA and the API deploy independently; a breaking API change must ship after (or tolerate) the old frontend.
6. **Verify before merge**: run the affected flow locally (booking, checkout, tracking). API changes: `composer test`. No frontend test suite exists yet (roadmap 8) — manual verification is mandatory until then.
7. **Refactor only when it improves maintainability** for a change you're already making. No drive-by rewrites.

## Code Standards

- **Frontend**: Vue 3 `<script setup lang="ts">`; design tokens only (no hex literals in components); use `.section-container`/`.section-padding`; components accept class passthrough; no data-fetching in presentational components. Icons: lucide.
- **Backend**: FormRequest validation on every write endpoint; named throttle on every public write route; business logic in `app/Services`, not controllers; Pint for formatting (`vendor/bin/pint`).
- **Naming**: components `PascalCase` namespaced by folder; composables `useX`; API responses camelCase (via `CamelCaseResponse` middleware) while DB stays snake_case.
- **Linting/formatting**: not yet configured for the frontend — adding ESLint (`@nuxt/eslint`) + Prettier is a Phase 5 task; until then match surrounding style exactly.
- **Comments**: only for non-obvious constraints. No narration.

## Security Ground Rules

- Never commit `.env*` (only `*.example`, with secrets empty). Leaked secret ⇒ rotate immediately, then scrub.
- New external service ⇒ update [SECURITY.md](SECURITY.md) (CSP source list) and [PERFORMANCE.md](PERFORMANCE.md).
- User-generated content must be moderated (follow the testimonial pattern) and validated server-side.

## Definition of Done

Code merged ∧ deployed ∧ verified on production URL ∧ docs updated ∧ [CHANGELOG.md](CHANGELOG.md) entry added.
