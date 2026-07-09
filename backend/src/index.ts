import express from 'express'
import cors from 'cors'
import helmet from 'helmet'
import path from 'path'
import { toNodeHandler } from 'better-auth/node'
import { auth } from './auth'
import { env } from './config/env'
import routes from './routes'
import oauthRoutes from './routes/oauth'
import { errorHandler } from './middleware/error'
import { requireAuth } from './middleware/auth'
import { authLimiter, apiLimiter } from './middleware/rateLimit'
import { logger } from './config/logger'

const app = express()

// Security
app.use(helmet({
  contentSecurityPolicy: {
    directives: {
      defaultSrc: ["'self'"],
      scriptSrc: ["'self'", "'unsafe-inline'", "https://snap-assets.sandbox.midtrans.com", "https://api.sandbox.midtrans.com", "https://pay.google.com", "https://gwk.gopayapi.com", "https://www.googletagmanager.com"],
      frameSrc: ["'self'", "https://app.sandbox.midtrans.com", "https://accounts.google.com"],
      connectSrc: ["'self'", "https://api.sandbox.midtrans.com"],
    }
  }
}))
app.use(cors({
  origin: env.FRONTEND_URL,
  credentials: true
}))

// Rate limiting
app.use('/api/auth', authLimiter)
app.use('/api', apiLimiter)

// OAuth redirect routes (must be before better-auth handler to avoid path conflicts)
app.use('/api/auth', oauthRoutes)

// Better-auth handler must run before body parsing (it reads the raw request)
app.use('/api/auth', toNodeHandler(auth))

// Body parsing
app.use(express.json({ limit: '10mb' }))
app.use(express.urlencoded({ extended: true }))

// Static files for uploads — behind auth
app.use('/uploads', requireAuth, express.static(path.join(process.cwd(), 'uploads')))

// Routes
app.use('/api', routes)

// Error handling
app.use(errorHandler)

// Start server (skip on Vercel — serverless handles this)
if (process.env.VERCEL !== '1') {
  const PORT = env.PORT
  app.listen(PORT, () => {
    logger.info({ port: PORT, env: env.NODE_ENV }, 'Server started')
  })
}

export default app
