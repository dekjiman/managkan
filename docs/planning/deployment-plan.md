# Deployment Plan — ManagPro v2

> **Status:** PLANNING — Dibuat sebelum development.
> **Purpose:** Rencana deployment ke Vercel dengan Supabase PostgreSQL.

---

## 1. Deployment Architecture

```
┌─────────────────────────────────────────────────────┐
│                     VERCEL                          │
│                                                     │
│  ┌─────────────────┐   ┌─────────────────────────┐  │
│  │  Frontend (SPA) │   │  Backend (Serverless)    │  │
│  │  Vue.js + Vite  │   │  Express.js Functions    │  │
│  │                 │   │                          │  │
│  │  /index.html    │   │  /api/*                  │  │
│  │  /assets/*      │   │                          │  │
│  │                 │   │  Better Auth             │  │
│  │  Static CDN     │   │  Drizzle ORM            │  │
│  └─────────────────┘   └──────────┬──────────────┘  │
│                                    │                 │
└────────────────────────────────────┼─────────────────┘
                                     │
                                     ▼
                    ┌──────────────────────────┐
                    │   Supabase PostgreSQL     │
                    │   (Managed Database)      │
                    │                          │
                    │   - Connection pooling    │
                    │   - Auto backups          │
                    │   - Dashboard             │
                    └──────────────────────────┘
```

---

## 2. Vercel Configuration

### Frontend (Vue.js SPA)

**Framework Preset:** Vue.js (Vite)

```json
// vercel.json (root)
{
  "buildCommand": "cd frontend && npm run build",
  "outputDirectory": "frontend/dist",
  "installCommand": "cd frontend && npm install"
}
```

### Backend (Express.js Serverless)

**Option A: Vercel Serverless Functions**

```json
// backend/vercel.json
{
  "version": 2,
  "builds": [
    {
      "src": "src/server.ts",
      "use": "@vercel/node"
    }
  ],
  "routes": [
    {
      "src": "/api/(.*)",
      "dest": "src/server.ts"
    }
  ]
}
```

**Option B: Separate Deployment (Recommended)**

Deploy backend separately if Express functions are too large for Vercel serverless. Consider:
- Railway
- Render
- Fly.io

For MVP, we'll use Vercel serverless functions.

---

## 3. Environment Variables

### Frontend (Vercel)

| Variable | Value | Notes |
|----------|-------|-------|
| `VITE_API_URL` | `https://your-app.vercel.app/api` | Backend API URL |

### Backend (Vercel)

| Variable | Value | Notes |
|----------|-------|-------|
| `DATABASE_URL` | `postgresql://...@aws-0-... supabase.co:5432/postgres` | Supabase connection |
| `BETTER_AUTH_SECRET` | `your-32-char-secret` | Auth secret |
| `BETTER_AUTH_URL` | `https://your-app.vercel.app` | Backend base URL |
| `FRONTEND_URL` | `https://your-app.vercel.app` | CORS origin |
| `GOOGLE_CLIENT_ID` | OAuth client ID | Google OAuth |
| `GOOGLE_CLIENT_SECRET` | OAuth client secret | Google OAuth |
| `GITHUB_CLIENT_ID` | OAuth client ID | GitHub OAuth |
| `GITHUB_CLIENT_SECRET` | OAuth client secret | GitHub OAuth |

### Supabase Dashboard

| Variable | Location | Notes |
|----------|----------|-------|
| `DATABASE_URL` | Settings → Database → Connection string | Use connection pooling (port 6543) |

---

## 4. Deployment Steps

### Initial Setup

```bash
# 1. Create Vercel project
vercel init

# 2. Link to GitHub repo
vercel git connect

# 3. Set environment variables
vercel env add VITE_API_URL
vercel env add DATABASE_URL
vercel env add BETTER_AUTH_SECRET
# ... etc

# 4. Deploy
vercel deploy
```

### Database Setup (Supabase)

```bash
# 1. Create Supabase project at supabase.com
# 2. Get connection string from Settings → Database
# 3. Use connection pooling URL (port 6543)
# 4. Run migrations
cd backend
npx drizzle-kit push
```

### GitHub Actions (CI/CD)

```yaml
# .github/workflows/deploy.yml
name: Deploy

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: amondnet/vercel-action@v25
        with:
          vercel-token: ${{ secrets.VERCEL_TOKEN }}
          vercel-org-id: ${{ secrets.VERCEL_ORG_ID }}
          vercel-project-id: ${{ secrets.VERCEL_PROJECT_ID }}
          vercel-args: '--prod'
```

---

## 5. Domain Configuration

### Custom Domain

1. Go to Vercel Dashboard → Project → Settings → Domains
2. Add custom domain (e.g., `managpro.example.com`)
3. Configure DNS:
   - Type: CNAME
   - Name: `@` or `managpro`
   - Value: `cname.vercel-dns.com`
4. SSL auto-configured by Vercel

### Supabase URL (if needed)

- Dashboard URL: `https://your-project.supabase.co`
- API URL: `https://your-project.supabase.co`

---

## 6. Database Migrations on Deploy

### Option A: Manual (MVP)

```bash
# Run locally before deploy
cd backend
npx drizzle-kit push
```

### Option B: Automated (Post-MVP)

Add to Vercel build command:

```json
{
  "buildCommand": "cd backend && npx drizzle-kit push && cd ../frontend && npm run build"
}
```

⚠️ **Warning:** Running migrations on every build can be dangerous in production. Use with caution.

### Option C: GitHub Actions (Recommended)

```yaml
- name: Run migrations
  run: cd backend && npx drizzle-kit push
  env:
    DATABASE_URL: ${{ secrets.DATABASE_URL }}
```

---

## 7. Monitoring & Logs

### Vercel

- **Function Logs:** Vercel Dashboard → Project → Logs
- **Analytics:** Vercel Analytics (enable in settings)
- **Speed Insights:** Vercel Speed Insights

### Supabase

- **Database Metrics:** Supabase Dashboard → Database → Metrics
- **Logs:** Supabase Dashboard → Logs

### Error Tracking (Post-MVP)

- Sentry for error tracking
- Set `SENTRY_DSN` environment variable

---

## 8. Backup Strategy

### Supabase Auto-Backups

- Daily backups (7 days retention on free tier)
- Point-in-time recovery (Pro plan)

### Manual Backup

```bash
pg_dump $DATABASE_URL > backup_$(date +%Y%m%d).sql
```

---

## 9. Cost Estimation

### Vercel

| Plan | Price | Features |
|------|-------|----------|
| Hobby | Free | 100GB bandwidth, 1000 build minutes |
| Pro | $20/month | 1TB bandwidth, unlimited builds |

### Supabase

| Plan | Price | Features |
|------|-------|----------|
| Free | $0 | 500MB database, 1GB file storage |
| Pro | $25/month | 8GB database, 100GB storage |

### Total (MVP)

**~$0-25/month** depending on usage.

---

## 10. Rollback Strategy

### Vercel Rollback

```bash
# List deployments
vercel ls

# Rollback to previous deployment
vercel rollback [deployment-url]
```

### Database Rollback

- Use Supabase point-in-time recovery (Pro plan)
- Or restore from backup

---

## 11. Post-MVP Enhancements

| Enhancement | Priority | Notes |
|------------|----------|-------|
| Custom domain | High | Setup after initial deploy |
| Sentry error tracking | High | Add `@sentry/vue` and `@sentry/node` |
| Vercel Analytics | Medium | Enable in dashboard |
| Redis caching | Medium | Upstash Redis on Vercel |
| CDN for assets | Low | Vercel handles this automatically |
