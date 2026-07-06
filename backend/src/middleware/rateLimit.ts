import rateLimit from 'express-rate-limit'

const hasUpstash = !!process.env.UPSTASH_REDIS_REST_URL && !!process.env.UPSTASH_REDIS_REST_TOKEN

let upstashLimiter: ((keyPrefix: string, max: number, windowSec: number) => any) | null = null

if (hasUpstash) {
  const { Ratelimit } = require('@upstash/ratelimit')
  const { Redis } = require('@upstash/redis')

  const redis = new Redis({
    url: process.env.UPSTASH_REDIS_REST_URL!,
    token: process.env.UPSTASH_REDIS_REST_TOKEN!,
  })

  const authRatelimit = new Ratelimit({
    redis,
    limiter: Ratelimit.slidingWindow(5, '60 s'),
    analytics: true,
  })

  const apiRatelimit = new Ratelimit({
    redis,
    limiter: Ratelimit.slidingWindow(60, '60 s'),
    analytics: true,
  })

  upstashLimiter = (keyPrefix: string, max: number, windowSec: number) => {
    const rl = keyPrefix === 'auth' ? authRatelimit : apiRatelimit
    return async (req: any, res: any, next: any) => {
      const ip = req.ip || req.headers['x-forwarded-for'] || 'unknown'
      const key = `${keyPrefix}:${ip}`
      const { success, limit, remaining, reset } = await rl.limit(key)

      res.setHeader('RateLimit-Limit', limit)
      res.setHeader('RateLimit-Remaining', remaining)
      res.setHeader('RateLimit-Reset', reset)

      if (!success) {
        return res.status(429).json({ message: 'Too many requests, please try again later' })
      }
      next()
    }
  }
}

// In-memory fallback (for local development only — not suitable for serverless)
const memoryAuthLimiter = rateLimit({
  windowMs: 60 * 1000,
  max: 5,
  message: { message: 'Too many attempts, please try again later' },
  standardHeaders: true,
  legacyHeaders: false,
})

const memoryApiLimiter = rateLimit({
  windowMs: 60 * 1000,
  max: 60,
  message: { message: 'Too many requests, please try again later' },
  standardHeaders: true,
  legacyHeaders: false,
})

export const authLimiter = upstashLimiter
  ? upstashLimiter('auth', 5, 60)
  : memoryAuthLimiter

export const apiLimiter = upstashLimiter
  ? upstashLimiter('api', 60, 60)
  : memoryApiLimiter
