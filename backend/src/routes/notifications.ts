import { Router } from 'express'
import { requireAuth, AuthRequest } from '../middleware/auth'
import { notificationService } from '../services/notifications'
import { wrap } from '../utils/wrap'

const router = Router()

router.get('/', requireAuth, wrap(async (req: AuthRequest, res) => {
  const notifications = await notificationService.getByUser(req.user!.id)
  res.json({ data: notifications })
}))

router.get('/unread-count', requireAuth, wrap(async (req: AuthRequest, res) => {
  const count = await notificationService.getUnreadCount(req.user!.id)
  res.json({ data: { count } })
}))

router.patch('/read-all', requireAuth, wrap(async (req: AuthRequest, res) => {
  await notificationService.markAllRead(req.user!.id)
  res.json({ message: 'All notifications marked as read' })
}))

router.patch('/:publicId/read', requireAuth, wrap(async (req: AuthRequest, res) => {
  await notificationService.markRead(req.params.publicId, req.user!.id)
  res.json({ message: 'Notification marked as read' })
}))

export default router
