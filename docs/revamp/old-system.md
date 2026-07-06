# Old System Audit — ManagPro

> **IMMUTABLE -- AI TIDAK BOLEH mengubah file ini. Baca sebagai panduan. Untuk output, lihat mapping di ai-rules/README.md.**

> **Status:** PLANNING — Analisis sistem yang sudah ada. Dokumentasikan SEBELUM menulis kode baru.
> **Purpose:** Pahami apa yang sudah berjalan — fitur, arsitektur, database, pain points. Ini acuan untuk `gap-analysis.md`.

---

## 1. System Identity

| Item | Detail |
|------|--------|
| Nama Sistem | ManagPro |
| Tahun Dibangun | 2025-2026 |
| URL Production | https://kan.bn |
| Repository | D:\laragon\www\manag-pro |
| Project Type | Monorepo (pnpm workspaces + Turbo) |

---

## 2. Tech Stack (Old)

| Layer | Technology | Version |
|-------|-----------|---------|
| Backend API | tRPC + Node.js | Latest |
| Frontend | Next.js + React + TypeScript | 15.x |
| Styling | Tailwind CSS | Latest |
| Database | PostgreSQL | 15+ |
| ORM | Drizzle ORM | Latest |
| Auth | Better Auth | Latest |
| i18n | Lingui | Latest |
| Monorepo | pnpm workspaces + Turbo | pnpm 9.14.2 |
| Email | React Email | Latest |
| Storage | S3-compatible (AWS S3, R2, MinIO) | — |
| Deployment | Railway / Docker / Vercel | — |

---

## 3. Module / Feature Inventory

| # | Module | Status | User Count | Pain Level | Notes |
|---|--------|--------|-----------|-----------|-------|
| 1 | Workspace Management | Active | — | Low | Multi-workspace, slug, plans, settings |
| 2 | Board Management | Active | — | Low | Kanban boards, visibility, favorites, templates |
| 3 | List Management | Active | — | Low | Columns in boards, reordering |
| 4 | Card Management | Active | — | Low | Core entity, drag-drop, rich text, due dates |
| 5 | Labels | Active | — | Low | Color-coded labels per board |
| 6 | Members & Assignment | Active | — | Low | Assign members to cards |
| 7 | Checklists | Active | — | Low | Sub-tasks with progress tracking |
| 8 | Comments | Active | — | Low | Discussion threads on cards |
| 9 | Attachments | Active | — | Med | S3 storage, thumbnails |
| 10 | Activity Log | Active | — | Low | Comprehensive change tracking |
| 11 | Board Filters | Active | — | Low | Filter by member, label, list, due date |
| 12 | Templates | Active | — | Low | Reusable board templates |
| 13 | RBAC | Active | — | Med | Roles, permissions, custom roles |
| 14 | Notifications | Active | — | Med | Mention, workspace events |
| 15 | Public Boards | Active | — | Low | Public board/card views |
| 16 | Trello Import | Active | — | High | Board import from Trello |
| 17 | S3 Storage | Active | — | Low | File uploads (avatars, attachments) |
| 18 | Email (SMTP) | Active | — | Low | Magic links, invitations |
| 19 | Webhooks | Active | — | Low | Event subscriptions per workspace |
| 20 | MCP Server | Active | — | Low | AI client integration (46 tools) |
| 21 | OAuth Providers | Active | — | Low | 17+ providers (Google, GitHub, etc.) |
| 22 | API Keys | Active | — | Low | Programmatic access |
| 23 | Stripe Billing | Active | — | Med | Subscription management |
| 24 | i18n | Active | — | Low | 9 languages supported |

**Pain Level:** Low = berfungsi baik, High = banyak keluhan / sering bug

---

## 4. Database Inventory

