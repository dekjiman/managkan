# Gap Analysis — Old vs New System

> **IMMUTABLE -- AI TIDAK BOLEH mengubah file ini. Baca sebagai panduan. Untuk output, lihat mapping di ai-rules/README.md.**

> **Status:** PLANNING — Setelah audit old system selesai. Dokumentasikan perbedaan sebelum mulai coding.
> **Purpose:** Identifikasi apa yang berubah, apa yang tetap, apa yang baru. Acuan untuk `migration-strategy.md`.

---

## 1. Stack Comparison

| Layer | Old | New | Reason for Change |
|-------|-----|-----|------------------|
| Frontend | Next.js 15 + React 19 | Vue.js 3 + Vite | User preference, simpler SPA, faster builds |
| API | tRPC (type-safe RPC) | Express.js REST API | Simpler, more standard, easier external consumption |
| Database | PostgreSQL + Drizzle ORM | PostgreSQL (Supabase) + Drizzle ORM | Managed PostgreSQL, same ORM |
| Auth | Better Auth | Better Auth | Same — compatible with new stack |
| Styling | Tailwind CSS | Tailwind CSS | Same |
| i18n | Lingui | Vue I18n (or similar) | Framework-specific i18n solution |
| Monorepo | pnpm + Turbo | Separate frontend/backend dirs | Simpler structure for smaller scope |
| Deployment | Railway / Docker / Vercel | Vercel (serverless) | Serverless-first architecture |
| Email | React Email | Nodemailer (lightweight) | Simpler, no React dependency needed backend-only |
| Storage | S3-compatible | Supabase Storage or S3 | Managed storage |
| Build | Turbo | Vite (FE) + tsc (BE) | Framework-native builds |

---

## 2. Feature Gap

| Module | Old System | New System | Action |
|--------|-----------|-----------|--------|
| Workspace Management | ✅ Full (plans, slug, settings) | ✅ Core (name, slug, description) | Migrate — simplify plans initially |
| Board Management | ✅ Full (visibility, favorites, templates) | ✅ Core (CRUD, visibility) | Migrate — add favorites/templates later |
| List Management | ✅ Full (CRUD, reordering) | ✅ Full | Migrate directly |
| Card Management | ✅ Full (CRUD, movement, rich text) | ✅ Core (CRUD, movement) | Migrate — rich text editor later |
| Labels | ✅ Full | ✅ Full | Migrate directly |
| Members & Assignment | ✅ Full | ✅ Full | Migrate directly |
| Checklists | ✅ Full (progress tracking) | ✅ Core | Migrate — progress UI later |
| Comments | ✅ Full | ✅ Full | Migrate directly |
| Attachments | ✅ Full (S3, thumbnails) | ❌ Deferred | Build later — Supabase Storage |
| Activity Log | ✅ Full | ✅ Full | Migrate directly |
| Board Filters | ✅ Full (member, label, due date) | ✅ Core (member, label) | Migrate — due date filter later |
| Templates | ✅ Full | ❌ Deferred | Build later |
| RBAC | ✅ Full (custom roles) | ✅ Basic (admin/member/guest) | Simplify initially |
| Notifications | ✅ Full | ❌ Deferred | Build later |
| Public Boards | ✅ Full | ❌ Deferred | Build later |
| Trello Import | ✅ Full | ❌ Deferred | Build later |
| S3 Storage | ✅ Full | ❌ Deferred | Build later with Supabase Storage |
| Email (SMTP) | ✅ Full (React Email) | ✅ Basic (Nodemailer) | Simplify — no React dependency |
| Webhooks | ✅ Full | ❌ Deferred | Build later |
| MCP Server | ✅ Full (46 tools) | ❌ Deferred | Build later |
| OAuth Providers | ✅ 17+ providers | ✅ Google + GitHub initially | Start with 2 most common |
| API Keys | ✅ Full | ❌ Deferred | Build later |
| Stripe Billing | ✅ Full | ❌ Deferred | Build later |
| i18n | ✅ 9 languages | ✅ English only initially | Add languages later |
| Dark Mode | ✅ Full | ✅ Full (Tailwind dark class) | Migrate directly |
| Rich Text Editor | ✅ Full | ❌ Deferred | Build later (TipTap or similar) |
| Command Palette | ✅ Full (Ctrl+K) | ❌ Deferred | Build later |
| Drag & Drop | ✅ Full | ✅ Full (vue-draggable or similar) | Migrate directly |

