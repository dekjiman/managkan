import { Router } from 'express'
import { z } from 'zod'
import { requireAuth, AuthRequest } from '../middleware/auth'
import { workspaceService } from '../services/workspace'
import { apiKeyService } from '../services/apikeys'
import { wrap } from '../utils/wrap'

const router = Router()

async function resolveWorkspace(slugOrId: string, userId: string) {
  const ws = await workspaceService.getById(slugOrId, userId)
  if (!ws) throw Object.assign(new Error('Workspace not found'), { status: 404 })
  return ws
}

const createSchema = z.object({
  name: z.string().min(1).max(255),
  permissions: z.array(z.string()).min(1),
})

router.get('/', requireAuth, wrap(async (req: AuthRequest, res) => {
  const workspace = await resolveWorkspace(req.query.workspaceSlug as string, req.user!.id)
  const keys = await apiKeyService.getByWorkspace(workspace.id)
  res.json({ data: keys })
}))

router.post('/', requireAuth, wrap(async (req: AuthRequest, res) => {
  const workspace = await resolveWorkspace(req.query.workspaceSlug as string, req.user!.id)
  const data = createSchema.parse(req.body)
  const result = await apiKeyService.create({
    ...data,
    workspaceId: workspace.id,
    userId: req.user!.id,
  })
  res.status(201).json({ data: result })
}))

router.delete('/:publicId', requireAuth, wrap(async (req: AuthRequest, res) => {
  const workspace = await resolveWorkspace(req.query.workspaceSlug as string, req.user!.id)
  await apiKeyService.revoke(req.params.publicId, workspace.id)
  res.json({ message: 'API key revoked' })
}))

export default router
