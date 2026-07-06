import { eq, and, isNull, max, asc } from 'drizzle-orm'
import { db } from '../db'
import { list, board } from '../db/schema'
import { generatePublicId } from '../utils/publicId'
import { AppError } from '../middleware/error'

async function resolveBoardId(boardPublicId: string): Promise<number> {
  const [boardData] = await db.select().from(board)
    .where(eq(board.publicId, boardPublicId))

  if (!boardData) {
    throw new AppError(404, 'Board not found')
  }

  return boardData.id
}

export const listService = {
  async getByBoard(boardPublicId: string) {
    const boardId = await resolveBoardId(boardPublicId)

    return db.select().from(list)
      .where(and(eq(list.boardId, boardId), isNull(list.deletedAt)))
      .orderBy(asc(list.index))
  },

  async getById(publicId: string) {
    const [listData] = await db.select().from(list)
      .where(and(eq(list.publicId, publicId), isNull(list.deletedAt)))

    if (!listData) {
      throw new AppError(404, 'List not found')
    }

    return listData
  },

  async create(data: { boardPublicId: string; name: string }, userId: string) {
    const boardId = await resolveBoardId(data.boardPublicId)

    const result = await db.select({ maxIndex: max(list.index) })
      .from(list)
      .where(eq(list.boardId, boardId))

    const nextIndex = (result[0]?.maxIndex ?? -1) + 1
    const publicId = generatePublicId()

    const [newList] = await db.insert(list).values({
      publicId,
      name: data.name,
      boardId,
      index: nextIndex,
      createdBy: userId
    }).returning()

    return newList
  },

  async update(publicId: string, data: { name?: string }) {
    const listData = await this.getById(publicId)

    const [updated] = await db.update(list)
      .set({ ...data, updatedAt: new Date() })
      .where(eq(list.id, listData.id))
      .returning()

    return updated
  },

  async reorder(boardPublicId: string, listIds: string[]) {
    const boardId = await resolveBoardId(boardPublicId)

    for (let i = 0; i < listIds.length; i++) {
      const [listData] = await db.select().from(list)
        .where(and(eq(list.publicId, listIds[i]), eq(list.boardId, boardId)))

      if (listData) {
        await db.update(list)
          .set({ index: i, updatedAt: new Date() })
          .where(eq(list.id, listData.id))
      }
    }
  },

  async delete(publicId: string) {
    const listData = await this.getById(publicId)

    await db.update(list)
      .set({ deletedAt: new Date() })
      .where(eq(list.id, listData.id))
  }
}