---

## 3. Database Gap

| Table | Old Schema | New Schema | Migration Strategy |
|-------|-----------|-----------|------------------|
| `user` | Better Auth schema | Better Auth schema | Direct — same structure |
| `session` | Better Auth schema | Better Auth schema | Direct — same structure |
| `account` | Better Auth schema | Better Auth schema | Direct — same structure |
| `verification` | Better Auth schema | Better Auth schema | Direct — same structure |
| `workspace` | Full (plans, cardPrefix, cardCounter, weekStart) | Core (name, slug, description) | Simplify — add fields later |
| `workspace_member` | Full | Full | Direct |
| `workspace_role` | Full (custom roles) | Basic (admin/member/guest) | Simplify initially |
| `workspace_role_permission` | Full | Basic | Simplify initially |
| `workspace_member_permission` | Full | Deferred | Build later |
| `workspace_invite_link` | Full | Full | Direct |
| `board` | Full (types, favorites, template) | Core | Simplify initially |
| `list` | Full | Full | Direct |
| `card` | Full (rich fields) | Core | Direct — omit unused fields |
| `label` | Full | Full | Direct |
| `card_label` | Full | Full | Direct |
| `card_member` | Full | Full | Direct |
| `card_checklist` | Full | Full | Direct |
| `card_checklist_item` | Full | Full | Direct |
| `card_comment` | Full | Full | Direct |
| `card_attachment` | Full | Deferred | Build later |
| `card_activity` | Full | Full | Direct |
| `notification` | Full | Deferred | Build later |
| `subscription` | Full | Deferred | Build later |
| `workspace_webhook` | Full | Deferred | Build later |
| `import` | Full | Deferred | Build later |
| `integration` | Full | Deferred | Build later |
| `api_key` | Full | Deferred | Build later |
| `feedback` | Full | Deferred | Build later |

**Data yang DROP (tidak dimigrasi):**
- `subscription` — Billing deferred to later phase
- `workspace_webhook` — Webhooks deferred
- `import` — Trello import deferred
- `integration` — OAuth integrations deferred
- `api_key` — API keys deferred
- `feedback` — Feedback deferred
- `notification` — Notifications deferred
- `card_attachment` — Attachments deferred (S3 storage setup later)

**Data yang perlu TRANSFORM:**
- None — fresh installation, no existing data to migrate

---

## 4. API / Endpoint Gap

