# 04 — Agent Interactions

This document describes how agents interact with each other, with the repository, and with developers. It covers the standard workflows, handoff protocols, escalation paths, and team dynamics.

---

## Agent Hierarchy

No agent has unconditional authority. The following describes relative trust:

```
Developer (final decision-maker)
    │
    ├── Security agent (blockers cannot be overridden without explicit justification)
    │
    ├── Reviewer (coordinates all agents during PR review)
    │
    ├── Architect (governs cross-cutting concerns)
    │       │
    │       ├── Backend ──── Schema
    │       │
    │       └── Frontend
    │
    └── Support agents (scoped, non-blocking)
            ├── TestWriter
            ├── DocKeeper
            └── BrandVoice
```

**Security blockers are the exception**: if the Security agent marks something as `CRITICAL`, work on that feature pauses until the issue is addressed. This cannot be overridden by time pressure.

**Reviewer findings are consolidated**: the Reviewer synthesises findings from all other agents but does not override them. If Backend and Security disagree, the developer resolves it.

---

## Standard Development Workflow

### 1. Feature Planning

Before writing any code, plan the feature using the Architect and Planner:

```
Developer runs:
  /plan to break down the feature into tasks

If the approach is novel:
  @architect to validate the approach

Output: ordered task list with agents assigned to each task
```

---

### 2. Database First

New features that require schema changes start with the Schema agent:

```
@schema: Design migration + Eloquent model

Developer reviews output:
  - Does the table follow existing conventions?
  - Are the indexes correct?
  - Do the relationships make sense?

Developer applies migration:
  php artisan migrate
```

Only proceed to Backend and Frontend after the schema is confirmed.

---

### 3. Backend Implementation

```
@backend: Implement service + controller + form request

@security: Audit the implementation (run in parallel or immediately after)

@tests: Generate feature tests for the endpoint

Developer reviews all three outputs together before committing.
```

The Backend agent and Security agent run on the same code. Their outputs are complementary — Backend focuses on correctness, Security focuses on safety.

---

### 4. Frontend Implementation

```
@frontend: Implement page / component / composable

@brand: Review all user-visible strings (can run in parallel)

@tests: Generate Vitest component tests

Developer reviews outputs before committing.
```

BrandVoice is invoked in parallel with Frontend for any page with user-visible copy.

---

### 5. Pre-PR Review

Before opening a pull request:

```
Developer assembles:
  - PR title and description
  - Files changed (or git diff)

@reviewer: Full PR review

Developer reads review, resolves all blockers, considers suggestions.

If Security flagged anything:
  Fix the issue, then re-run @security on the fix before opening the PR.
```

---

### 6. Post-Merge Documentation

After a PR is merged:

```
@docs: Identify affected documentation sections, produce updates

Developer applies the doc updates as a follow-up commit or PR.
```

This is the only step where documentation changes are allowed to lag behind code changes. Everything else in this system assumes docs are updated within 24 hours of a merge.

---

## PR Review Flow (Detailed)

When the Reviewer agent receives a PR, it delegates to specialists in this order:

```
Reviewer receives: PR title + diff

Step 1: Classify changed files
  - PHP files in app/Http/     → Backend + Security
  - PHP files in app/Services/ → Backend + Security  
  - PHP files in database/     → Schema
  - PHP files in app/Filament/ → Backend (Filament)
  - Vue/TS files in pages/     → Frontend + Security
  - Vue/TS files in components/→ Frontend
  - Vue/TS files in stores/    → Frontend
  - Test files                 → TestWriter (coverage check only)
  - Docs files                 → DocKeeper

Step 2: Run specialist reviews (can be run in parallel in a single Copilot session)
  Each specialist reviews only their files.

Step 3: Consolidate
  Reviewer combines findings into:
  - Blockers (must fix before merge)
  - Suggestions (developer's discretion)
  - Test coverage verdict
  - Doc sync check (were docs updated if needed?)

Step 4: Developer resolves blockers
  For Security blockers: fix and re-run @security on the fix
  For other blockers: fix and re-run the relevant specialist
  For suggestions: decide and note in PR description
```

---

## Agent-to-Agent Handoffs

### Backend → Security

After Backend produces code for an endpoint, immediately invoke Security on the same code. Example:

```
First:
@backend Implement POST /api/otp/send [...]

Then:
@security Audit this OTP send handler for OWASP issues:
[paste the Backend agent's output]
```

Security reviews Backend output before it is committed. This is the most important agent-to-agent handoff in the system.

---

### Architect → Backend + Frontend

When Architect defines an approach, Backend and Frontend implement it. The Architect output becomes the "spec" for the implementation agents:

```
@architect: How should we structure the exchange/return flow?
Output: Architect recommends approach A with service ExchangeService

@backend: Implement ExchangeService following this spec:
[paste Architect's recommendation as context]

@frontend: Implement the exchange request form following this spec:
[paste Architect's recommendation as context]
```

