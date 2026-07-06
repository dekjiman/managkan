# Architecture Plan — ManagPro v2

> **Status:** PLANNING — Dibuat sebelum development.
> **Purpose:** Cetak biru teknis: stack, system design, komponen, deployment architecture.

---

## Project Type Declaration

| Item | Value |
|------|-------|
| Project Type | Fullstack (separate frontend + backend) |
| Git Location | `frontend/` dan `backend/` — single repo, dual directory |

---

## 1. Tech Stack

| Layer | Technology | Version | Rationale |
|-------|-----------|---------|----------|
| Frontend | Vue.js | 3.4+ | Reactive, simple, great ecosystem |
| Build Tool | Vite | 5.x | Fast HMR, optimized builds |
| Styling | Tailwind CSS | 3.4+ | Utility-first, same as old system |
| Backend | Express.js | 4.x | Lightweight, flexible, widely used |
| Database | PostgreSQL | 15+ | Same as old system, RLS support |
| ORM | Drizzle ORM | Latest | Same as old system |
| Auth | Better Auth | Latest | Same as old system, Express compatible |
| Validation | Zod | Latest | Same as old system |
| Hosting | Vercel | — | Serverless frontend + backend |
| Database Host | Supabase | — | Managed PostgreSQL |

---

## 2. System Design

### Architecture Pattern

**Layered Architecture (Frontend SPA + Backend REST API)**

**Alasan:** Clean separation of concerns, independent deployment, standard REST patterns, easier to scale frontend and backend independently.

### High-Level Diagram

```text
┌─────────────────────────────────────────────────────────┐
│                      VERCEL                             │
│                                                         │
│  ┌──────────────────┐    ┌──────────────────────────┐   │
│  │   Frontend SPA   │    │   Backend API (Serverless)│   │
│  │   (Vue.js)       │    │   (Express.js)            │   │
│  │                  │    │                            │   │
│  │   /index.html    │    │   /api/v1/*                │   │
│  │   /assets/*      │    │                            │   │
│  │                  │    │   ┌────────────────────┐   │   │
│  │   Vue Router     │    │   │   Better Auth       │   │   │
│  │   Pinia Store    │    │   │   (middleware)      │   │   │
│  │   Axios Client   │◄──►│   └────────────────────┘   │   │
│  │                  │    │                            │   │
│  └──────────────────┘    │   ┌────────────────────┐   │   │
│                          │   │   Drizzle ORM       │   │   │
│                          │   │   (query builder)   │   │   │
│                          │   └─────────┬──────────┘   │   │
│                          └─────────────┼──────────────┘   │
│                                        │                  │
└────────────────────────────────────────┼──────────────────┘
                                         │
                                         ▼
                          ┌──────────────────────────┐
                          │   Supabase PostgreSQL     │
                          │   (managed database)      │
                          └──────────────────────────┘
```

---

## 3. Component Breakdown

### Frontend Components

| Komponen | Tipe | Tanggung Jawab | Teknologi |
|----------|------|---------------|----------|
| Auth Pages | View | Login, Register | Vue.js + Tailwind |
| Dashboard | View | Workspace overview | Vue.js + Tailwind |
| Board View | View | Kanban board with lists/cards | Vue.js + Tailwind + Drag & Drop |
| Card Detail | Component | Card edit modal/panel | Vue.js + Tailwind |
| Settings | View | Account & workspace settings | Vue.js + Tailwind |
| Layout | Component | Sidebar, header, navigation | Vue.js + Tailwind |
| Shared UI | Component | Button, Input, Modal, etc. | Vue.js + Tailwind |

### Backend Components

| Komponen | Tipe | Tanggung Jawab | Teknologi |
|----------|------|---------------|----------|
| Auth Routes | Route | Login, register, OAuth | Better Auth + Express |
| Workspace Routes | Route | CRUD workspaces | Express + Drizzle |
| Board Routes | Route | CRUD boards | Express + Drizzle |
| List Routes | Route | CRUD lists + reorder | Express + Drizzle |
| Card Routes | Route | CRUD cards + move | Express + Drizzle |
| Label Routes | Route | CRUD labels | Express + Drizzle |
| Checklist Routes | Route | CRUD checklists | Express + Drizzle |
| Comment Routes | Route | CRUD comments | Express + Drizzle |
| Member Routes | Route | Member management | Express + Drizzle |
| User Routes | Route | User profile | Express + Drizzle |
| Auth Middleware | Middleware | Session verification | Better Auth |
| Error Handler | Middleware | Global error handling | Express |

---

## 4. Routing Strategy

### Backend REST API

