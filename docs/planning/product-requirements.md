# Product Requirements Document — ManagPro v2 (Vue.js Revamp)

> **Status:** PLANNING — Dibuat sebelum development.
> **Purpose:** Single source of truth untuk visi produk, user stories, dan fitur yang akan dibangun.

---

## 1. Product Vision

### Elevator Pitch

**ManagPro v2 adalah open-source project management tool (Trello alternative) yang dibangun ulang dengan Vue.js, Vite, Express.js, dan Supabase PostgreSQL — menghadirkan pengalaman kanban yang simpel, cepat, dan mudah dikembangkan.**

### Problem Statement

Sistem lama (Next.js + tRPC) memiliki kompleksitas berlebih untuk use case SPA. Server-side rendering tidak diperlukan, tRPC menambah overhead, dan monorepo structure terlalu kompleks untuk tim kecil. Revamp ini menyederhanakan stack tanpa mengurangi fitur inti.

### Target Users

| Role | Deskripsi | Kebutuhan Utama |
|------|----------|----------------|
| Individual Users | Pengguna personal untuk produktivitas | Board management, task tracking |
| Team Members | Kolaborator dalam workspace | Card assignment, comments, activity |
| Workspace Admins | Pengelola workspace | Member management, settings |
| Guests | Akses terbatas ke public boards | View-only access |

---

## 2. User Stories / Use Cases

### Epic 1: Authentication

| ID | User Story | Priority | Acceptance Criteria |
|----|-----------|----------|-------------------|
| US-001 | Sebagai user, saya ingin mendaftar dengan email dan password | P0 | Form validasi, email verification, redirect ke dashboard |
| US-002 | Sebagai user, saya ingin login dengan email dan password | P0 | Session management, redirect ke dashboard |
| US-003 | Sebagai user, saya ingin login dengan Google OAuth | P0 | OAuth flow, auto-create account |
| US-004 | Sebagai user, saya ingin login dengan GitHub OAuth | P0 | OAuth flow, auto-create account |
| US-005 | Sebagai user, saya ingin logout | P0 | Session destroyed, redirect ke login |

### Epic 2: Workspace Management

| ID | User Story | Priority | Acceptance Criteria |
|----|-----------|----------|-------------------|
| US-010 | Sebagai user, saya ingin membuat workspace baru | P0 | Name + slug, auto-add as admin |
| US-011 | Sebagai user, saya ingin melihat daftar workspace saya | P0 | List all workspaces where member |
| US-012 | Sebagai admin, saya ingin mengedit workspace | P0 | Update name, slug, description |
| US-013 | Sebagai admin, saya ingin menghapus workspace | P1 | Soft delete, confirm dialog |

### Epic 3: Board Management

| ID | User Story | Priority | Acceptance Criteria |
|----|-----------|----------|-------------------|
| US-020 | Sebagai member, saya ingin membuat board di workspace | P0 | Name, slug, visibility setting |
| US-021 | Sebagai member, saya ingin melihat daftar board | P0 | Grid/list view, filter by workspace |
| US-022 | Sebagai member, saya ingin membuka board | P0 | Redirect to board view with lists |
| US-023 | Sebagai member, saya ingin mengedit board | P1 | Update name, description, visibility |
| US-024 | Sebagai member, saya ingin menghapus board | P1 | Soft delete, confirm |

### Epic 4: List Management

| ID | User Story | Priority | Acceptance Criteria |
|----|-----------|----------|-------------------|
| US-030 | Sebagai member, saya ingin membuat list di board | P0 | Name, auto-position at end |
| US-031 | Sebagai member, saya ingin mengedit list | P0 | Inline edit name |
| US-032 | Sebagai member, saya ingin menghapus list | P0 | Soft delete, confirm |
| US-033 | Sebagai member, saya ingin mengatur urutan list | P1 | Drag and drop reordering |

### Epic 5: Card Management

| ID | User Story | Priority | Acceptance Criteria |
|----|-----------|----------|-------------------|
| US-040 | Sebagai member, saya ingin membuat card di list | P0 | Title, auto-number, append to list |
| US-041 | Sebagai member, saya ingin membuka detail card | P0 | Modal/page with all card details |
| US-042 | Sebagai member, saya ingin mengedit card | P0 | Title, description (plain text initially) |
| US-043 | Sebagai member, saya ingin memindahkan card antar list | P0 | Drag and drop, update indices |
| US-044 | Sebagai member, saya ingin menghapus card | P0 | Soft delete, confirm |
| US-045 | Sebagai member, saya ingin mengatur due date card | P1 | Date picker, display on card |
| US-046 | Sebagai member, saya ingin melabeli card | P0 | Add/remove labels, color display |
| US-047 | Sebagai member, saya ingin assign member ke card | P0 | Add/remove members, avatar display |