---

### Schema → Backend

Schema produces migrations and model definitions. Backend then uses those models in services and controllers. Always share Schema output with Backend as context:

```
@schema: Design the drop_subscribers table.
Output: migration + DropSubscriber model

@backend: Implement RestockController@subscribe using the DropSubscriber model:
[paste Schema output as context]
```

---

### TestWriter → All Agents

TestWriter is always the last agent invoked for any given piece of code. It receives the final implementation (after Security review) and generates tests:

```
After @backend + @security review and fixes:

@tests: Write Pest tests for the ExchangeService.
[paste the final ExchangeService code after Security fixes]
```

Do not write tests against pre-Security-review code — the code may change.

---

### BrandVoice → Frontend

BrandVoice reviews the strings in Frontend's output before they reach the component:

```
@frontend: Build the out-of-stock empty state for the product page
Output: Vue component with error messages and labels

@brand: Review these strings from the product page component:
"This size is sold out"
"Notify me when back"
"Your cart is empty"
```

BrandVoice does not review code structure — only the human-readable strings.

---

## Interaction with the Repository

### Files Agents Read

Each agent is given context from specific documentation files. Always include the relevant doc section when invoking an agent:

| Agent | Primary doc references |
|---|---|
| Architect | `docs/14-tech-stack.md` |
| Backend | `docs/10-backend-architecture.md` |
| Frontend | `docs/09-frontend-architecture.md` |
| Schema | `docs/11-database-schema.md` |
| Security | `docs/10-backend-architecture.md` (auth and rate limit sections) |
| TestWriter | The specific implementation file being tested |
| DocKeeper | All `docs/*.md` files |
| BrandVoice | `docs/01-brand-identity.md` |
| Reviewer | All of the above |

### Files Agents Write (via Developer)

Agents produce code as text output. Developers are responsible for writing that code to the correct file:

| Agent output | Where it goes |
|---|---|
| Migrations | `database/migrations/` |
| Models | `app/Models/` |
| Services | `app/Services/` |
| Controllers | `app/Http/Controllers/` |
| Form Requests | `app/Http/Requests/` |
| Filament Resources | `app/Filament/Resources/` |
| Vue pages | `pages/` |
| Vue components | `components/` |
| Composables | `composables/` |
| Pinia stores | `stores/` |
| Pest tests | `tests/Feature/` or `tests/Unit/` |
| Vitest tests | Alongside the component |
| Doc updates | `docs/[relevant file]` |

---

## Interaction with Developers

### When to Trust Agent Output

**High confidence (review and apply):**
- Schema migrations following established patterns
- Test cases for CRUD endpoints
- PHPDoc / JSDoc comments
- Copy from BrandVoice with no structural changes

**Medium confidence (review carefully):**
- Service class implementations
- Vue component code with animation
- Security audit findings — investigate each finding before dismissing

**Low confidence (use as a starting point, validate manually):**
- Architectural recommendations for novel requirements
- Refactoring suggestions
- Performance optimization advice

### When to Override Agents

Agents can be wrong. Override when:
- The agent's output violates a project convention it wasn't given context for
- The Security agent flags a false positive (document why it's a false positive in the PR)
- The Architect suggests a pattern that was tried and rejected previously

When overriding, add a comment in the code or PR description explaining why the agent's recommendation was not followed.

### When to Escalate

If two agents produce conflicting recommendations:
1. Invoke the Architect to adjudicate
2. If the conflict is about security, default to the Security agent's recommendation
3. If the conflict is still unresolved, discuss with the team and document the decision in `docs/14-tech-stack.md`

---

## Scheduled Maintenance Workflows

These workflows run on a schedule, not triggered by code changes.

### Weekly: Dependency Review

```
@architect 

Review the current package versions in:
- package.json (Nuxt frontend)
- composer.json (Laravel backend)

Identify packages that:
- Have known security vulnerabilities (cross-reference with any CVE feeds you know)
- Have major version releases since our pinned version
- Could be removed without loss of functionality

Do not suggest replacing Pinia, @vueuse/motion, Vee-Validate, Eloquent, 
or Filament with alternatives.
```

### Monthly: Security Audit

```
@security

Conduct a monthly security review of the following files:
- app/Http/Controllers/OtpController.php
- app/Http/Controllers/CheckoutController.php  
- app/Services/KhaltiService.php
- app/Http/Middleware/

Check for OWASP Top 10 issues. Reference the security prompt from 
docs/ai-agents/02-agent-prompts.md for the full checklist.
```

### Quarterly: Docs Audit

```
@docs

Audit all files in docs/ against the current codebase.
Identify: 
- Documentation that describes patterns no longer used
- Missing documentation for features that were added
- API route maps that don't match current routes/api.php
- Schema docs that don't match current migrations

Produce a list of files and sections that need updating.
```
