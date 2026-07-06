import { Router } from 'express'
import { z } from 'zod'
import { requireAuth, AuthRequest } from '../middleware/auth'
import { requireCardAccess } from '../middleware/workspaceAccess'
import { checklistService } from '../services/checklist'
import { wrap } from '../utils/wrap'

const router = Router()

const createChecklistSchema = z.object({
  cardId: z.coerce.number(),
  name: z.string().min(1).max(255)
})

const updateChecklistSchema = z.object({
  name: z.string().min(1).max(255).optional()
})

const createItemSchema = z.object({
  title: z.string().min(1).max(500)
})

const updateItemSchema = z.object({
  title: z.string().min(1).max(500).optional(),
  completed: z.boolean().optional()
})

router.get('/', requireAuth, wrap(async (req: AuthRequest, res) => {
  const { cardId } = req.query
  if (!cardId) {
    return res.status(400).json({ message: 'cardId is required' })
  }
  const checklists = await checklistService.getByCard(Number(cardId))
  res.json({ data: checklists })
}))

router.post('/', requireAuth, wrap(async (req: AuthRequest, res) => {
  const data = createChecklistSchema.parse(req.body)
  const checklist = await checklistService.createChecklist(data)
  res.status(201).json({ data: checklist, message: 'Checklist created' })
}))

router.patch('/:publicId', requireAuth, wrap(async (req: AuthRequest, res) => {
  const data = updateChecklistSchema.parse(req.body)
  const checklist = await checklistService.updateChecklist(req.params.publicId, data)
  res.json({ data: checklist, message: 'Checklist updated' })
}))

router.delete('/:publicId', requireAuth, wrap(async (req: AuthRequest, res) => {
  await checklistService.deleteChecklist(req.params.publicId)
  res.json({ message: 'Checklist deleted' })
}))

router.post('/:checklistId/items', requireAuth, wrap(async (req: AuthRequest, res) => {
  const data = createItemSchema.parse(req.body)
  const item = await checklistService.createItem({
    checklistId: Number(req.params.checklistId),
    title: data.title
  })
  res.status(201).json({ data: item, message: 'Item created' })
}))

router.patch('/items/:publicId', requireAuth, wrap(async (req: AuthRequest, res) => {
  const data = updateItemSchema.parse(req.body)
  const item = await checklistService.updateItem(req.params.publicId, data)
  res.json({ data: item, message: 'Item updated' })
}))

router.delete('/items/:publicId', requireAuth, wrap(async (req: AuthRequest, res) => {
  await checklistService.deleteItem(req.params.publicId)
  res.json({ message: 'Item deleted' })
}))

export default router
