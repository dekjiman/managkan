# Deployment Guide — ManagPro

## Stack

- **Backend:** Node.js (Express) + TypeScript
- **Frontend:** Vue 3 + Vite + TypeScript
- **Database:** PostgreSQL
- **Auth:** better-auth
- **Payment:** Midtrans (Snap.js)
- **Email:** Nodemailer (Gmail SMTP)
- **Hosting:** Vercel (frontend) + Railway/Render/VPS (backend)

## Environment Variables

All env vars are defined in `backend/.env.example`. Required for production:

| Variable | Purpose | Required |
|----------|---------|----------|
| `DATABASE_URL` | PostgreSQL connection string | YES |
| `BETTER_AUTH_SECRET` | Session secret (min 32 chars) | YES |
| `BETTER_AUTH_URL` | Backend public URL (https) | YES |
| `FRONTEND_URL` | Frontend public URL (https) | YES |
| `SMTP_HOST/PORT/USER/PASS` | Email provider | YES |
| `MIDTRANS_SERVER_KEY` | Midtrans payment | YES |
| `MIDTRANS_CLIENT_KEY` | Midtrans payment | YES |
| `MIDTRANS_IS_PRODUCTION` | TRUE for live | YES |
| `UPSTASH_REDIS_REST_URL` | Rate limiting (serverless) | RECOMMENDED |
| `UPSTASH_REDIS_REST_TOKEN` | Rate limiting (serverless) | RECOMMENDED |
| `SENTRY_DSN` | Error tracking | OPTIONAL |
| `LOG_LEVEL` | Logging level (debug/info/warn/error) | OPTIONAL |

## Backend Deployment

### Railway / Render / Fly.io

1. Connect GitHub repo
2. Set root directory to `backend/`
3. Set build command: `npm install && npm run build`
4. Set start command: `npm start`
5. Add all env vars from `.env.example`

### Vercel (Serverless)

Backend cannot run as Vercel serverless function as-is (uses `app.listen()`).
Use Railway, Render, or a VPS for the backend.

## Frontend Deployment (Vercel)

1. Framework: Vite
2. Root directory: `frontend/`
3. Build command: `npm run build`
4. Output directory: `dist`
5. Env var: `VITE_API_URL=https://your-backend-url/api`

## Database Setup

```sql
-- Create database
CREATE DATABASE managpro;

-- Connect and run migrations
cd backend
npm run db:push
```

## Backup

```bash
# Manual backup
./scripts/backup-database.sh

# Automated (add to crontab)
0 3 * * * /path/to/managkan/scripts/backup-database.sh
```

## Monitoring

- **Health check:** `GET /api/health`
- **Sentry:** Set `SENTRY_DSN` env var for error tracking
- **Logs:** JSON structured logs via pino (set `LOG_LEVEL=info` in production)

## Rate Limiting

- Auth routes: 5 requests/minute per IP
- API routes: 60 requests/minute per IP
- Uses Upstash Redis when `UPSTASH_REDIS_REST_URL` is set (required for Vercel)
- Falls back to in-memory for local development
