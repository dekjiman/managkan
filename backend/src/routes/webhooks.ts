import { Router } from 'express'
import { z } from 'zod'
import { requireAuth, AuthRequest } from '../middleware/auth'
import { workspaceService } from '../services/workspace'
import { webhookService } from '../services/webhooks'
import { wrap } from '../utils/wrap'

const router = Router()

async function resolveWorkspace(slugOrId: string, userId: string) {
  const ws = await workspaceService.getById(slugOrId, userId)
  if (!ws) throw Object.assign(new Error('Workspace not found'), { status: 404 })
  return ws
}

const createSchema = z.object({
  name: z.string().min(1).max(255),
  url: z.string().url(),
  events: z.array(z.string()).min(1),
})

router.get('/', requireAuth, wrap(async (req: AuthRequest, res) => {
  const workspace = await resolveWorkspace(req.query.workspaceSlug as string, req.user!.id)
  const hooks = await webhookService.getByWorkspace(workspace.id)
  res.json({ data: hooks })
}))

router.post('/', requireAuth, wrap(async (req: AuthRequest, res) => {
  const workspace = await resolveWorkspace(req.query.workspaceSlug as string, req.user!.id)
  const data = createSchema.parse(req.body)
  const created = await webhookService.create({
    ...data,
    workspaceId: workspace.id,
    userId: req.user!.id,
  })
  res.status(201).json({ data: created })
}))

router.delete('/:publicId', requireAuth, wrap(async (req: AuthRequest, res) => {
  const workspace = await resolveWorkspace(req.query.workspaceSlug as string, req.user!.id)
  await webhookService.delete(req.params.publicId, workspace.id)
  res.json({ message: 'Webhook deleted' })
}))

router.post('/:publicId/test', requireAuth, wrap(async (req: AuthRequest, res) => {
  const result = await webhookService.test(req.params.publicId)
  res.json({ data: result })
}))

export default router