| Table | Rows (approx) | Size | Critical? | Notes |
|-------|-------------|------|----------|-------|
| `user` | — | — | ✅ | Auth users (Better Auth) |
| `session` | — | — | ✅ | Auth sessions |
| `account` | — | — | ✅ | OAuth accounts |
| `verification` | — | — | | Email verifications |
| `workspace` | — | — | ✅ | Multi-workspace |
| `workspace_member` | — | — | ✅ | Workspace membership |
| `workspace_role` | — | — | ✅ | RBAC roles |
| `workspace_role_permission` | — | — | ✅ | Role permissions |
| `workspace_member_permission` | — | — | | Per-member overrides |
| `workspace_invite_link` | — | — | | Invite links |
| `board` | — | — | ✅ | Kanban boards |
| `list` | — | — | ✅ | Board columns |
| `card` | — | — | ✅ | Core work items |
| `card_label` | — | — | | Many-to-many labels |
| `card_member` | — | — | | Many-to-many members |
| `card_checklist` | — | — | | Checklists |
| `card_checklist_item` | — | — | | Checklist items |
| `card_comment` | — | — | | Comments |
| `card_attachment` | — | — | | File attachments |
| `card_activity` | — | — | ✅ | Change tracking |
| `label` | — | — | | Board labels |
| `notification` | — | — | | User notifications |
| `subscription` | — | — | | Stripe billing |
| `workspace_webhook` | — | — | | Webhook configs |
| `import` | — | — | | Trello imports |
| `integration` | — | — | | OAuth integrations |
| `api_key` | — | — | | API key auth |
| `feedback` | — | — | | User feedback |

**Total DB size:** — (fresh installation)

---

## 5. Architecture (Old)

### Pattern

Monorepo with shared packages:
- `apps/web/` — Next.js frontend (SSR + SPA)
- `packages/api/` — tRPC routers (server-side)
- `packages/db/` — Drizzle ORM schema + migrations + repositories
- `packages/auth/` — Better Auth configuration
- `packages/shared/` — Shared utilities
- `packages/email/` — React Email templates
- `packages/stripe/` — Stripe integration
- `packages/logger/` — Logging
- `packages/mcp/` — MCP server

### Directory Structure

```
manag-pro/
├── apps/
│   ├── web/                    # Next.js app
│   │   └── src/
│   │       ├── components/     # React components
│   │       ├── views/          # Page-level components
│   │       ├── pages/          # Next.js pages
│   │       ├── hooks/          # Custom hooks
│   │       ├── utils/          # Utilities
│   │       ├── locales/        # i18n translations
│   │       ├── styles/         # CSS/Tailwind
│   │       └── types/          # TypeScript types
│   └── docs/                   # Documentation
├── packages/
│   ├── api/                    # tRPC API routers
│   │   └── src/routers/*.ts
│   ├── db/                     # Database layer
│   │   └── src/
│   │       ├── schema/         # Drizzle schemas
│   │       └── repository/     # DB queries
│   ├── auth/                   # Better Auth config
│   ├── shared/                 # Shared utilities
│   ├── email/                  # Email templates
│   ├── stripe/                 # Stripe integration
│   ├── logger/                 # Logging
│   └── mcp/                    # MCP server
└── tooling/                    # Shared configs (ESLint, Prettier, TS)
```

### Key Dependencies

| Package | Version | Purpose | Masih Supported? |
|---------|---------|---------|----------------|
| Next.js | 15.x | Frontend framework | ✅ |
| React | 19.x | UI library | ✅ |
| tRPC | Latest | Type-safe API | ✅ |
| Drizzle ORM | Latest | Database ORM | ✅ |
| Better Auth | Latest | Authentication | ✅ |
| Tailwind CSS | Latest | Styling | ✅ |
| Lingui | Latest | i18n | ✅ |
| Turbo | 2.3.1 | Monorepo build | ✅ |
| Zod | Latest | Validation | ✅ |

---

## 6. Pain Points & Reason for Revamp

| # | Pain Point | Impact | Priority |
|---|-----------|--------|----------|
| 1 | Next.js SSR complexity for simple SPA use case | High build times, complex deployment | High |
| 2 | tRPC adds abstraction overhead vs REST | Steeper learning curve, harder to expose API externally | High |
| 3 | Monorepo complexity for smaller team | Overhead without benefit at current scale | Medium |
| 4 | 17+ OAuth providers — maintenance burden | Security surface area, testing complexity | Medium |
| 5 | MCP server — niche feature | Low usage, high maintenance | Low |

**Kenapa revamp (bukan refactor):**
- Stack migration dari React/Next.js ke Vue.js/Vite — fundamentally different frontend paradigm
- API paradigm shift dari tRPC ke REST — different contract style
- Deployment target shift ke Vercel serverless — requires architectural changes
- Auth stays with Better Auth — compatible with new stack
- Database stays with PostgreSQL + Drizzle — compatible with new stack

---

