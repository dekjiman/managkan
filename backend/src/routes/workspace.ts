import { Router } from 'express'
import { z } from 'zod'
import { requireAuth, AuthRequest } from '../middleware/auth'
import { workspaceService } from '../services/workspace'
import { wrap } from '../utils/wrap'

const router = Router()

const createSchema = z.object({
  name: z.string().min(1).max(64),
  description: z.string().max(280).optional()
})

const updateSchema = z.object({
  name: z.string().min(1).max(64).optional(),
  description: z.string().max(280).optional()
})

router.get('/', requireAuth, wrap(async (req: AuthRequest, res) => {
  const workspaces = await workspaceService.getByUser(req.user!.id)
  res.json({ data: workspaces })
}))

router.get('/check-slug-availability', requireAuth, wrap(async (req: AuthRequest, res) => {
  const { workspaceSlug } = req.query
  if (!workspaceSlug || typeof workspaceSlug !== 'string') {
    return res.status(400).json({ message: 'workspaceSlug is required' })
  }
  const result = await workspaceService.checkSlugAvailability(workspaceSlug)
  res.json({ data: result })
}))

router.post('/', requireAuth, wrap(async (req: AuthRequest, res) => {
  const data = createSchema.parse(req.body)
  const workspace = await workspaceService.create(data, req.user!.id)
  res.status(201).json({ data: workspace, message: 'Workspace created' })
}))

router.get('/:publicId', requireAuth, wrap(async (req: AuthRequest, res) => {
  const workspace = await workspaceService.getById(req.params.publicId, req.user!.id)
  res.json({ data: workspace })
}))

router.patch('/:publicId', requireAuth, wrap(async (req: AuthRequest, res) => {
  const data = updateSchema.parse(req.body)
  const workspace = await workspaceService.update(req.params.publicId, data, req.user!.id)
  res.json({ data: workspace, message: 'Workspace updated' })
}))

router.delete('/:publicId', requireAuth, wrap(async (req: AuthRequest, res) => {
  await workspaceService.delete(req.params.publicId, req.user!.id)
  res.json({ message: 'Workspace deleted' })
}))

export default router
