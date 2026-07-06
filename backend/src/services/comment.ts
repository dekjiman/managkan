import { eq, and, isNull, desc, inArray } from 'drizzle-orm'
import { db } from '../db'
import { cardComment, user, card, list, board, workspace } from '../db/schema'
import { generatePublicId } from '../utils/publicId'
import { AppError } from '../middleware/error'
import { notificationService } from './notifications'
import { logger } from '../config/logger'

const log = logger.child({ module: 'comment' })

export const commentService = {
  async getByCard(cardId: number) {
    const comments = await db.select({
      id: cardComment.id,
      publicId: cardComment.publicId,
      comment: cardComment.comment,
      cardId: cardComment.cardId,
      createdBy: cardComment.createdBy,
      createdAt: cardComment.createdAt,
      updatedAt: cardComment.updatedAt,
      userName: user.name,
      userImage: user.image,
    })
      .from(cardComment)
      .leftJoin(user, eq(cardComment.createdBy, user.id))
      .where(and(eq(cardComment.cardId, cardId), isNull(cardComment.deletedAt)))
      .orderBy(desc(cardComment.createdAt))

    return comments
  },

  async getById(publicId: string) {
    const [comment] = await db.select().from(cardComment)
      .where(and(eq(cardComment.publicId, publicId), isNull(cardComment.deletedAt)))

    if (!comment) {
      throw new AppError(404, 'Comment not found')
    }

    return comment
  },

  async create(data: { cardId: number; comment: string }, userId: string, mentionedUserIds?: string[]) {
    const publicId = generatePublicId()

    const [newComment] = await db.insert(cardComment).values({
      publicId,
      comment: data.comment,
      cardId: data.cardId,
      createdBy: userId
    }).returning()

    // Notify card creator about the comment (but not if they're commenting on their own card)
    try {
      const [cardData] = await db.select().from(card)
        .where(eq(card.id, data.cardId))
        .limit(1)

      if (cardData && cardData.createdBy && cardData.createdBy !== userId) {
        // Get commenter's name
        const [commenter] = await db.select().from(user)
          .where(eq(user.id, userId))
          .limit(1)

        // Build entityUrl: card → list → board → workspace
        let entityUrl: string | undefined
        try {
          const [listData] = await db.select().from(list)
            .where(eq(list.id, cardData.listId))
            .limit(1)
          if (listData) {
            const [boardData] = await db.select().from(board)
              .where(eq(board.id, listData.boardId))
              .limit(1)
            if (boardData) {
              const [wsData] = await db.select().from(workspace)
                .where(eq(workspace.id, boardData.workspaceId))
                .limit(1)
              if (wsData) {
                entityUrl = `/${wsData.slug}/${boardData.slug}/cards/${cardData.publicId}`
              }
            }
          }
        } catch {
          // entityUrl is optional, don't fail if lookup fails
        }

        const commenterName = commenter?.name || 'Someone'
        await notificationService.create({
          userId: cardData.createdBy,
          type: 'comment_added',
          title: `${commenterName} commented on "${cardData.title}"`,
          entityType: 'card',
          entityId: cardData.publicId,
          entityUrl,
          data: {
            comment: data.comment,
            cardTitle: cardData.title,
            commenterName,
          },
          createdBy: userId,
        })
      }
    } catch (error) {
      // Don't fail comment creation if notification fails
      log.error({ err: error }, 'Failed to create comment notification')
    }

    // Notify mentioned users
    if (mentionedUserIds && mentionedUserIds.length > 0) {
      try {
        const mentionedIds = mentionedUserIds.filter(id => id !== userId)
        if (mentionedIds.length > 0) {
          const [commenter] = await db.select().from(user)
            .where(eq(user.id, userId))
            .limit(1)
          const commenterName = commenter?.name || 'Someone'

          const [cardData] = await db.select().from(card)
            .where(eq(card.id, data.cardId))
            .limit(1)

          let entityUrl: string | undefined
          if (cardData) {
            try {
              const [listData] = await db.select().from(list)
                .where(eq(list.id, cardData.listId))
                .limit(1)
              if (listData) {
                const [boardData] = await db.select().from(board)
                  .where(eq(board.id, listData.boardId))
                  .limit(1)
                if (boardData) {
                  const [wsData] = await db.select().from(workspace)
                    .where(eq(workspace.id, boardData.workspaceId))
                    .limit(1)
                  if (wsData) {
                    entityUrl = `/${wsData.slug}/${boardData.slug}/cards/${cardData.publicId}`
                  }
                }
              }
            } catch {}
          }

          const mentionedUsers = await db.select().from(user)
            .where(inArray(user.id, mentionedIds))

          for (const mentioned of mentionedUsers) {
            await notificationService.create({
              userId: mentioned.id,
              type: 'comment_mentioned',
              title: `${commenterName} mentioned you in a comment on "${cardData?.title || 'a card'}"`,
              entityType: 'card',
              entityId: cardData?.publicId,
              entityUrl,
              data: {
                comment: data.comment,
                cardTitle: cardData?.title,
                commenterName,
              },
              createdBy: userId,
            })
          }
        }
      } catch (error) {
        log.error({ err: error }, 'Failed to create mention notifications')
      }
    }

    return newComment
  },

  async update(publicId: string, data: { comment: string }, userId: string) {
    const commentData = await this.getById(publicId)

    if (commentData.createdBy !== userId) {
      throw new AppError(403, 'Can only edit your own comments')
    }

    const [updated] = await db.update(cardComment)
      .set({ comment: data.comment, updatedAt: new Date() })
      .where(eq(cardComment.id, commentData.id))
      .returning()

    return updated
  },

  async delete(publicId: string, userId: string) {
    const commentData = await this.getById(publicId)

    if (commentData.createdBy !== userId) {
      throw new AppError(403, 'Can only delete your own comments')
    }

    await db.update(cardComment)
      .set({ deletedAt: new Date(), deletedBy: userId })
      .where(eq(cardComment.id, commentData.id))
  }
}
