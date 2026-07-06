# ManagPro v2 — Open-Source Project Management Tool

A Trello alternative rebuilt with Vue.js, Express.js, and Supabase PostgreSQL.

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Frontend | Vue.js 3, Vite, Tailwind CSS |
| Backend | Express.js, Better Auth, Drizzle ORM |
| Database | PostgreSQL (Supabase) |
| Deployment | Vercel |

## Quick Start

### Prerequisites

- Node.js 18+
- PostgreSQL (or Supabase account)

### 1. Clone & Install

```bash
# Install backend dependencies
cd backend
npm install

# Install frontend dependencies
cd ../frontend
npm install
```

### 2. Environment Setup

```bash
# Backend
cd backend
cp .env.example .env
# Edit .env with your database URL and auth secret

# Frontend
cd ../frontend
cp .env.example .env
```

### 3. Database Setup

```bash
cd backend
npm run db:push
```

### 4. Start Development

```bash
# Terminal 1 - Backend
cd backend
npm run dev

# Terminal 2 - Frontend
cd frontend
npm run dev
```

Open [http://localhost:5173](http://localhost:5173)

## Project Structure

```
managkan/
├── frontend/              # Vue.js SPA
│   ├── src/
│   │   ├── components/    # Reusable components
│   │   ├── views/         # Page components
│   │   ├── stores/        # Pinia stores
│   │   ├── services/      # API client
│   │   └── router/        # Vue Router
│   └── ...
├── backend/               # Express.js API
│   ├── src/
│   │   ├── routes/        # API routes
│   │   ├── services/      # Business logic
│   │   ├── db/            # Drizzle ORM schema
│   │   └── middleware/    # Auth, error handling
│   └── ...
├── planning/              # Planning documents
├── revamp/                # Migration audit docs
└── vercel.json            # Vercel config
```

## Environment Variables

### Backend

| Variable | Required | Description |
|----------|----------|-------------|
| `DATABASE_URL` | Yes | PostgreSQL connection URL |
| `BETTER_AUTH_SECRET` | Yes | Auth secret (32+ chars) |
| `BETTER_AUTH_URL` | Yes | Backend URL |
| `FRONTEND_URL` | Yes | Frontend URL for CORS |
| `GOOGLE_CLIENT_ID` | Optional | Google OAuth |
| `GOOGLE_CLIENT_SECRET` | Optional | Google OAuth |
| `GITHUB_CLIENT_ID` | Optional | GitHub OAuth |
| `GITHUB_CLIENT_SECRET` | Optional | GitHub OAuth |

### Frontend

| Variable | Required | Description |
|----------|----------|-------------|
| `VITE_API_URL` | Yes | Backend API URL |

## Features (MVP)

- Email/Password authentication
- Google & GitHub OAuth
- Workspace management
- Board management (Kanban)
- List management with reordering
- Card management with drag & drop
- Labels and member assignment
- Checklists with completion tracking
- Comments
- Activity logging
- Dark mode
- Responsive design

## Deployment to Vercel

### Frontend

1. Push to GitHub
2. Import project in Vercel
3. Set root directory to `frontend`
4. Set build command to `npm run build`
5. Set output directory to `dist`
6. Add environment variable: `VITE_API_URL`

### Backend

1. Deploy backend separately (Railway, Render, or Fly.io)
2. Update `VITE_API_URL` to point to deployed backend

Or use Vercel Serverless Functions (see `vercel.json`).

## Database

Supabase PostgreSQL with Drizzle ORM.

### Migrations

```bash
cd backend
npm run db:push    # Push schema changes
npm run db:studio  # Open Drizzle Studio
```

## License

MIT
# managkan
