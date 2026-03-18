# 05 — Setup and Configuration

This document explains how to configure GitHub Copilot to be aware of the Bhaisepati Sports Academy project context — so that every Copilot suggestion respects the stack, conventions, and domain constraints documented in `docs/`.

---

## 1. Global Project Instructions

GitHub Copilot reads `.github/copilot-instructions.md` automatically for every session in this repository. Create this file at the repository root with the content below.

### `.github/copilot-instructions.md`

```markdown
# Bhaisepati Sports Academy — Copilot Instructions

## Project Overview
Bhaisepati Sports Academy is a minimal Nepali clothing brand based in Lalitpur, Nepal.
This repository contains the full stack: Nuxt 3 frontend + Laravel 11 backend.

## Stack (non-negotiable)
- Frontend: Nuxt 3 + Vue 3 Composition API + TypeScript
- Styling: Tailwind CSS 3
- Animation: @vueuse/motion (never Framer Motion — it is React-only)
- State: Pinia (never Vuex, never Zustand)
- Forms: Vee-Validate + Zod
- Backend: Laravel 11, PHP 8.3
- Admin: Filament 3 (lives at /admin inside Laravel)
- ORM: Eloquent (never Prisma, never raw SQL as primary approach)
- Database: PostgreSQL via Supabase (never SQLite)
- Payments: Khalti + eSewa (always server-side verified)
- SMS: Sparrow SMS API (server-side only)
- Rate limiting: Laravel RateLimiter (built-in, no Upstash Redis)
- AI: Groq free tier or hand-crafted templates (no paid OpenAI)

## Domain Rules
- Currency: NPR as integers — never floats, never "$"
- Phone numbers: ALWAYS hashed before storage with hash_hmac
  Formula: hash_hmac('sha256', $phone, config('app.otp_secret'))
- Order IDs: DH-YYMM-XXXX format
- Stock locking: lockForUpdate() on ProductVariant during checkout
- SMS failures must never block order completion (catch + log only)

## Laravel Conventions
- Service layer in app/Services/ — business logic never goes in controllers
- Controllers are thin: accept Request, call Service, return Response
- All multi-table writes use DB::transaction()
- API errors: {"error": "SNAKE_CASE", "message": "human readable"}
- All monetary values are integers (NPR, no decimals)
- Mass assignment: always use specific $fillable, never ['*']
- UUIDs: gen_random_uuid() in migrations, HasUuids trait on models

## Vue / Nuxt Conventions
- Always <script setup lang="ts"> — no Options API
- No direct fetch() in components — wrap in composables
- Images always via <NuxtImg>, never <img>
- Price format: `NPR ${price.toLocaleString()}`
- v-motion for entrance animations, from utils/motionVariants.ts
- Lazy components use <LazyComponentName> prefix

## Testing
- Laravel: Pest syntax (it(), describe(), expect())
- Always mock SmsService in tests
- Use Http::fake() for Khalti and Sparrow SMS
- Test phone number: +977 9800000001
- Vue: Vitest + @vue/test-utils, mock Pinia with createTestingPinia()

## Docs Location
Full architecture documentation is in docs/. Reference it, do not contradict it.
AI agent system documentation is in docs/ai-agents/.
```

---

## 2. Custom Prompt Files

GitHub Copilot supports `.github/prompts/*.prompt.md` files that can be invoked in Agent Mode. Create these files to make agent invocation faster.

### Directory structure to create

```
.github/
  copilot-instructions.md       ← global context (always active)
  prompts/
    architect.prompt.md         ← @architect agent
    backend.prompt.md           ← @backend agent
    frontend.prompt.md          ← @frontend agent
    schema.prompt.md            ← @schema agent
    security.prompt.md          ← @security agent
    tests.prompt.md             ← @tests agent
    docs.prompt.md              ← @docs agent
    brand.prompt.md             ← @brand agent
    reviewer.prompt.md          ← @reviewer agent
    plan.prompt.md              ← /plan workflow command
    review.prompt.md            ← /review workflow command
```

Each prompt file contains the full system prompt from [02-agent-prompts.md](02-agent-prompts.md) wrapped in the correct YAML frontmatter:

```yaml
---
mode: agent
description: Bhaisepati Sports Academy Backend Agent — Laravel 11 / PHP 8.3 specialist
---
[paste the full Backend system prompt here]
```

The `description` field is what appears in Copilot's prompt picker.

---

## 3. VS Code Settings

Add these settings to `.vscode/settings.json` in the repository root:

```json
{
  "github.copilot.chat.codeGeneration.useInstructionFiles": true,
  "github.copilot.chat.codeGeneration.instructions": [
    {
      "file": ".github/copilot-instructions.md"
    }
  ],
  "github.copilot.enable": {
    "*": true,
    "markdown": true
  },
  "github.copilot.editor.enableAutoCompletions": true
}
```

`useInstructionFiles: true` tells Copilot to read `.github/copilot-instructions.md` and all `.github/prompts/` files automatically.

---

## 4. First-Time Developer Setup

When a new developer joins the project, walk through these steps in order:

### Step 1 — Install GitHub Copilot

