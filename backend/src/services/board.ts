import { eq, and, or, isNull, desc, asc } from 'drizzle-orm'
import { db } from '../db'
import { board, list, card, label, workspace, workspaceMember, user, cardLabel, cardMember, cardChecklist, cardChecklistItem } from '../db/schema'
import { generatePublicId } from '../utils/publicId'
import { AppError } from '../middleware/error'

function toSlug(name: string): string {
  return name
    .toLowerCase()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .replace(/^-|-$/g, '')
}

async function uniqueSlug(baseSlug: string, workspaceId: number): Promise<string> {
  let slug = baseSlug
  let counter = 1
  while (true) {
    const [existing] = await db.select().from(board)
      .where(and(eq(board.slug, slug), eq(board.workspaceId, workspaceId)))
    if (!existing) return slug
    slug = `${baseSlug}-${counter}`
    counter++
  }
}

const LABEL_COLOURS = [
  '#0d9488', '#65a30d', '#0284c7', '#4f46e5',
  '#ca8a04', '#ea580c', '#dc2626', '#db2777'
]

export const boardService = {
  async checkSlugAvailability(slug: string) {
    const [existing] = await db.select().from(board)
      .where(eq(board.slug, slug))
    return { isAvailable: !existing, isReserved: false }
  },

  async getByWorkspace(workspacePublicId: string, type?: string) {
    const [ws] = await db.select().from(workspace)
      .where(eq(workspace.publicId, workspacePublicId))

    if (!ws) {
      throw new AppError(404, 'Workspace not found')
    }

    const conditions = [eq(board.workspaceId, ws.id), isNull(board.deletedAt)]

    if (type) {
      conditions.push(eq(board.type, type as 'regular' | 'template'))
    }

    return db.select().from(board)
      .where(and(...conditions))
      .orderBy(desc(board.createdAt))
  },

  async getById(publicIdOrSlug: string) {
    const [boardData] = await db.select().from(board)
      .where(and(
        or(eq(board.publicId, publicIdOrSlug), eq(board.slug, publicIdOrSlug)),
        isNull(board.deletedAt)
      ))

    if (!boardData) {
      throw new AppError(404, 'Board not found')
    }

    const lists = await db.select().from(list)
      .where(and(eq(list.boardId, boardData.id), isNull(list.deletedAt)))
      .orderBy(asc(list.index))

    const listsWithCards = await Promise.all(
      lists.map(async (l) => {
        const cards = await db.select().from(card)
          .where(and(eq(card.listId, l.id), isNull(card.deletedAt)))
          .orderBy(asc(card.index))

        const cardsWithRelations = await Promise.all(
          cards.map(async (c) => {
            const cardLabels = await db.select({
              cardId: cardLabel.cardId,
              labelId: cardLabel.labelId,
              id: label.id,
              publicId: label.publicId,
              name: label.name,
              colourCode: label.colourCode,
            })
              .from(cardLabel)
              .innerJoin(label, eq(cardLabel.labelId, label.id))
              .where(eq(cardLabel.cardId, c.id))

            const cardMembers = await db.select({
              cardId: cardMember.cardId,
              workspaceMemberId: cardMember.workspaceMemberId,
              id: workspaceMember.id,
              publicId: workspaceMember.publicId,
              userId: workspaceMember.userId,
              role: workspaceMember.role,
              userName: user.name,
              userImage: user.image,
            })
              .from(cardMember)
              .innerJoin(workspaceMember, eq(cardMember.workspaceMemberId, workspaceMember.id))
              .leftJoin(user, eq(workspaceMember.userId, user.id))
              .where(eq(cardMember.cardId, c.id))

            return { ...c, labels: cardLabels, members: cardMembers }
          })
        )

        return { ...l, cards: cardsWithRelations }
      })
    )

    const labels = await db.select().from(label)
      .where(and(eq(label.boardId, boardData.id), isNull(label.deletedAt)))

    const members = await db.select({
      id: workspaceMember.id,
      publicId: workspaceMember.publicId,
      email: workspaceMember.email,
      role: workspaceMember.role,
      userId: workspaceMember.userId,
      user: {
        id: user.id,
        name: user.name,
        email: user.email,
        image: user.image
      }
    })
      .from(workspaceMember)
      .leftJoin(user, eq(workspaceMember.userId, user.id))
      .where(eq(workspaceMember.workspaceId, boardData.workspaceId))

    return {
      ...boardData,
      lists: listsWithCards,
      labels,
      workspace: {
        publicId: (await db.select().from(workspace).where(eq(workspace.id, boardData.workspaceId)))[0]?.publicId,
        members
      }
    }
  },

  async create(workspacePublicId: string, data: { name: string; lists: string[]; labels: string[]; visibility?: string; type?: string; sourceBoardPublicId?: string }, userId: string) {
    const [ws] = await db.select().from(workspace)
      .where(eq(workspace.publicId, workspacePublicId))

    if (!ws) {
      throw new AppError(404, 'Workspace not found')
    }

    const baseSlug = toSlug(data.name)
    if (!baseSlug) {
      throw new AppError(400, 'Invalid board name')
    }
    const slug = await uniqueSlug(baseSlug, ws.id)
    const publicId = generatePublicId()

    const [newBoard] = await db.insert(board).values({
      publicId,
      name: data.name,
      slug,
      workspaceId: ws.id,
      visibility: (data.visibility || 'private') as 'private' | 'public',
      type: (data.type || 'regular') as 'regular' | 'template',
      createdBy: userId
    }).returning()

    if (data.sourceBoardPublicId) {
      const templateBoard = await boardService.getById(data.sourceBoardPublicId)

      if (!templateBoard || templateBoard.type !== 'template') {
        throw new AppError(400, 'Invalid source template')
      }

      // Copy labels with name-colour mapping
      const labelIdMap = new Map<number, number>()
      for (const lbl of templateBoard.labels) {
        const [newLabel] = await db.insert(label).values({
          publicId: generatePublicId(),
          name: lbl.name,
          colourCode: lbl.colourCode,
          boardId: newBoard.id,
          createdBy: userId
        }).returning()
        labelIdMap.set(lbl.id, newLabel.id)
      }

      // Copy lists and cards
      for (const srcList of templateBoard.lists) {
        const [newList] = await db.insert(list).values({
          publicId: generatePublicId(),
          name: srcList.name,
          boardId: newBoard.id,
          index: srcList.index,
          createdBy: userId
        }).returning()

        for (const srcCard of srcList.cards) {
          const [newCard] = await db.insert(card).values({
            publicId: generatePublicId(),
            title: srcCard.title,
            description: srcCard.description || '',
            index: srcCard.index,
            listId: newList.id,
            createdBy: userId,
            dueDate: srcCard.dueDate || null,
            cardNumber: srcCard.cardNumber || null,
          }).returning()

          // Copy card-label associations
          for (const cl of srcCard.labels) {
            const newLabelId = labelIdMap.get(cl.labelId || cl.id)
            if (newLabelId) {
              await db.insert(cardLabel).values({ cardId: newCard.id, labelId: newLabelId })
            }
          }

          // Copy checklists and items
          const srcChecklists = await db.select().from(cardChecklist)
            .where(and(eq(cardChecklist.cardId, srcCard.id), isNull(cardChecklist.deletedAt)))
            .orderBy(asc(cardChecklist.index))

          for (const cl of srcChecklists) {
            const [newChecklist] = await db.insert(cardChecklist).values({
              publicId: generatePublicId(),
              name: cl.name,
              cardId: newCard.id,
              index: cl.index,
              createdBy: userId
            }).returning()

            const items = await db.select().from(cardChecklistItem)
              .where(and(eq(cardChecklistItem.checklistId, cl.id), isNull(cardChecklistItem.deletedAt)))
              .orderBy(asc(cardChecklistItem.index))

            for (const item of items) {
              await db.insert(cardChecklistItem).values({
                publicId: generatePublicId(),
                title: item.title,
                completed: false,
                index: item.index,
                checklistId: newChecklist.id,
                createdBy: userId
              })
            }
          }
        }
      }
    } else {
      const listsToCreate = data.lists.length > 0 ? data.lists : ['To Do', 'In Progress', 'Done']
      for (let i = 0; i < listsToCreate.length; i++) {
        await db.insert(list).values({
          publicId: generatePublicId(),
          name: listsToCreate[i],
          boardId: newBoard.id,
          index: i,
          createdBy: userId
        })
      }

      for (let i = 0; i < data.labels.length; i++) {
        await db.insert(label).values({
          publicId: generatePublicId(),
          name: data.labels[i],
          colourCode: LABEL_COLOURS[i % LABEL_COLOURS.length],
          boardId: newBoard.id,
          createdBy: userId
        })
      }
    }

    return newBoard
  },

  async update(publicId: string, data: { name?: string; description?: string; visibility?: string }) {
    const [boardData] = await db.select().from(board)
      .where(and(eq(board.publicId, publicId), isNull(board.deletedAt)))

    if (!boardData) {
      throw new AppError(404, 'Board not found')
    }

    const [updated] = await db.update(board)
      .set({
        ...(data.name !== undefined && { name: data.name }),
        ...(data.description !== undefined && { description: data.description }),
        ...(data.visibility !== undefined && { visibility: data.visibility as 'private' | 'public' }),
        updatedAt: new Date(),
      })
      .where(eq(board.id, boardData.id))
      .returning()

    return updated
  },

  async delete(publicId: string) {
    const [boardData] = await db.select().from(board)
      .where(and(eq(board.publicId, publicId), isNull(board.deletedAt)))

    if (!boardData) {
      throw new AppError(404, 'Board not found')
    }

    await db.update(board)
      .set({ deletedAt: new Date() })
      .where(eq(board.id, boardData.id))
  }
}
