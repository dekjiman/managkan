import { eq, and, isNull } from 'drizzle-orm'
import { db } from '../db'
import { label } from '../db/schema'
import { generatePublicId } from '../utils/publicId'
import { AppError } from '../middleware/error'

export const labelService = {
  async getByBoard(boardId: number) {
    return db.select().from(label).where(eq(label.boardId, boardId))
  },

  async getById(publicId: string) {
    const [labelData] = await db.select().from(label).where(eq(label.publicId, publicId))

    if (!labelData) {
      throw new AppError(404, 'Label not found')
    }

    return labelData
  },

  async create(data: { boardId: number; name: string; colourCode?: string }) {
    const publicId = generatePublicId()

    const [newLabel] = await db.insert(label).values({
      publicId,
      name: data.name,
      colourCode: data.colourCode,
      boardId: data.boardId
    }).returning()

    return newLabel
  },

  async update(publicId: string, data: { name?: string; colourCode?: string }) {
    const labelData = await this.getById(publicId)

    const [updated] = await db.update(label)
      .set({ ...data, updatedAt: new Date() })
      .where(eq(label.id, labelData.id))
      .returning()

    return updated
  },

  async delete(publicId: string) {
    const labelData = await this.getById(publicId)

    await db.delete(label).where(eq(label.id, labelData.id))
  }
}
