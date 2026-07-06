# Auth Plan — ManagPro v2

> **Status:** PLANNING — Dibuat sebelum development.
> **Purpose:** Rencana implementasi autentikasi dengan Better Auth.

---

## 1. Authentication Architecture

### Better Auth Integration

Better Auth handles:
- User registration (email + password)
- User login (email + password)
- OAuth (Google, GitHub)
- Session management (cookies)
- CSRF protection
- Email verification (optional in MVP)

### Integration Pattern

```
Frontend (Vue.js)  →  Express.js Backend  →  Better Auth  →  PostgreSQL
     │                      │                    │                │
     │  POST /api/auth/*    │  Auth middleware    │  Session       │
     ├──────────────────────►├────────────────────►├───────────────►
     │                      │                    │                │
     │  Cookie: session=xxx │  Verify session    │  User data     │
     ├──────────────────────►├────────────────────►├───────────────►
```

---

## 2. Auth Flow

### Registration Flow

```
1. User fills form (name, email, password)
2. POST /api/auth/sign-up → Better Auth
3. Better Auth hashes password, creates user + session
4. Set session cookie
5. Return user + session data
6. Frontend stores user in Pinia, redirects to dashboard
```

### Login Flow

```
1. User fills form (email, password)
2. POST /api/auth/sign-in → Better Auth
3. Better Auth validates credentials
4. Set session cookie
5. Return user + session data
6. Frontend stores user in Pinia, redirects to dashboard
```

### OAuth Flow (Google/GitHub)

```
1. User clicks "Login with Google"
2. Redirect to /api/auth/google → Better Auth → Google OAuth
3. Google callback → /api/auth/google/callback
4. Better Auth creates/finds user, sets session
5. Redirect to frontend with session cookie
6. Frontend fetches session, stores user
```

### Logout Flow

```
1. User clicks logout
2. POST /api/auth/sign-out → Better Auth
3. Better Auth destroys session
4. Clear session cookie
5. Frontend clears Pinia store, redirects to login
```

---

## 3. Session Management

### Session Storage
- Database-backed sessions (PostgreSQL)
- Session token stored in HTTP-only cookie
- Cookie settings:
  - `httpOnly: true`
  - `secure: true` (production)
  - `sameSite: 'lax'`
  - `path: '/'`

### Session Verification Middleware

```typescript
// Express middleware
const authMiddleware = async (req, res, next) => {
  const session = await auth.api.getSession({
    headers: req.headers
  })
  
  if (!session) {
    return res.status(401).json({ message: 'Unauthorized' })
  }
  
  req.user = session.user
  req.session = session.session
  next()
}
```

---

## 4. Authorization (Workspace Access)

### Middleware Pattern

```typescript
const workspaceAccess = async (req, res, next) => {
  const { publicId } = req.params
  const userId = req.user.id
  
  const membership = await db.query.workspaceMember.findFirst({
    where: and(
      eq(workspaceMember.userId, userId),
      eq(workspaceMember.workspaceId, publicId)
    )
  })
  
  if (!membership) {
    return res.status(403).json({ message: 'No access to workspace' })
  }
  
  req.workspaceRole = membership.role
  next()
}
```

### Role Hierarchy

| Role | Permissions |
|------|------------|
| admin | Full workspace management, member management, settings |
| member | Create/edit boards, cards, lists; view members |
| guest | View-only access (future) |

---

## 5. Better Auth Configuration

```typescript
// backend/src/auth.ts
import { betterAuth } from 'better-auth'
import { drizzleAdapter } from 'better-auth/adapters/drizzle'
import { db } from './db'

export const auth = betterAuth({
  database: drizzleAdapter(db, {
    provider: 'postgresql'
  }),
  emailAndPassword: {
    enabled: true,
    requireEmailVerification: false // MVP: no email verification
  },
  socialProviders: {
    google: {
      clientId: process.env.GOOGLE_CLIENT_ID!,
      clientSecret: process.env.GOOGLE_CLIENT_SECRET!
    },
    github: {
      clientId: process.env.GITHUB_CLIENT_ID!,
      clientSecret: process.env.GITHUB_CLIENT_SECRET!
    }
  },
  session: {
    expiresIn: 60 * 60 * 24 * 7, // 7 days
    updateAge: 60 * 60 * 24 // 1 day
  },
  trustedOrigins: [
    process.env.FRONTEND_URL || 'http://localhost:5173'
  ]
})
```

---

## 6. Frontend Auth Integration

### useAuth Composable

```typescript
// composables/useAuth.ts
export function useAuth() {
  const authStore = useAuthStore()
  
  const login = async (email: string, password: string) => {
    const { data } = await authService.login({ email, password })
    authStore.setUser(data.user)
    router.push('/dashboard')
  }
  
  const register = async (name: string, email: string, password: string) => {
    const { data } = await authService.register({ name, email, password })
    authStore.setUser(data.user)
    router.push('/dashboard')
  }
  
  const logout = async () => {
    await authService.logout()
    authStore.clearUser()
    router.push('/login')
  }
  
  const fetchSession = async () => {
    try {
      const { data } = await authService.getSession()
      authStore.setUser(data.user)
    } catch {
      authStore.clearUser()
    }
  }
  
  return { login, register, logout, fetchSession }
}
```

### Route Guards

```typescript
// router/index.ts
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  if (to.meta.auth && !authStore.isAuthenticated) {
    await authStore.fetchSession()
    if (!authStore.isAuthenticated) {
      return next('/login')
    }
  }
  
  if (to.meta.guest && authStore.isAuthenticated) {
    return next('/dashboard')
  }
  
  next()
})
```

---

## 7. Environment Variables

```env
# Backend
BETTER_AUTH_SECRET=your-secret-key-32-chars-min
BETTER_AUTH_URL=http://localhost:3000
FRONTEND_URL=http://localhost:5173

# OAuth
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
```

---

## 8. Security Considerations

| Area | Implementation |
|------|---------------|
| Password Hashing | Better Auth handles (bcrypt) |
| CSRF | Better Auth CSRF tokens |
| Session Theft | HTTP-only cookies, secure flag |
| OAuth State | Better Auth handles state parameter |
| Rate Limiting | In-memory initially, Redis later |
| Input Validation | Zod schemas on all auth inputs |
