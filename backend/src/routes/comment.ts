import { Router } from 'express'
import { z } from 'zod'
import { requireAuth, AuthRequest } from '../middleware/auth'
import { requireCommentAccess } from '../middleware/workspaceAccess'
import { commentService } from '../services/comment'
import { activityService } from '../services/activity'
import { wrap } from '../utils/wrap'
import { logger } from '../config/logger'

const log = logger.child({ module: 'comment' })

const router = Router()

const createSchema = z.object({
  cardId: z.coerce.number(),
  comment: z.string().min(1),
  mentionedUserIds: z.array(z.string().uuid()).optional()
})

const updateSchema = z.object({
  comment: z.string().min(1)
})

router.get('/', requireAuth, wrap(async (req: AuthRequest, res) => {
  const { cardId } = req.query
  if (!cardId) {
    return res.status(400).json({ message: 'cardId is required' })
  }
  const comments = await commentService.getByCard(Number(cardId))
  res.json({ data: comments })
}))

router.post('/', requireAuth, wrap(async (req: AuthRequest, res) => {
  const data = createSchema.parse(req.body)
  const comment = await commentService.create(data, req.user!.id, data.mentionedUserIds)

  try {
    await activityService.create({
      type: 'card.updated.comment.added',
      cardId: data.cardId,
      userId: req.user!.id,
      commentId: comment.id
    })
  } catch (activityErr) {
    log.error({ err: activityErr }, 'Failed to create activity')
  }

  res.status(201).json({ data: comment, message: 'Comment created' })
}))

router.patch('/:publicId', requireAuth, requireCommentAccess, wrap(async (req: AuthRequest, res) => {
  const data = updateSchema.parse(req.body)
  const comment = await commentService.update(req.params.publicId, data, req.user!.id)
  res.json({ data: comment, message: 'Comment updated' })
}))

router.delete('/:publicId', requireAuth, requireCommentAccess, wrap(async (req: AuthRequest, res) => {
  const commentData = await commentService.getById(req.params.publicId)
  await commentService.delete(req.params.publicId, req.user!.id)

  try {
    await activityService.create({
      type: 'card.updated.comment.deleted',
      cardId: commentData.cardId,
      userId: req.user!.id
    })
  } catch (activityErr) {
    log.error({ err: activityErr }, 'Failed to create activity')
  }

  res.json({ message: 'Comment deleted' })
}))

export default router
