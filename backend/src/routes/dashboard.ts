import { Router } from 'express'
import { requireAuth, AuthRequest } from '../middleware/auth'
import { dashboardService } from '../services/dashboard'
import { wrap } from '../utils/wrap'

const router = Router()

router.get('/', requireAuth, wrap(async (req: AuthRequest, res) => {
  const workspacePublicId = req.query.workspacePublicId as string | undefined
  const data = await dashboardService.getData(req.user!.id, workspacePublicId)
  res.json({ data })
}))

export default router
