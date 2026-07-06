# ManagPro v2 — Documentation

An open-source project management tool (Trello alternative) built with Vue.js 3, Express.js, and PostgreSQL.

---

## Table of Contents

1. [Tech Stack](#tech-stack)
2. [Project Structure](#project-structure)
3. [Setup & Installation](#setup--installation)
4. [Environment Variables](#environment-variables)
5. [Database Schema](#database-schema)
6. [Authentication](#authentication)
7. [API Endpoints](#api-endpoints)
8. [Billing & Subscription](#billing--subscription)
9. [Frontend Architecture](#frontend-architecture)
10. [Features](#features)
11. [Development](#development)
12. [Deployment](#deployment)

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Frontend | Vue.js 3, Vite, Tailwind CSS, Pinia |
| Backend | Express.js, TypeScript |
| Auth | Better Auth (email/password + OAuth) |
| ORM | Drizzle ORM |
| Database | PostgreSQL |
| Email | Nodemailer (Gmail SMTP) |
| Payments | Midtrans (Snap) |
| Drag & Drop | vuedraggable (SortableJS) |

---

## Project Structure

```
managkan/
├── backend/                    # Express.js API server
│   ├── src/
│   │   ├── index.ts            # Server entry point
│   │   ├── auth.ts             # Better Auth configuration
│   │   ├── config/
│   │   │   └── env.ts          # Environment variable validation (Zod)
│   │   ├── db/
│   │   │   ├── index.ts        # Database connection
│   │   │   └── schema/         # Drizzle ORM schemas
│   │   │       ├── auth.ts         # users, sessions, accounts, verification
│   │   │       ├── workspaces.ts   # workspace, workspace_members
│   │   │       ├── boards.ts       # boards
│   │   │       ├── lists.ts        # lists
│   │   │       ├── cards.ts        # cards
│   │   │       ├── labels.ts       # labels, card_labels
│   │   │       ├── checklists.ts   # card_checklists, card_checklist_items
│   │   │       ├── comments.ts     # card_comments
│   │   │       ├── activity.ts     # activity_log
│   │   │       ├── attachments.ts  # card_attachments
│   │   │       ├── apikeys.ts      # api_keys
│   │   │       ├── webhooks.ts     # webhooks
│   │   │       ├── integrations.ts # integrations
│   │   │       ├── plans.ts        # plans (Free/Team/Pro)
│   │   │       └── subscriptions.ts # subscriptions (payment records)
│   │   ├── routes/             # API route handlers
│   │   ├── services/           # Business logic
│   │   ├── middleware/         # auth.ts, error.ts
│   │   ├── email/              # Nodemailer email templates
│   │   ├── types/              # TypeScript declarations
│   │   └── utils/              # publicId, wrap, etc.
│   ├── uploads/                # Uploaded files
│   ├── package.json
│   └── tsconfig.json
│
├── frontend/                   # Vue.js SPA
│   ├── src/
│   │   ├── main.ts             # App entry point
│   │   ├── App.vue             # Root component
│   │   ├── router/             # Vue Router config
│   │   ├── stores/             # Pinia stores (workspace, ui)
│   │   ├── services/           # Axios API client modules
│   │   ├── composables/        # Vue composables (useAuth, useOAuth, useToast, useModal)
│   │   ├── views/              # Page-level components
│   │   │   ├── auth/           # Login, Register, ForgotPassword, etc.
│   │   │   ├── board/          # BoardDetailView, CardDetailView
│   │   │   ├── dashboard/      # DashboardView
│   │   │   ├── settings/       # Account, Billing, API, Webhooks, etc.
│   │   │   ├── workspace/      # BoardList, Members, Templates, etc.
│   │   │   └── NotFoundView.vue
│   │   ├── components/         # Reusable components
│   │   │   ├── ui/             # Button, Input, Modal, Badge, Avatar, etc.
│   │   │   ├── layout/         # AppLayout, Sidebar, SettingsLayout
│   │   │   ├── board/          # Board-specific components
│   │   │   ├── card/           # Card-specific components
│   │   │   ├── list/           # List-specific components
│   │   │   └── workspace/      # Workspace-specific components
│   │   ├── types/              # TypeScript interfaces
│   │   └── utils/              # date.ts, etc.
│   ├── index.html
│   ├── package.json
│   └── vite.config.ts
│
├── planning/                   # Planning documents
│   └── billing.md              # Billing flow documentation
│
└── README.md
```

---

## Setup & Installation

### Prerequisites

- Node.js 18+
- PostgreSQL (local or Supabase)

### 1. Clone & Install

```bash
git clone <repo-url>
cd managkan

# Backend
cd backend
npm install

# Frontend
cd ../frontend
npm install
```

### 2. Environment Setup

```bash
# Backend
cd backend
cp .env.example .env
# Edit .env with your values (see Environment Variables below)

# Frontend
cd ../frontend
cp .env.example .env
```

### 3. Database Setup

```bash
cd backend

# Push schema to database
npm run db:push

# Or run SQL migrations manually
psql -U postgres -d db_kanban -h localhost -f <migration-file>.sql
```

### 4. Start Development

```bash
# Terminal 1 — Backend (port 3000)
cd backend
npm run dev

# Terminal 2 — Frontend (port 5173)
cd frontend
npm run dev
```

Open [http://localhost:5173](http://localhost:5173)

---

## Environment Variables

### Backend (`backend/.env`)

| Variable | Required | Default | Description |
|----------|----------|---------|-------------|
| `DATABASE_URL` | Yes | — | PostgreSQL connection URL |
| `BETTER_AUTH_SECRET` | Yes | — | Auth secret (32+ chars) |
| `BETTER_AUTH_URL` | Yes | `http://localhost:3000` | Backend URL |
| `FRONTEND_URL` | Yes | `http://localhost:5173` | Frontend URL for CORS/redirects |
| `PORT` | No | `3000` | Server port |
| `NODE_ENV` | No | `development` | `development`, `production`, or `test` |
| `SMTP_HOST` | Yes | — | Email SMTP host (e.g., `smtp.gmail.com`) |
| `SMTP_PORT` | No | `587` | SMTP port |
| `SMTP_USER` | Yes | — | SMTP username/email |
| `SMTP_PASS` | Yes | — | SMTP password/app password |
| `MAIL_FROM_NAME` | No | `ManagPro` | Sender name |
| `GOOGLE_CLIENT_ID` | No | — | Google OAuth client ID |
| `GOOGLE_CLIENT_SECRET` | No | — | Google OAuth client secret |
| `GITHUB_CLIENT_ID` | No | — | GitHub OAuth client ID |
| `GITHUB_CLIENT_SECRET` | No | — | GitHub OAuth client secret |
| `MIDTRANS_SERVER_KEY` | Yes | — | Midtrans payment server key |
| `MIDTRANS_CLIENT_KEY` | Yes | — | Midtrans payment client key |
| `MIDTRANS_IS_PRODUCTION` | No | `FALSE` | `TRUE` for production, `FALSE` for sandbox |

### Frontend (`frontend/.env`)

| Variable | Required | Description |
|----------|----------|-------------|
| `VITE_API_URL` | No | Backend API URL (default: `/api`) |

---

## Database Schema

### Auth Tables (Better Auth)

| Table | Purpose |
|-------|---------|
| `user` | User accounts (id, name, email, emailVerified, image) |
| `session` | Active sessions (token, userId, expiresAt) |
| `account` | OAuth provider accounts (userId, providerId) |
| `verification` | Email verification / password reset tokens |

### Core Tables

| Table | Purpose |
|-------|---------|
| `workspace` | Workspaces (name, slug, description, plan, createdBy) |
| `workspace_members` | Membership (userId, workspaceId, role, status, inviteCode, email) |
| `board` | Boards (name, slug, type, visibility, workspaceId) |
| `list` | Lists within boards (name, index, boardId) |
| `card` | Cards within lists (title, description, index, listId, dueDate, cardNumber) |
| `label` | Labels per board (name, color, boardId) |
| `card_labels` | Card-label junction |
| `card_members` | Card-assignee junction |
| `card_checklists` | Checklists on cards (name, cardId) |
| `card_checklist_items` | Checklist items (name, checked, checklistId) |
| `card_comments` | Comments on cards (content, cardId, userId) |
| `card_attachments` | File attachments (filename, size, contentType, path, cardId) |
| `activity_log` | Activity feed (action, entityType, entityId, userId, workspaceId) |

### Extension Tables

| Table | Purpose |
|-------|---------|
| `plans` | Subscription plans (name, price, boardLimit, memberLimit, storageLimit, features) |
| `subscriptions` | Payment records (workspaceId, plan, status, midtransOrderId, endDate) |
| `api_keys` | API keys (name, keyHash, keyPrefix, workspaceId, permissions) |
| `webhooks` | Webhook subscriptions (url, events, workspaceId) |
| `integrations` | Third-party integrations |

### Roles & Permissions

| Role | Can |
|------|-----|
| `admin` | Full workspace access, manage members, billing, settings |
| `member` | Create/edit boards, cards, lists |
| `guest` | View-only access |

### Plan Limits

| Plan | Price | Boards | Members | Storage |
|------|-------|--------|---------|---------|
| Free | Rp 0 | 3 | 3 | 10 MB |
| Team | Rp 99.000/month | 20 | 10 | 500 MB |
| Pro | Rp 299.000/month | Unlimited | Unlimited | 5 GB |

---

## Authentication

### Email/Password

- **Register**: `POST /api/auth/sign-up` — creates user, sends verification email
- **Login**: `POST /api/auth/sign-in` — returns session cookie
- **Logout**: `POST /api/auth/sign-out` — invalidates session

### Email Verification

- Sent automatically on signup (`sendOnSignUp: true`)
- User must verify before login (`requireEmailVerification: true`)
- Verification link redirects to frontend
- Resend: `POST /api/auth/send-verification-email`

### Forgot / Reset Password

1. User requests reset: `POST /api/auth/forgot-password`
2. System sends email with reset link (`/reset-password?token=...`)
3. User sets new password: `POST /api/auth/reset-password`

### OAuth (Google / GitHub)

1. Frontend redirects to `/api/auth/signin/google` or `/api/auth/signin/github`
2. OAuth callback handled by Better Auth
3. User auto-created if new, session set via cookie
4. If user already exists with same email, accounts are linked

### Invite System

1. Admin invites via email: `POST /api/members` with `{ email, role }`
2. System generates invite code, sends invite email with link
3. **New user**: Signs up via `/invite/:code` — account created, auto-signed-in, redirected to dashboard
4. **Existing user**: Receives notification email, member added to workspace
5. Resend invite: `POST /api/members/:publicId/resend-invite`
6. Accept invite: `POST /api/members/invite-signup` with `{ inviteCode, name, password }`

---

## API Endpoints

Base URL: `http://localhost:3000/api`

### Auth (`/api/auth/*`)

Handled by Better Auth. See [Better Auth docs](https://www.better-auth.com/docs).

### Workspaces

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/workspaces` | Yes | List user's workspaces |
| `POST` | `/workspaces` | Yes | Create workspace |
| `GET` | `/workspaces/:slug` | Yes | Get workspace by slug |
| `PATCH` | `/workspaces/:slug` | Yes | Update workspace |
| `DELETE` | `/workspaces/:slug` | Yes | Delete workspace (admin) |

### Boards

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/workspaces/:wsId/boards` | Yes | List boards in workspace |
| `POST` | `/workspaces/:wsId/boards` | Yes | Create board |
| `GET` | `/boards/:slug` | Yes | Get board with lists & cards |
| `PATCH` | `/boards/:publicId` | Yes | Update board |
| `DELETE` | `/boards/:publicId` | Yes | Delete board |

### Lists

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `POST` | `/lists` | Yes | Create list |
| `PATCH` | `/lists/:publicId` | Yes | Update list |
| `DELETE` | `/lists/:publicId` | Yes | Delete list |
| `PATCH` | `/lists/reorder` | Yes | Reorder lists |

### Cards

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `POST` | `/cards` | Yes | Create card |
| `PATCH` | `/cards/:publicId` | Yes | Update card |
| `DELETE` | `/cards/:publicId` | Yes | Delete card |
| `PATCH` | `/cards/move` | Yes | Move card between lists |
| `POST` | `/cards/:publicId/labels` | Yes | Add label to card |
| `DELETE` | `/cards/:publicId/labels/:labelId` | Yes | Remove label |
| `POST` | `/cards/:publicId/members` | Yes | Assign member |
| `DELETE` | `/cards/:publicId/members/:memberId` | Yes | Unassign member |

### Checklists

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `POST` | `/checklists` | Yes | Create checklist |
| `PATCH` | `/checklists/:publicId` | Yes | Update checklist |
| `DELETE` | `/checklists/:publicId` | Yes | Delete checklist |
| `POST` | `/checklists/:publicId/items` | Yes | Add item |
| `PATCH` | `/checklists/items/:publicId` | Yes | Toggle item |
| `DELETE` | `/checklists/items/:publicId` | Yes | Delete item |

### Comments

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/comments?cardId=` | Yes | List comments on card |
| `POST` | `/comments` | Yes | Add comment |
| `DELETE` | `/comments/:publicId` | Yes | Delete comment |

### Members

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/members?workspaceId=` | Yes | List workspace members |
| `POST` | `/members` | Yes (admin) | Invite member |
| `PATCH` | `/members/:publicId` | Yes (admin) | Update member role |
| `DELETE` | `/members/:publicId` | Yes (admin) | Remove member |
| `POST` | `/members/:publicId/resend-invite` | Yes (admin) | Resend invite email |
| `GET` | `/members/invite/:code` | Yes | Get invite details |
| `POST` | `/members/invite-signup` | Yes | Accept invite + create account |

### Labels

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `POST` | `/labels` | Yes | Create label |
| `PATCH` | `/labels/:publicId` | Yes | Update label |
| `DELETE` | `/labels/:publicId` | Yes | Delete label |

### Attachments

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `POST` | `/attachments` | Yes | Upload file (multipart) |
| `GET` | `/attachments/:publicId` | Yes | Download file |
| `DELETE` | `/attachments/:publicId` | Yes | Delete attachment |

### Activity

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/activities?workspaceId=` | Yes | List activity feed |

### Plans (Public)

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/plans` | No | List all active plans |
| `GET` | `/plans/:name` | No | Get plan by name |

### Billing

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/billing?workspaceSlug=` | Yes | Get subscription + usage |
| `POST` | `/billing/checkout` | Yes (admin) | Create Midtrans Snap token |
| `POST` | `/billing/notification` | No (webhook) | Midtrans payment notification |

### API Keys

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/api-keys?workspaceSlug=` | Yes | List API keys |
| `POST` | `/api-keys` | Yes | Create API key |
| `DELETE` | `/api-keys/:publicId` | Yes | Revoke API key |

### Webhooks

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/webhooks?workspaceSlug=` | Yes | List webhooks |
| `POST` | `/webhooks` | Yes | Create webhook |
| `DELETE` | `/webhooks/:publicId` | Yes | Delete webhook |

### Integrations

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/integrations?workspaceSlug=` | Yes | List integrations |
| `POST` | `/integrations` | Yes | Create integration |
| `DELETE` | `/integrations/:publicId` | Yes | Delete integration |

### Users

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/users/me` | Yes | Get current user |
| `PATCH` | `/users/me` | Yes | Update profile |

---

## Billing & Subscription

See [planning/billing.md](planning/billing.md) for detailed flow diagrams.

### Payment Flow

1. User clicks **Upgrade** on billing page
2. Frontend calls `POST /api/billing/checkout` with `{ planName: "team" }` or `"pro"`
3. Backend creates subscription record + Midtrans Snap transaction
4. Frontend opens Midtrans Snap popup
5. User pays via their preferred method (VA, e-wallet, credit card, etc.)
6. Midtrans sends webhook to `POST /api/billing/notification`
7. Backend activates subscription, updates workspace plan

### Subscription Statuses

| Status | Meaning |
|--------|---------|
| `pending` | Payment initiated, awaiting confirmation |
| `active` | Payment confirmed, plan active |
| `failed` | Payment failed/expired/cancelled |

---

## Frontend Architecture

### Routing

| Path | Component | Description |
|------|-----------|-------------|
| `/login` | LoginView | Email/password + OAuth login |
| `/register` | RegisterView | New account registration |
| `/forgot-password` | ForgotPasswordView | Request password reset |
| `/reset-password` | ResetPasswordView | Set new password |
| `/invite/:code` | InviteSignupView | Accept workspace invite |
| `/verify-email` | VerifyEmailNoticeView | Email verification notice |
| `/` | DashboardView | User dashboard |
| `/settings/account` | AccountSettingsView | Profile settings |
| `/:slug` | WorkspaceView | Workspace home |
| `/:slug/settings` | WorkspaceSettingsView | Workspace settings |
| `/:slug/settings/billing` | BillingView | Billing & subscription |
| `/:slug/settings/permissions` | PermissionsView | Role management |
| `/:slug/settings/api` | ApiSettingsView | API key management |
| `/:slug/settings/webhooks` | WebhooksView | Webhook management |
| `/:slug/settings/integrations` | IntegrationsView | Third-party integrations |
| `/:slug/boards` | BoardListView | List all boards |
| `/:slug/members` | MembersView | Manage members |
| `/:slug/templates` | TemplatesView | Board templates |
| `/:slug/templates/:templateId` | TemplateDetailView | Template detail |
| `/:slug/:boardSlug` | BoardDetailView | Kanban board view |
| `/:pathMatch(.*)*` | NotFoundView | 404 page |

### Stores (Pinia)

| Store | Purpose |
|-------|---------|
| `workspace` | Current workspace, workspace list |
| `ui` | Sidebar state, theme |

### Composables

| Composable | Purpose |
|------------|---------|
| `useAuth` | Login, register, logout, OAuth |
| `useOAuth` | OAuth sign-in with error handling |
| `useToast` | Success/error toast notifications |
| `useModal` | Modal open/close state |

### Services (Axios)

| Service | API Module |
|---------|-----------|
| `auth.service.ts` | Auth endpoints |
| `workspace.service.ts` | Workspace CRUD |
| `board.service.ts` | Board CRUD + templates |
| `list.service.ts` | List CRUD + reorder |
| `card.service.ts` | Card CRUD + move |
| `label.service.ts` | Label CRUD |
| `checklist.service.ts` | Checklist CRUD |
| `comment.service.ts` | Comment CRUD |
| `member.service.ts` | Member management |
| `attachment.service.ts` | File upload/download |
| `activity.service.ts` | Activity feed |
| `billing.service.ts` | Billing + checkout |
| `plan.service.ts` | Plans listing |
| `apikeys.service.ts` | API key management |
| `webhooks.service.ts` | Webhook management |
| `integrations.service.ts` | Integration management |
| `user.service.ts` | User profile |

---

## Features

### Core

- Email/password authentication
- Google & GitHub OAuth
- Email verification
- Forgot/reset password
- Workspace management (create, update, delete)
- Board management (create, update, delete, templates)
- List management (create, reorder, drag-and-drop)
- Card management (create, edit, drag-and-drop between lists)
- Labels (color-coded, per board)
- Member assignment (assign users to cards)
- Checklists (with completion tracking)
- Comments (on cards)
- File attachments (upload/download)
- Activity logging (all changes tracked)
- 404 page

### Collaboration

- Workspace roles (admin, member, guest)
- Invite system (email invites with codes)
- Resend invite
- Real-time activity feed
- Member management

### Billing

- 3-tier plans (Free, Team, Pro) from database
- Midtrans Snap payment integration
- Plan limits enforcement (boards, members, storage)
- Webhook-based payment confirmation
- Subscription history

### UI/UX

- Dark mode (toggle)
- Responsive design (mobile-friendly)
- Sidebar navigation
- Modal dialogs with focus trap
- Toast notifications
- Loading spinners & skeleton states
- Password strength indicator
- Drag-and-drop (boards, lists, cards)
- Search/filter (boards, members, templates)
- Empty states with CTAs
- Avatar fallback (broken image → initials)

### Settings

- Account settings (profile)
- Workspace settings (name, description)
- Permissions (role management)
- Billing (subscription, usage, upgrade)
- API keys (create, revoke)
- Webhooks (create, delete)
- Integrations (create, delete)

---

## Development

### Commands

```bash
# Backend
cd backend
npm run dev          # Start dev server with hot reload (tsx watch)
npm run build        # Compile TypeScript
npm start            # Run compiled JS
npm run db:push      # Push schema changes to DB
npm run db:studio    # Open Drizzle Studio (DB GUI)

# Frontend
cd frontend
npm run dev          # Start Vite dev server
npm run build        # Build for production
npm run preview      # Preview production build
```

### Database Migrations

Schema changes are managed via Drizzle ORM:

```bash
cd backend

# Push schema directly (dev)
npm run db:push

# Generate migration files
npm run db:generate

# Run migrations
npm run db:migrate
```

For manual SQL migrations, run `.sql` files directly:

```bash
psql -U postgres -d db_kanban -h localhost -f migration.sql
```

### Adding New Features

1. **Schema**: Add/update Drizzle schema in `backend/src/db/schema/`
2. **Service**: Create service in `backend/src/services/`
3. **Route**: Create route in `backend/src/routes/`, mount in `routes/index.ts`
4. **Frontend Service**: Create Axios service in `frontend/src/services/`
5. **View**: Create Vue component in `frontend/src/views/`
6. **Route**: Add Vue Router entry in `frontend/src/router/index.ts`

---

## Deployment

### Frontend (Vercel)

1. Push to GitHub
2. Import in Vercel
3. Root directory: `frontend`
4. Build: `npm run build`
5. Output: `dist`
6. Env: `VITE_API_URL`

### Backend (Railway / Render / Fly.io)

1. Deploy backend separately
2. Set all backend environment variables
3. Update `FRONTEND_URL` to match deployed frontend
4. Update `MIDTRANS_IS_PRODUCTION=TRUE` for live payments

### Database

- Use Supabase, Neon, or any PostgreSQL provider
- Update `DATABASE_URL` in backend env

---

## License

MIT
