import { Router } from 'express'
import { z } from 'zod'
import { requireAuth, AuthRequest } from '../middleware/auth'
import { requireLabelAccess } from '../middleware/workspaceAccess'
import { labelService } from '../services/label'
import { wrap } from '../utils/wrap'

const router = Router()

const createSchema = z.object({
  boardId: z.coerce.number(),
  name: z.string().min(1).max(255),
  colourCode: z.string().regex(/^#[0-9A-Fa-f]{6}$/).optional()
})

const updateSchema = z.object({
  name: z.string().min(1).max(255).optional(),
  colourCode: z.string().regex(/^#[0-9A-Fa-f]{6}$/).optional()
})

router.get('/', requireAuth, wrap(async (req: AuthRequest, res) => {
  const { boardId } = req.query
  if (!boardId) {
    return res.status(400).json({ message: 'boardId is required' })
  }
  const labels = await labelService.getByBoard(Number(boardId))
  res.json({ data: labels })
}))

router.post('/', requireAuth, wrap(async (req: AuthRequest, res) => {
  const data = createSchema.parse(req.body)
  const label = await labelService.create(data)
  res.status(201).json({ data: label, message: 'Label created' })
}))

router.patch('/:publicId', requireAuth, requireLabelAccess, wrap(async (req: AuthRequest, res) => {
  const data = updateSchema.parse(req.body)
  const label = await labelService.update(req.params.publicId, data)
  res.json({ data: label, message: 'Label updated' })
}))

router.delete('/:publicId', requireAuth, requireLabelAccess, wrap(async (req: AuthRequest, res) => {
  await labelService.delete(req.params.publicId)
  res.json({ message: 'Label deleted' })
}))

export default router
