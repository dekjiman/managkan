import { Router } from 'express'
import { z } from 'zod'
import { requireAuth, AuthRequest } from '../middleware/auth'
import { userService } from '../services/user'
import { wrap } from '../utils/wrap'

const router = Router()

const updateSchema = z.object({
  name: z.string().min(1).max(255).optional(),
  image: z.string().url().optional()
})

router.get('/me', requireAuth, wrap(async (req: AuthRequest, res) => {
  const userData = await userService.getById(req.user!.id)
  res.json({ data: userData })
}))

router.patch('/me', requireAuth, wrap(async (req: AuthRequest, res) => {
  const data = updateSchema.parse(req.body)
  const userData = await userService.update(req.user!.id, data)
  res.json({ data: userData, message: 'Profile updated' })
}))

export default router
