import { Router } from 'express'
import { z } from 'zod'
import { requireAuth, AuthRequest } from '../middleware/auth'
import { workspaceService } from '../services/workspace'
import { billingService } from '../services/billing'
import { wrap } from '../utils/wrap'

const router = Router()

async function resolveWorkspace(slugOrId: string, userId: string) {
  const ws = await workspaceService.getById(slugOrId, userId)
  if (!ws) throw Object.assign(new Error('Workspace not found'), { status: 404 })
  return ws
}

router.get('/', requireAuth, wrap(async (req: AuthRequest, res) => {
  const ws = await resolveWorkspace(req.query.workspaceSlug as string, req.user!.id)
  const [subscriptionData, usage] = await Promise.all([
    billingService.getByWorkspace(ws.id, req.user!.id),
    billingService.getUsage(ws.id),
  ])
  res.json({ data: { ...subscriptionData, usage } })
}))

const checkoutSchema = z.object({
  planName: z.enum(['team', 'pro']),
})

router.post('/checkout', requireAuth, wrap(async (req: AuthRequest, res) => {
  const ws = await resolveWorkspace(req.query.workspaceSlug as string, req.user!.id)
  if (ws.role !== 'admin') {
    return res.status(403).json({ message: 'Only admins can upgrade plan' })
  }
  const { planName } = checkoutSchema.parse(req.body)
  const result = await billingService.createCheckout(ws.id, req.user!.id, planName)
  res.json({ data: result })
}))

router.post('/notification', wrap(async (req, res) => {
  const result = await billingService.handleNotification(req.body)
  res.json({ data: result })
}))

export default router