```
Base URL: /api/v1

Auth (Better Auth):
  POST   /api/auth/sign-up
  POST   /api/auth/sign-in
  POST   /api/auth/sign-out
  GET    /api/auth/session
  GET    /api/auth/google (OAuth)
  GET    /api/auth/github (OAuth)

Workspaces:
  GET    /api/v1/workspaces
  POST   /api/v1/workspaces
  GET    /api/v1/workspaces/:publicId
  PATCH  /api/v1/workspaces/:publicId
  DELETE /api/v1/workspaces/:publicId

Boards:
  GET    /api/v1/boards?workspaceId=
  POST   /api/v1/boards
  GET    /api/v1/boards/:publicId
  PATCH  /api/v1/boards/:publicId
  DELETE /api/v1/boards/:publicId

Lists:
  GET    /api/v1/lists?boardId=
  POST   /api/v1/lists
  PATCH  /api/v1/lists/:publicId
  DELETE /api/v1/lists/:publicId
  PUT    /api/v1/lists/reorder

Cards:
  GET    /api/v1/cards?listId=
  POST   /api/v1/cards
  GET    /api/v1/cards/:publicId
  PATCH  /api/v1/cards/:publicId
  DELETE /api/v1/cards/:publicId
  PUT    /api/v1/cards/:publicId/move

Labels:
  GET    /api/v1/labels?boardId=
  POST   /api/v1/labels
  PATCH  /api/v1/labels/:publicId
  DELETE /api/v1/labels/:publicId

Checklists:
  GET    /api/v1/checklists?cardId=
  POST   /api/v1/checklists
  PATCH  /api/v1/checklists/:publicId
  DELETE /api/v1/checklists/:publicId

Checklist Items:
  POST   /api/v1/checklists/:checklistId/items
  PATCH  /api/v1/checklist-items/:publicId
  DELETE /api/v1/checklist-items/:publicId

Comments:
  GET    /api/v1/comments?cardId=
  POST   /api/v1/comments
  PATCH  /api/v1/comments/:publicId
  DELETE /api/v1/comments/:publicId

Members:
  GET    /api/v1/members?workspaceId=
  POST   /api/v1/members
  PATCH  /api/v1/members/:publicId
  DELETE /api/v1/members/:publicId

Users:
  GET    /api/v1/users/me
  PATCH  /api/v1/users/me
```

### Frontend Routes

```
/                        → Landing/redirect
/login                   → Login page
/register                → Register page
/dashboard               → Workspace list
/:workspaceSlug          → Workspace boards
/:workspaceSlug/:boardSlug → Board view
/settings/account        → Account settings
/settings/workspace      → Workspace settings
```

---

## 5. Data Flow

### Authentication Flow

```text
[User Login] → [Vue Form] → [POST /api/auth/sign-in] → [Better Auth]
                                    ↓
                            [Session Cookie Set]
                                    ↓
                            [GET /api/auth/session]
                                    ↓
                            [User Data Returned]
                                    ↓
                            [Vue Store Updated]
```

### Card Operation Flow

```text
[User Drag Card] → [Vue DnD Event] → [PUT /api/v1/cards/:id/move]
                                            ↓
                                    [Express Route]
                                            ↓
                                    [Auth Middleware]
                                            ↓
                                    [Validation (Zod)]
                                            ↓
                                    [Card Service]
                                            ↓
                                    [Drizzle Query]
                                            ↓
                                    [PostgreSQL]
                                            ↓
                                    [Activity Log Created]
                                            ↓
                                    [Response → Vue]
```

---

## 6. Security Plan

| Area | Strategy |
|------|----------|
| Authentication | Better Auth with session cookies |
| Authorization | Workspace membership check middleware |
| CSRF | Better Auth CSRF protection |
| XSS | Vue.js template escaping + DOMPurify for rich text |
| SQL Injection | Drizzle ORM parameterized queries |
| File Upload | Deferred (Supabase Storage later) |
| Rate Limiting | In-memory initially, Redis later |
| CORS | Configurable origins in Express |

---

## 7. Deployment Architecture

```
[GitHub Push] → [Vercel CI/CD] → [Build Frontend (Vite)]
                                → [Build Backend (tsc)]
                                → [Deploy to Vercel Edge/Functions]
                                        ↓
                                [Frontend: Vercel CDN]
                                [Backend: Vercel Serverless]
                                        ↓
                                [Supabase PostgreSQL]
```

### Environment

| Environment | Server | Domain | Branch |
|------------|--------|--------|--------|
| Local | localhost:5173 (FE) :3000 (BE) | — | `dev/feat` |
| Preview | Vercel Preview | `*.vercel.app` | PR branches |
| Production | Vercel Production | `managpro.example.com` | `main` |

---

## 8. Key Decisions

| # | Keputusan | Alasan | ADR |
|---|----------|--------|-----|
| 1 | Vue.js over React | Simpler, faster builds, user preference | — |
| 2 | REST over tRPC | Standard API, easier external consumption | — |
| 3 | Separate FE/BE dirs | Simpler than monorepo for this scope | — |
| 4 | Vercel Serverless | Managed, auto-scaling, good Vue support | — |
| 5 | Supabase PostgreSQL | Managed DB, built-in auth storage, backups | — |
| 6 | Better Auth (same) | Proven, works with Express, feature-rich | — |
| 7 | Drizzle ORM (same) | Proven, type-safe, works with Supabase PG | — |
| 8 | Zod (same) | Proven validation, works in both stacks | — |
