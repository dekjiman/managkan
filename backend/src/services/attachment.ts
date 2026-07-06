import { eq, and, isNull } from 'drizzle-orm'
import { db } from '../db'
import { cardAttachment } from '../db/schema'
import { generatePublicId } from '../utils/publicId'
import { AppError } from '../middleware/error'

export const attachmentService = {
  async getByCard(cardId: number) {
    return db.select().from(cardAttachment)
      .where(and(eq(cardAttachment.cardId, cardId), isNull(cardAttachment.deletedAt)))
  },

  async getById(publicId: string) {
    const [attachment] = await db.select().from(cardAttachment)
      .where(eq(cardAttachment.publicId, publicId))

    if (!attachment) {
      throw new AppError(404, 'Attachment not found')
    }

    return attachment
  },

  async create(data: {
    cardId: number
    filename: string
    originalFilename: string
    contentType: string
    size: number
    path: string
  }, userId: string) {
    const publicId = generatePublicId()

    const [attachment] = await db.insert(cardAttachment).values({
      publicId,
      cardId: data.cardId,
      filename: data.filename,
      originalFilename: data.originalFilename,
      contentType: data.contentType,
      size: data.size,
      path: data.path,
      createdBy: userId
    }).returning()

    return attachment
  },

  async delete(publicId: string) {
    const [attachment] = await db.select().from(cardAttachment)
      .where(eq(cardAttachment.publicId, publicId))

    if (!attachment) {
      throw new AppError(404, 'Attachment not found')
    }

    await db.update(cardAttachment)
      .set({ deletedAt: new Date() })
      .where(eq(cardAttachment.id, attachment.id))
  }
}
