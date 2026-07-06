import { Router } from 'express'
import { z } from 'zod'
import { requireAuth, AuthRequest } from '../middleware/auth'
import { requireCardAccess } from '../middleware/workspaceAccess'
import { cardService } from '../services/card'
import { wrap } from '../utils/wrap'

const router = Router()

const createSchema = z.object({
  title: z.string().min(1).max(2000),
  description: z.string().max(10000).default(''),
  listPublicId: z.string().min(12),
  labelPublicIds: z.array(z.string().min(12)).default([]),
  memberPublicIds: z.array(z.string().min(12)).default([]),
  position: z.enum(['start', 'end']).default('end'),
  dueDate: z.string().nullable().optional()
})

const updateSchema = z.object({
  title: z.string().min(1).max(2000).optional(),
  description: z.string().max(10000).optional(),
  dueDate: z.string().nullable().optional()
})

const moveSchema = z.object({
  listPublicId: z.string().min(12),
  index: z.number().min(0)
})

router.get('/', requireAuth, wrap(async (req: AuthRequest, res) => {
  const { listPublicId } = req.query
  if (!listPublicId || typeof listPublicId !== 'string') {
    return res.status(400).json({ message: 'listPublicId is required' })
  }
  const cards = await cardService.getByList(listPublicId)
  res.json({ data: cards })
}))

router.post('/', requireAuth, wrap(async (req: AuthRequest, res) => {
  const data = createSchema.parse(req.body)
  const card = await cardService.create(data, req.user!.id)
  res.status(201).json({ data: card, message: 'Card created' })
}))

router.get('/:publicId', requireAuth, requireCardAccess, wrap(async (req: AuthRequest, res) => {
  const card = await cardService.getById(req.params.publicId)
  res.json({ data: card })
}))

router.patch('/:publicId', requireAuth, requireCardAccess, wrap(async (req: AuthRequest, res) => {
  const data = updateSchema.parse(req.body)
  const updateData: { title?: string; description?: string; dueDate?: Date | null } = {
    title: data.title,
    description: data.description,
    dueDate: data.dueDate !== undefined ? (data.dueDate ? new Date(data.dueDate) : null) : undefined
  }
  const card = await cardService.update(req.params.publicId, updateData)
  res.json({ data: card, message: 'Card updated' })
}))

router.put('/:publicId/move', requireAuth, requireCardAccess, wrap(async (req: AuthRequest, res) => {
  const data = moveSchema.parse(req.body)
  const card = await cardService.move(req.params.publicId, data)
  res.json({ data: card, message: 'Card moved' })
}))

router.post('/:publicId/duplicate', requireAuth, requireCardAccess, wrap(async (req: AuthRequest, res) => {
  const card = await cardService.duplicate(req.params.publicId, req.user!.id)
  res.status(201).json({ data: card, message: 'Card duplicated' })
}))

router.delete('/:publicId', requireAuth, requireCardAccess, wrap(async (req: AuthRequest, res) => {
  await cardService.delete(req.params.publicId)
  res.json({ message: 'Card deleted' })
}))

router.put('/:publicId/labels/:labelPublicId', requireAuth, requireCardAccess, wrap(async (req: AuthRequest, res) => {
  const result = await cardService.toggleLabel(req.params.publicId, req.params.labelPublicId)
  res.json({ data: result })
}))

router.put('/:publicId/members/:workspaceMemberPublicId', requireAuth, requireCardAccess, wrap(async (req: AuthRequest, res) => {
  const result = await cardService.toggleMember(req.params.publicId, req.params.workspaceMemberPublicId)
  res.json({ data: result })
}))

export default router
