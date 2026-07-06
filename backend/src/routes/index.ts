import { Router, Response, NextFunction } from 'express'
import { z } from 'zod'
import workspaceRoutes from './workspace'
import boardRoutes from './board'
import listRoutes from './list'
import cardRoutes from './card'
import labelRoutes from './label'
import checklistRoutes from './checklist'
import commentRoutes from './comment'
import activityRoutes from './activity'
import memberRoutes from './member'
import userRoutes from './user'
import attachmentRoutes from './attachment'
import apiKeyRoutes from './apikeys'
import webhookRoutes from './webhooks'
import integrationRoutes from './integrations'
import billingRoutes from './billing'
import planRoutes from './plans'
import notificationRoutes from './notifications'
import dashboardRoutes from './dashboard'
import { requireAuth, AuthRequest } from '../middleware/auth'
import { requireBoardAccess } from '../middleware/workspaceAccess'
import { boardService } from '../services/board'
import { wrap } from '../utils/wrap'

const router = Router()

router.use('/workspaces/:workspacePublicId/boards', boardRoutes)
router.use('/workspaces', workspaceRoutes)
router.use('/lists', listRoutes)
router.use('/cards', cardRoutes)
router.use('/labels', labelRoutes)
router.use('/checklists', checklistRoutes)
router.use('/comments', commentRoutes)
router.use('/activities', activityRoutes)
router.use('/members', memberRoutes)
router.use('/users', userRoutes)
router.use('/attachments', attachmentRoutes)
router.use('/api-keys', apiKeyRoutes)
router.use('/webhooks', webhookRoutes)
router.use('/integrations', integrationRoutes)
router.use('/billing', billingRoutes)
router.use('/plans', planRoutes)
router.use('/notifications', notificationRoutes)
router.use('/dashboard', dashboardRoutes)

router.get('/boards/:publicIdOrSlug', requireAuth, (req: AuthRequest, res: Response, next: NextFunction) => {
  req.params.boardPublicId = req.params.publicIdOrSlug
  next()
}, requireBoardAccess, wrap(async (req: AuthRequest, res) => {
  const board = await boardService.getById(req.params.publicIdOrSlug)
  res.json({ data: board })
}))

router.patch('/boards/:publicId', requireAuth, (req: AuthRequest, res: Response, next: NextFunction) => {
  req.params.boardPublicId = req.params.publicId
  next()
}, requireBoardAccess, wrap(async (req: AuthRequest, res) => {
  const schema = z.object({
    name: z.string().min(1).max(100).optional(),
    description: z.string().optional(),
    visibility: z.enum(['private', 'public']).optional()
  })
  const data = schema.parse(req.body)
  const board = await boardService.update(req.params.publicId, data)
  res.json({ data: board, message: 'Board updated' })
}))

router.delete('/boards/:publicId', requireAuth, (req: AuthRequest, res: Response, next: NextFunction) => {
  req.params.boardPublicId = req.params.publicId
  next()
}, requireBoardAccess, wrap(async (req: AuthRequest, res) => {
  await boardService.delete(req.params.publicId)
  res.json({ message: 'Board deleted' })
}))

router.get('/health', (req, res) => {
  res.json({ status: 'ok', timestamp: new Date().toISOString() })
})

export default router