## 7. Integration Points (Existing)

| Integration | Type | Critical? | Notes |
|-----------|------|----------|-------|
| Supabase PostgreSQL | Database | ✅ | Managed PostgreSQL |
| Better Auth | Auth | ✅ | Session-based auth |
| S3 Storage | File Storage | ✅ | Avatar + attachments |
| SMTP Email | Email | ✅ | Magic links, invitations |
| Stripe | Payment | ✅ | Subscription billing |
| Trello API | Import | | Board import |
| OAuth Providers | Auth | ✅ | 17+ providers |
| Webhooks | Events | | Workspace event hooks |
| MCP Server | AI | | AI client integration |

---

## 8. Users & Stakeholders

| Role | Jumlah | Kebutuhan Kritis |
|------|--------|----------------|
| Individual Users | — | Board management, personal productivity |
| Team Members | — | Collaboration, card assignment, comments |
| Workspace Admins | — | Member management, settings, billing |
| Guests | — | Limited board access (public boards) |

---

## 9. Key Design Patterns

| Pattern | Implementation |
|---------|---------------|
| Soft Deletes | `deletedAt` timestamp on all major entities |
| Public IDs | 12-character `publicId` for all external-facing entities |
| Internal IDs | Never exposed externally — always use `publicId` |
| Sequential Indexing | Cards and lists maintain ordered `index` fields |
| Activity Logging | All card mutations create activity records |
| Row-Level Security | PostgreSQL RLS on all tables |
| Optimistic Updates | tRPC `onMutate` for instant UI feedback |

---

## 10. API Endpoints (tRPC Routers)

| Router | File | Key Operations |
|--------|------|----------------|
| `workspace` | workspace.ts | CRUD, slug, settings |
| `board` | board.ts | CRUD, visibility, favorites, archive, move |
| `list` | list.ts | CRUD, reordering |
| `card` | card.ts | CRUD, movement, labels, members, due dates |
| `label` | label.ts | CRUD for board labels |
| `checklist` | checklist.ts | CRUD for checklists and items |
| `member` | member.ts | Invite, remove, update roles |
| `permission` | permission.ts | Role and permission management |
| `user` | user.ts | User profile, account management |
| `attachment` | attachment.ts | Upload, download, delete |
| `feedback` | feedback.ts | Submit user feedback |
| `import` | import.ts | Trello board import |
| `integration` | integration.ts | Trello OAuth integration |
| `webhook` | webhook.ts | Webhook CRUD |
| `health` | health.ts | Health check endpoint |

---

## 11. Frontend Pages & Routes

| Route | Description |
|-------|-------------|
| `/` | Landing page |
| `/login` | User login |
| `/signup` | User registration |
| `/boards` | Board listing |
| `/boards/[...boardId]` | Board view |
| `/cards/[cardId]` | Card detail view |
| `/templates` | Template listing |
| `/[workspaceSlug]` | Workspace dashboard |
| `/[workspaceSlug]/[...boardSlug]` | Board by slug |
| `/members` | Member management |
| `/settings/account` | Account settings |
| `/settings/workspace` | Workspace settings |
| `/settings/billing` | Billing settings |
| `/settings/permissions` | Permission management |
| `/settings/api` | API key management |
| `/settings/webhooks` | Webhook management |
| `/invite/[code]` | Invite link |
| `/onboarding/workspace` | Workspace onboarding |

---

## 12. Internationalization

| Code | Language |
|------|----------|
| `en` | English |
| `fr` | Francais |
| `de` | Deutsch |
| `es` | Espanol |
| `it` | Italiano |
| `nl` | Nederlands |
| `ru` | Russkiy |
| `pl` | Polski |
| `ptbr` | Portugues (BR) |

---

## 13. Environment Variables

| Variable | Required | Description |
|----------|----------|-------------|
| `NEXT_PUBLIC_BASE_URL` | ✅ | Application base URL |
| `BETTER_AUTH_SECRET` | ✅ | Auth secret (32+ chars) |
| `POSTGRES_URL` | Optional | External database URL |
| `SMTP_*` | Optional | Email configuration |
| `S3_*` | Optional | File storage configuration |
| `REDIS_URL` | Optional | Rate limiting |
| `*_CLIENT_ID/SECRET` | Optional | OAuth providers |
| `TRELLO_*` | Optional | Trello integration |