### Epic 6: Collaboration

| ID | User Story | Priority | Acceptance Criteria |
|----|-----------|----------|-------------------|
| US-050 | Sebagai member, saya ingin menambah comment di card | P0 | Text comment, timestamp |
| US-051 | Sebagai member, saya ingin mengedit comment | P1 | Edit own comments |
| US-052 | Sebagai member, saya ingin menghapus comment | P1 | Delete own comments |
| US-053 | Sebagai member, saya ingin menambah checklist di card | P0 | Multiple checklists, items |
| US-054 | Sebagai member, saya ingin toggle item completion | P0 | Checkbox, progress tracking |
| US-055 | Sebagai admin, saya ingin mengundang member | P1 | Email invite, role assignment |

### Epic 7: Activity & Filtering

| ID | User Story | Priority | Acceptance Criteria |
|----|-----------|----------|-------------------|
| US-060 | Sebagai member, saya ingin melihat activity log card | P0 | Chronological list of changes |
| US-061 | Sebagai member, saya ingin memfilter board | P1 | Filter by member, label |

---

## 3. Feature List

### MVP (Minimum Viable Product)

| # | Fitur | Deskripsi | Epic |
|---|-------|----------|------|
| 1 | Email/Password Auth | Registration, login, logout | Auth |
| 2 | OAuth (Google, GitHub) | Social login | Auth |
| 3 | Workspace CRUD | Create, read, update, delete workspaces | Workspace |
| 4 | Board CRUD | Create, read, update, delete boards | Board |
| 5 | List CRUD | Create, read, update, delete lists | List |
| 6 | List Reordering | Drag and drop | List |
| 7 | Card CRUD | Create, read, update, delete cards | Card |
| 8 | Card Movement | Drag between lists | Card |
| 9 | Labels | Create and assign labels | Card |
| 10 | Member Assignment | Assign members to cards | Card |
| 11 | Checklists | Sub-task lists with completion | Collaboration |
| 12 | Comments | Discussion on cards | Collaboration |
| 13 | Activity Log | Track all card changes | Activity |
| 14 | Board Filters | Filter by member, label | Filtering |
| 15 | Dark Mode | Light/dark theme toggle | UI |
| 16 | Responsive Design | Mobile-friendly layout | UI |

### Post-MVP / Future

| # | Fitur | Deskripsi | Prioritas |
|---|-------|----------|----------|
| 1 | Rich Text Editor | Markdown/WYSIWYG for card descriptions | High |
| 2 | Attachments | File uploads via Supabase Storage | High |
| 3 | Due Date Filter | Filter by overdue, due today, etc. | Medium |
| 4 | Templates | Reusable board templates | Medium |
| 5 | Notifications | In-app notification system | Medium |
| 6 | Public Boards | Share boards publicly | Medium |
| 7 | Trello Import | Import from Trello | Medium |
| 8 | RBAC | Custom roles and permissions | Medium |
| 9 | Webhooks | Event subscriptions | Low |
| 10 | API Keys | Programmatic access | Low |
| 11 | i18n | Multiple languages | Low |
| 12 | Command Palette | Global search (Ctrl+K) | Low |
| 13 | MCP Server | AI client integration | Low |

---

## 4. Constraints & Assumptions

### Constraints
- Deploy target: Vercel (serverless)
- Database: Supabase PostgreSQL (managed)
- Auth: Better Auth (must be compatible)
- Frontend: Vue.js 3 (no React)
- API: REST (no tRPC)

### Assumptions
- Supabase PostgreSQL supports all required features (RLS, extensions)
- Better Auth works with Express.js middleware pattern
- Vercel serverless functions can handle Express.js backend
- Users have modern browsers (ES2020+)

---

## 5. Success Metrics

| Metric | Target | Cara Ukur |
|--------|--------|----------|
| Auth Flow | < 2s login time | Performance monitoring |
| Board Load | < 1s initial load | Lighthouse score |
| Card Operations | < 500ms response | API response time |
| Build Time | < 30s frontend build | Vercel build logs |
| Bundle Size | < 200KB gzipped | Vite bundle analyzer |

---

## 6. Out of Scope

**Yang TIDAK akan dibangun di versi ini:**
- Attachments / file uploads
- Rich text editor
- Templates
- Notifications
- Public boards
- Trello import
- RBAC (custom roles)
- Webhooks
- API keys
- Stripe billing
- MCP server
- i18n (multiple languages)
- Command palette
