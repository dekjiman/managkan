import { Router } from 'express'
import { z } from 'zod'
import { requireAuth, AuthRequest } from '../middleware/auth'
import { workspaceService } from '../services/workspace'
import { integrationService } from '../services/integrations'
import { wrap } from '../utils/wrap'

const router = Router()

async function resolveWorkspace(slugOrId: string, userId: string) {
  const ws = await workspaceService.getById(slugOrId, userId)
  if (!ws) throw Object.assign(new Error('Workspace not found'), { status: 404 })
  return ws
}

const toggleSchema = z.object({
  integrationId: z.string(),
  connected: z.boolean(),
})

router.get('/', requireAuth, wrap(async (req: AuthRequest, res) => {
  const workspace = await resolveWorkspace(req.query.workspaceSlug as string, req.user!.id)
  const integrations = await integrationService.getByWorkspace(workspace.id)
  res.json({ data: integrations })
}))

router.put('/', requireAuth, wrap(async (req: AuthRequest, res) => {
  const workspace = await resolveWorkspace(req.query.workspaceSlug as string, req.user!.id)
  const data = toggleSchema.parse(req.body)
  await integrationService.setConnected({
    ...data,
    workspaceId: workspace.id,
    userId: req.user!.id,
  })
  res.json({ message: 'Integration updated' })
}))

export default router
