import { eq, and, isNull, max, asc } from 'drizzle-orm'
import { db } from '../db'
import { cardChecklist, cardChecklistItem } from '../db/schema'
import { generatePublicId } from '../utils/publicId'
import { AppError } from '../middleware/error'

export const checklistService = {
  async getByCard(cardId: number) {
    const checklists = await db.select().from(cardChecklist)
      .where(eq(cardChecklist.cardId, cardId))

    const checklistsWithItems = await Promise.all(
      checklists.map(async (cl) => {
        const items = await db.select().from(cardChecklistItem)
          .where(eq(cardChecklistItem.checklistId, cl.id))
          .orderBy(asc(cardChecklistItem.index))
        return { ...cl, items }
      })
    )

    return checklistsWithItems
  },

  async getChecklistById(publicId: string) {
    const [checklist] = await db.select().from(cardChecklist)
      .where(eq(cardChecklist.publicId, publicId))

    if (!checklist) {
      throw new AppError(404, 'Checklist not found')
    }

    return checklist
  },

  async getItemById(publicId: string) {
    const [item] = await db.select().from(cardChecklistItem)
      .where(eq(cardChecklistItem.publicId, publicId))

    if (!item) {
      throw new AppError(404, 'Checklist item not found')
    }

    return item
  },

  async createChecklist(data: { cardId: number; name: string }) {
    const publicId = generatePublicId()

    const [newChecklist] = await db.insert(cardChecklist).values({
      publicId,
      name: data.name,
      cardId: data.cardId,
      index: 0
    }).returning()

    return newChecklist
  },

  async updateChecklist(publicId: string, data: { name?: string }) {
    const checklist = await this.getChecklistById(publicId)

    const [updated] = await db.update(cardChecklist)
      .set({ ...data, updatedAt: new Date() })
      .where(eq(cardChecklist.id, checklist.id))
      .returning()

    return updated
  },

  async deleteChecklist(publicId: string) {
    const checklist = await this.getChecklistById(publicId)
    await db.delete(cardChecklist).where(eq(cardChecklist.id, checklist.id))
  },

  async createItem(data: { checklistId: number; title: string }) {
    const result = await db.select({ maxIndex: max(cardChecklistItem.index) })
      .from(cardChecklistItem)
      .where(eq(cardChecklistItem.checklistId, data.checklistId))

    const nextIndex = (result[0]?.maxIndex ?? -1) + 1

    const publicId = generatePublicId()

    const [newItem] = await db.insert(cardChecklistItem).values({
      publicId,
      title: data.title,
      checklistId: data.checklistId,
      index: nextIndex
    }).returning()

    return newItem
  },

  async updateItem(publicId: string, data: { title?: string; completed?: boolean }) {
    const item = await this.getItemById(publicId)

    const [updated] = await db.update(cardChecklistItem)
      .set({ ...data, updatedAt: new Date() })
      .where(eq(cardChecklistItem.id, item.id))
      .returning()

    return updated
  },

  async deleteItem(publicId: string) {
    const item = await this.getItemById(publicId)
    await db.delete(cardChecklistItem).where(eq(cardChecklistItem.id, item.id))
  }
}
