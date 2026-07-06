import { Router } from 'express'
import { auth } from '../auth'
import { env } from '../config/env'

const router = Router()

router.get('/google', async (req, res) => {
  try {
    const callbackURL = new URL(env.FRONTEND_URL)
    const redirect = req.query.redirect as string
    if (redirect) {
      callbackURL.searchParams.set('redirect', redirect)
    }
    const result = await auth.api.signInSocial({
      body: { provider: 'google', callbackURL: callbackURL.toString() }
    })
    if (result?.url) {
      res.redirect(result.url)
    } else {
      res.status(400).json({ message: 'OAuth configuration error' })
    }
  } catch {
    res.status(500).json({ message: 'Authentication failed' })
  }
})

router.get('/github', async (req, res) => {
  try {
    const callbackURL = new URL(env.FRONTEND_URL)
    const redirect = req.query.redirect as string
    if (redirect) {
      callbackURL.searchParams.set('redirect', redirect)
    }
    const result = await auth.api.signInSocial({
      body: { provider: 'github', callbackURL: callbackURL.toString() }
    })
    if (result?.url) {
      res.redirect(result.url)
    } else {
      res.status(400).json({ message: 'OAuth configuration error' })
    }
  } catch {
    res.status(500).json({ message: 'Authentication failed' })
  }
})

export default router
