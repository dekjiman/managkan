import { Router } from 'express'
import { z } from 'zod'
import { requireAuth, AuthRequest } from '../middleware/auth'
import { requireBoardAccess } from '../middleware/workspaceAccess'
import { boardService } from '../services/board'
import { wrap } from '../utils/wrap'

const router = Router({ mergeParams: true })

const createSchema = z.object({
  name: z.string().min(1).max(100),
  lists: z.array(z.string().min(1)).default([]),
  labels: z.array(z.string().min(1)).default([]),
  visibility: z.enum(['private', 'public']).optional(),
  type: z.enum(['regular', 'template']).optional(),
  sourceBoardPublicId: z.string().optional()
})

const updateSchema = z.object({
  name: z.string().min(1).max(100).optional(),
  description: z.string().optional(),
  visibility: z.enum(['private', 'public']).optional()
})

router.get('/check-slug-availability', requireAuth, wrap(async (req: AuthRequest, res) => {
  const { boardSlug } = req.query
  if (!boardSlug || typeof boardSlug !== 'string') {
    return res.status(400).json({ message: 'boardSlug is required' })
  }
  const result = await boardService.checkSlugAvailability(boardSlug)
  res.json({ data: result })
}))

router.get('/', requireAuth, wrap(async (req: AuthRequest, res) => {
  const { workspacePublicId } = req.params
  const { type } = req.query
  const boards = await boardService.getByWorkspace(workspacePublicId, type as string | undefined)
  res.json({ data: boards })
}))

router.post('/', requireAuth, wrap(async (req: AuthRequest, res) => {
  const { workspacePublicId } = req.params
  const data = createSchema.parse(req.body)

  const board = await boardService.create(workspacePublicId, data, req.user!.id)
  res.status(201).json({ data: board, message: 'Board created' })
}))

router.get('/:boardPublicId', requireAuth, requireBoardAccess, wrap(async (req: AuthRequest, res) => {
  const board = await boardService.getById(req.params.boardPublicId)
  res.json({ data: board })
}))

router.patch('/:boardPublicId', requireAuth, requireBoardAccess, wrap(async (req: AuthRequest, res) => {
  const data = updateSchema.parse(req.body)
  const board = await boardService.update(req.params.boardPublicId, data)
  res.json({ data: board, message: 'Board updated' })
}))

router.delete('/:boardPublicId', requireAuth, requireBoardAccess, wrap(async (req: AuthRequest, res) => {
  await boardService.delete(req.params.boardPublicId)
  res.json({ message: 'Board deleted' })
}))

export default router
