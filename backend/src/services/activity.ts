import { eq, desc } from 'drizzle-orm'
import { db } from '../db'
import { cardActivity, cardComment, user } from '../db/schema'
import { generatePublicId } from '../utils/publicId'

export const activityService = {
  async getByCard(cardId: number) {
    const activities = await db.select({
      id: cardActivity.id,
      publicId: cardActivity.publicId,
      type: cardActivity.type,
      cardId: cardActivity.cardId,
      fromIndex: cardActivity.fromIndex,
      toIndex: cardActivity.toIndex,
      fromListId: cardActivity.fromListId,
      toListId: cardActivity.toListId,
      fromTitle: cardActivity.fromTitle,
      toTitle: cardActivity.toTitle,
      fromDescription: cardActivity.fromDescription,
      toDescription: cardActivity.toDescription,
      createdBy: cardActivity.createdBy,
      createdAt: cardActivity.createdAt,
      commentId: cardActivity.commentId,
      fromComment: cardActivity.fromComment,
      toComment: cardActivity.toComment,
      userName: user.name,
      userImage: user.image,
      commentText: cardComment.comment,
    })
      .from(cardActivity)
      .leftJoin(user, eq(cardActivity.createdBy, user.id))
      .leftJoin(cardComment, eq(cardActivity.commentId, cardComment.id))
      .where(eq(cardActivity.cardId, cardId))
      .orderBy(desc(cardActivity.createdAt))

    return activities
  },

  async create(data: {
    type: string
    cardId: number
    userId: string
    fromIndex?: number
    toIndex?: number
    fromListId?: number
    toListId?: number
    labelId?: number
    workspaceMemberId?: number
    fromTitle?: string
    toTitle?: string
    fromDescription?: string
    toDescription?: string
    commentId?: number
    fromComment?: string
    toComment?: string
    fromDueDate?: string
    toDueDate?: string
    attachmentId?: number
  }) {
    const publicId = generatePublicId()

    const [newActivity] = await db.insert(cardActivity).values({
      publicId,
      type: data.type,
      cardId: data.cardId,
      createdBy: data.userId,
      fromIndex: data.fromIndex,
      toIndex: data.toIndex,
      fromListId: data.fromListId,
      toListId: data.toListId,
      labelId: data.labelId,
      workspaceMemberId: data.workspaceMemberId,
      fromTitle: data.fromTitle,
      toTitle: data.toTitle,
      fromDescription: data.fromDescription,
      toDescription: data.toDescription,
      commentId: data.commentId,
      fromComment: data.fromComment,
      toComment: data.toComment,
      fromDueDate: data.fromDueDate ? new Date(data.fromDueDate) : undefined,
      toDueDate: data.toDueDate ? new Date(data.toDueDate) : undefined,
      attachmentId: data.attachmentId,
    }).returning()

    return newActivity
  }
}
