import { Router } from 'express'
import { requireAuth, AuthRequest } from '../middleware/auth'
import { activityService } from '../services/activity'
import { wrap } from '../utils/wrap'

const router = Router()

router.get('/', requireAuth, wrap(async (req: AuthRequest, res) => {
  const { cardId } = req.query
  if (!cardId) {
    return res.status(400).json({ message: 'cardId is required' })
  }
  const activities = await activityService.getByCard(Number(cardId))
  res.json({ data: activities })
}))

export default router