1. Install the GitHub Copilot extension in VS Code
2. Install the GitHub Copilot Chat extension
3. Sign in with your GitHub account (must have Copilot access)

### Step 2 — Verify instructions are loaded

Open Copilot Chat (`Cmd+Shift+I`). Ask:

```
What framework does Bhaisepati Sports Academy use for the admin panel?
```

**Expected response:** Filament 3 inside Laravel. If Copilot says anything else, the instructions file is not loaded — check that `.github/copilot-instructions.md` exists and `useInstructionFiles` is true in `.vscode/settings.json`.

### Step 3 — Test an agent

Run:

```
Using your understanding of the Bhaisepati Sports Academy stack, list five constraints 
I must follow when writing a new Laravel service class.
```

Copilot should mention: NPR integers, phone hashing, DB::transaction, service injection, SMS try/catch.

### Step 4 — Read the workflow docs

In this order:
1. `docs/ai-agents/00-overview.md` — system overview
2. `docs/ai-agents/01-agent-roster.md` — skim all nine agents
3. `docs/ai-agents/03-copilot-commands.md` — commands you'll use daily
4. `docs/14-tech-stack.md` — the authoritative stack reference

### Step 5 — Pick up your first task

Use `/plan` with your first assigned issue before writing any code.

---

## 5. GitHub Actions Integration (Optional)

To have Copilot review PRs automatically when they are opened, add this workflow:

### `.github/workflows/copilot-review.yml`

```yaml
name: Copilot PR Review

on:
  pull_request:
    types: [opened, ready_for_review]

jobs:
  copilot-review:
    runs-on: ubuntu-latest
    permissions:
      pull-requests: write
      contents: read
    steps:
      - uses: actions/checkout@v4
      
      - name: Post review prompt as comment
        uses: actions/github-script@v7
        with:
          script: |
            const reviewPrompt = `
            ## AI Review Request
            
            This PR is ready for Copilot review. To run a structured review:
            
            1. Open GitHub Copilot Chat
            2. Load the reviewer prompt from \`docs/ai-agents/02-agent-prompts.md\`
            3. Paste the diff from this PR
            
            **Files changed:** ${{ github.event.pull_request.changed_files }}
            **Branch:** ${{ github.event.pull_request.head.ref }}
            
            Remember: resolve all Security blockers before requesting human review.
            `;
            
            github.rest.issues.createComment({
              issue_number: context.issue.number,
              owner: context.repo.owner,
              repo: context.repo.repo,
              body: reviewPrompt
            });
```

This posts a reminder comment with the review instructions on every new PR. It does not run Copilot automatically (that requires GitHub Copilot for PRs access).

---

## 6. Agent Prompt File Template

When creating a `.github/prompts/` file for an agent, use this template:

```markdown
---
mode: agent
description: Bhaisepati Sports Academy [Agent Name] — [one-line description]
---

[Full system prompt from docs/ai-agents/02-agent-prompts.md]
```

**Example — backend.prompt.md:**

```markdown
---
mode: agent
description: Bhaisepati Sports Academy Backend Agent — Laravel 11 / PHP 8.3 specialist
---

You are the Backend agent for Bhaisepati Sports Academy, a minimal Nepali clothing brand 
from Lalitpur, Nepal.

[... rest of backend prompt ...]
```

---

## 7. Maintaining This System

The AI agent system is part of the codebase and must be maintained like code.

### When to update agent prompts

- When a new technology is added to the stack, update the relevant agent prompt and `.github/copilot-instructions.md`
- When a convention changes, update both the agent prompt and `docs/14-tech-stack.md`
- When a new agent is added, add it to `01-agent-roster.md`, `02-agent-prompts.md`, `04-interactions.md`, and `00-overview.md`

### When to update Copilot instructions

- When a new table is added, the Schema agent prompt's table list should be updated
- When a new package is added or removed, update the relevant layer instructions
- After the quarterly docs audit, update any agent that references stale information

### Prompt versioning

Agent prompts do not need semantic versioning. They are code — changes to them go through the same PR process as any other change. The DocKeeper agent is responsible for flagging when prompts go out of sync with the codebase.

---

## 8. Quick Reference Card

Print or bookmark this for daily use:

```
AGENT QUICK REFERENCE — Bhaisepati Sports Academy

Planning a feature:     /plan [feature description]
Validating architecture: @architect [question]
Writing Laravel code:   @backend [task]
Writing Vue/Nuxt code:  @frontend [task]
Database design:        @schema [table/relation description]
Security audit:         @security [paste code to audit]
Writing tests:          @tests [paste implementation]
Updating docs:          @docs [describe what changed]
Reviewing copy:         @brand [paste strings to review]
Pre-merge review:       @reviewer [paste PR diff]

COMMANDS
/explain — understand code
/fix     — fix errors
/tests   — generate tests
/doc     — add docblocks
/new     — scaffold boilerplate
/migrate — generate migration
/component — scaffold Vue component

CONSTRAINTS TO NEVER VIOLATE
- NPR as integers  - Phone hashed  - lockForUpdate() for stock
- DB::transaction() for multi-writes  - SMS failures never block orders
- @vueuse/motion (not Framer Motion)  - Pinia (not Vuex)
```