| Old Endpoint (tRPC) | New Endpoint (REST) | Notes |
|---------------------|---------------------|-------|
| `workspace.create` | `POST /api/v1/workspaces` | |
| `workspace.getMany` | `GET /api/v1/workspaces` | |
| `workspace.getOne` | `GET /api/v1/workspaces/:publicId` | |
| `workspace.update` | `PATCH /api/v1/workspaces/:publicId` | |
| `workspace.delete` | `DELETE /api/v1/workspaces/:publicId` | |
| `board.create` | `POST /api/v1/boards` | |
| `board.getMany` | `GET /api/v1/boards?workspaceId=` | |
| `board.getOne` | `GET /api/v1/boards/:publicId` | |
| `board.update` | `PATCH /api/v1/boards/:publicId` | |
| `board.delete` | `DELETE /api/v1/boards/:publicId` | |
| `list.create` | `POST /api/v1/lists` | |
| `list.getMany` | `GET /api/v1/lists?boardId=` | |
| `list.update` | `PATCH /api/v1/lists/:publicId` | |
| `list.delete` | `DELETE /api/v1/lists/:publicId` | |
| `list.reorder` | `PUT /api/v1/lists/reorder` | |
| `card.create` | `POST /api/v1/cards` | |
| `card.getMany` | `GET /api/v1/cards?listId=` | |
| `card.getOne` | `GET /api/v1/cards/:publicId` | |
| `card.update` | `PATCH /api/v1/cards/:publicId` | |
| `card.delete` | `DELETE /api/v1/cards/:publicId` | |
| `card.move` | `PUT /api/v1/cards/:publicId/move` | |
| `label.*` | `POST/GET/PATCH/DELETE /api/v1/labels` | |
| `checklist.*` | `POST/GET/PATCH/DELETE /api/v1/checklists` | |
| `member.*` | `POST/GET/PATCH/DELETE /api/v1/members` | |
| `user.*` | `GET/PATCH /api/v1/users/me` | |
| `auth.*` | Handled by Better Auth endpoints | |

---

## 5. Business Logic Gap

| Logic | Old Implementation | New Implementation | Notes |
|-------|------------------|-------------------|-------|
| Auth | Better Auth (tRPC context) | Better Auth (Express middleware) | Same auth, different integration |
| Validation | Zod in tRPC procedures | Zod in Express middleware | Same validation library |
| Authorization | `assertUserInWorkspace` helper | Express middleware | Move to middleware pattern |
| Error Handling | TRPCError | Express error handler middleware | Standard Express pattern |
| Optimistic Updates | tRPC React Query | Vue Query (or manual) | Different client library |
| Card Indexing | Sequential index management | Same logic, REST endpoints | Direct migration |
| Activity Logging | Repository pattern | Service/repository pattern | Same logic |
| Pagination | tRPC infinite query | REST pagination (?page=&limit=) | Standard REST pagination |
| File Uploads | S3 direct | Deferred | Build later |
| Real-time | None (polling) | Deferred | Consider WebSocket later |

---

## 6. Infrastructure Gap

| Component | Old | New | Notes |
|-----------|-----|-----|-------|
| Frontend Hosting | Vercel (Next.js) | Vercel (Vue.js SPA) | Same platform, different build |
| Backend Hosting | Railway / Docker | Vercel Serverless Functions | Express runs as serverless |
| Database | PostgreSQL (self-hosted/Railway) | Supabase PostgreSQL | Managed database |
| File Storage | S3-compatible | Supabase Storage (deferred) | Build later |
| Email | SMTP (React Email) | SMTP (Nodemailer) | Simpler stack |
| Redis | Optional (rate limiting) | Deferred | In-memory rate limiting initially |
| CI/CD | GitHub Actions | GitHub Actions | Same |
| Monitoring | Basic logging | Deferred | Add Sentry later |
| Backup | Manual | Supabase auto-backups | Managed |

---

## 7. Summary: What Changes, What Stays, What's New

### STAYS (Same technology, direct migration)
- PostgreSQL database
- Drizzle ORM
- Better Auth
- Tailwind CSS
- Zod validation
- Card indexing logic
- Activity logging pattern
- Soft delete pattern

### CHANGES (Different technology, reimplementation)
- Frontend: React → Vue.js
- Build: Next.js → Vite
- API: tRPC → REST (Express)
- Deployment: Monolith → Serverless (Vercel)
- i18n: Lingui → Vue I18n (later)
- Monorepo → Separate directories

### NEW (Not in old system)
- Express.js server
- Vue.js components
- Vite build system
- Serverless backend functions
- Supabase managed PostgreSQL

### DEFERRED (In old system, build later in new)
- Attachments (S3 storage)
- Templates
- Notifications
- Public boards
- Trello import
- MCP server
- Webhooks
- API keys
- Stripe billing
- Rich text editor
- Command palette
- Additional OAuth providers
- i18n (multiple languages)
