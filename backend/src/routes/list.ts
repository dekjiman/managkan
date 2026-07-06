import { Router } from 'express'
import { z } from 'zod'
import { requireAuth, AuthRequest } from '../middleware/auth'
import { listService } from '../services/list'
import { wrap } from '../utils/wrap'

const router = Router()

const createSchema = z.object({
  boardPublicId: z.string().min(12),
  name: z.string().min(1).max(255)
})

const updateSchema = z.object({
  name: z.string().min(1).max(255).optional()
})

const reorderSchema = z.object({
  boardPublicId: z.string().min(12),
  listIds: z.array(z.string())
})

router.get('/', requireAuth, wrap(async (req: AuthRequest, res) => {
  const { boardPublicId } = req.query
  if (!boardPublicId || typeof boardPublicId !== 'string') {
    return res.status(400).json({ message: 'boardPublicId is required' })
  }
  const lists = await listService.getByBoard(boardPublicId)
  res.json({ data: lists })
}))

router.post('/', requireAuth, wrap(async (req: AuthRequest, res) => {
  const data = createSchema.parse(req.body)
  const list = await listService.create(data, req.user!.id)
  res.status(201).json({ data: list, message: 'List created' })
}))

router.put('/reorder', requireAuth, wrap(async (req: AuthRequest, res) => {
  const data = reorderSchema.parse(req.body)
  await listService.reorder(data.boardPublicId, data.listIds)
  res.json({ message: 'Lists reordered' })
}))

router.patch('/:publicId', requireAuth, wrap(async (req: AuthRequest, res) => {
  const data = updateSchema.parse(req.body)
  const list = await listService.update(req.params.publicId, data)
  res.json({ data: list, message: 'List updated' })
}))

router.delete('/:publicId', requireAuth, wrap(async (req: AuthRequest, res) => {
  await listService.delete(req.params.publicId)
  res.json({ message: 'List deleted' })
}))

export default router
