# AI Agent System — Bhaisepati Sports Academy

## What This Is

This directory defines the AI-assisted development environment for the Bhaisepati Sports Academy project. It treats GitHub Copilot not as an autocomplete tool but as a collection of specialist agents — each with a defined role, a narrow scope, and a clear set of responsibilities.

A new developer joining this project should be able to read this directory and understand:
- Which agent to invoke for any given task
- What prompt context to provide
- How the agents interact with each other and with the codebase
- How to use Copilot commands effectively within this project

---

## Philosophy

**Agents are specialists, not generalists.** Each agent has deep knowledge of one layer of the stack. You get better output by invoking the right agent with focused context than by asking a general agent to "build a feature."

**The codebase is the source of truth.** Agents read the `docs/` folder to understand decisions that have already been made. They do not re-derive architecture — they enforce it.

**Agents flag, developers decide.** No agent has write authority. They produce output (code, plans, reviews, docs) that a developer reviews and applies. Escalation paths exist for situations where agents disagree.

**Nepali context is a first-class concern.** Every agent that produces user-visible output is aware of the cultural context: Lalitpur origin, Nepali phone numbers, NPR currency, Sparrow SMS, and the minimal brand voice.

---

## Agent Map

```
                    ┌──────────────────────────────────────┐
                    │          REVIEWER (Orchestrator)     │
                    │  Coordinates all agents on a PR      │
                    └───────────┬───────────────────┬──────┘
                                │                   │
              ┌─────────────────┼──────────┐        │
              ▼                 ▼          ▼        ▼
        ┌──────────┐    ┌──────────┐  ┌──────┐  ┌──────┐
        │ARCHITECT │    │ SECURITY │  │TESTS │  │ DOCS │
        │ Stack &  │    │  OWASP   │  │Writer│  │Keeper│
        │ patterns │    │  Auditor │  └──────┘  └──────┘
        └──────────┘    └──────────┘
              │
    ┌─────────┴─────────┐
    ▼                   ▼
┌──────────┐     ┌──────────┐
│ BACKEND  │     │FRONTEND  │
│ Laravel  │     │Nuxt/Vue  │
│  Agent   │     │  Agent   │
└──────────┘     └──────────┘
       │
       ▼
┌──────────┐     ┌──────────┐
│  SCHEMA  │     │  BRAND   │
│  Agent   │     │  Voice   │
└──────────┘     └──────────┘
```

---

## Agent Roster (Quick Reference)

| Agent | Tag | Primary Layer | When to invoke |
|---|---|---|---|
| Reviewer | `@reviewer` | All layers | Starting a PR review |
| Architect | `@architect` | Architecture | Stack decisions, pattern questions |
| Backend | `@backend` | Laravel / PHP | API routes, services, migrations |
| Frontend | `@frontend` | Nuxt / Vue | Pages, components, composables |
| Schema | `@schema` | PostgreSQL / Eloquent | DB design, migrations, queries |
| Security | `@security` | All layers | Auth, validation, data handling |
| TestWriter | `@tests` | All layers | Writing / reviewing tests |
| DocKeeper | `@docs` | Documentation | Keeping docs in sync with code |
| BrandVoice | `@brand` | UX copy | Strings, error messages, labels |

---

## Document Index

| File | Contents |
|---|---|
| [01-agent-roster.md](01-agent-roster.md) | Full agent definitions — responsibilities, triggers, examples |
| [02-agent-prompts.md](02-agent-prompts.md) | System prompts to invoke each agent in Copilot |
| [03-copilot-commands.md](03-copilot-commands.md) | Slash command reference with BSA-specific examples |
| [04-interactions.md](04-interactions.md) | Agent-to-agent flows, PR workflow, escalation paths |
| [05-setup.md](05-setup.md) | Configuration, `.github/copilot-instructions.md`, VS Code setup |

---

## Five-Minute Quick Start

If you're a new developer who just cloned the repo:

1. **Read [05-setup.md](05-setup.md)** — get Copilot configured for this project
2. **Read [03-copilot-commands.md](03-copilot-commands.md)** — learn the commands you'll use daily
3. **Skim [01-agent-roster.md](01-agent-roster.md)** — know which agent to call for what
4. When you pick up your first task, use `/plan` to break it down
5. When you open a PR, paste the `@reviewer` prompt from [02-agent-prompts.md](02-agent-prompts.md)

---

## Guiding Constraints

These constraints must be respected by every agent and documented in every agent prompt:

**Stack (non-negotiable):**
- Backend: Laravel 11 + Filament 3 (no Next.js, no Node.js API)
- Frontend: Nuxt 3 + Vue 3 Composition API (no React, no Framer Motion)
- Animation: `@vueuse/motion` only
- State: Pinia (no Vuex, no Zustand)
- Database: PostgreSQL via Supabase (no SQLite, no MongoDB)
- ORM: Eloquent (no Prisma, no raw SQL unless unavoidable)

**Domain:**
- Currency: NPR (Nepali Rupees) — integers, never floats
- Phone: Nepal format (+977 98XXXXXXXX) — always hashed before storage
- SMS: Sparrow SMS API — never direct phone number logging
- Payments: Khalti and eSewa — always verify server-side
- Origin: Lalitpur, Nepal — reflected in all "about" and brand copy
