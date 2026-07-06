import { eq, and, or, isNull, inArray, sql } from 'drizzle-orm'
import { db } from '../db'
import { workspace, workspaceMember, board, list, card, label, cardLabel, cardMember, cardChecklist, cardChecklistItem, cardComment, cardAttachment, plan } from '../db/schema'
import { generatePublicId } from '../utils/publicId'
import { AppError } from '../middleware/error'

const RESERVED_SLUGS = ['dashboard', 'login', 'register', 'settings', 'api', 'admin', 'app', 'new', 'edit', 'delete']

function toSlug(name: string): string {
  return name
    .toLowerCase()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .replace(/^-|-$/g, '')
}

async function uniqueSlug(baseSlug: string): Promise<string> {
  let slug = baseSlug
  let counter = 1
  while (true) {
    const [existing] = await db.select().from(workspace)
      .where(eq(workspace.slug, slug))
    if (!existing) return slug
    slug = `${baseSlug}-${counter}`
    counter++
  }
}

export const workspaceService = {
  async checkSlugAvailability(slug: string) {
    const isReserved = RESERVED_SLUGS.includes(slug)
    if (isReserved) return { isAvailable: false, isReserved: true }

    const [existing] = await db.select().from(workspace)
      .where(eq(workspace.slug, slug))

    return { isAvailable: !existing, isReserved: false }
  },

  async getByUser(userId: string) {
    const memberships = await db.select({
      workspaceId: workspaceMember.workspaceId,
      role: workspaceMember.role,
      id: workspace.id,
      publicId: workspace.publicId,
      name: workspace.name,
      slug: workspace.slug,
      description: workspace.description,
      createdBy: workspace.createdBy,
      createdAt: workspace.createdAt,
      updatedAt: workspace.updatedAt,
    })
      .from(workspaceMember)
      .innerJoin(workspace, eq(workspaceMember.workspaceId, workspace.id))
      .where(and(eq(workspaceMember.userId, userId), isNull(workspace.deletedAt)))

    return memberships.map(m => ({
      id: m.id,
      publicId: m.publicId,
      name: m.name,
      slug: m.slug,
      description: m.description,
      createdBy: m.createdBy,
      createdAt: m.createdAt,
      updatedAt: m.updatedAt,
      role: m.role
    }))
  },

  async getById(publicIdOrSlug: string, userId: string) {
    const [ws] = await db.select().from(workspace)
      .where(and(
        or(eq(workspace.publicId, publicIdOrSlug), eq(workspace.slug, publicIdOrSlug)),
        isNull(workspace.deletedAt)
      ))

    if (!ws) {
      throw new AppError(404, 'Workspace not found')
    }

    const [membership] = await db.select().from(workspaceMember)
      .where(and(eq(workspaceMember.workspaceId, ws.id), eq(workspaceMember.userId, userId)))

    if (!membership) {
      throw new AppError(403, 'No access to workspace')
    }

    return { ...ws, role: membership.role }
  },

  async create(data: { name: string; description?: string }, userId: string) {
    // Check workspace limit for user (always check against free plan)
    const [freePlan] = await db.select().from(plan)
      .where(eq(plan.name, 'free'))
      .limit(1)

    const workspaceLimit = freePlan?.workspaceLimit ?? 3

    const [countResult] = await db.select({ count: sql<number>`count(*)::int` })
      .from(workspaceMember)
      .innerJoin(workspace, eq(workspaceMember.workspaceId, workspace.id))
      .where(and(
        eq(workspaceMember.userId, userId),
        isNull(workspace.deletedAt)
      ))

    if (countResult && countResult.count >= workspaceLimit) {
      throw new AppError(403, `Workspace limit reached (${workspaceLimit}). Upgrade your plan to create more workspaces.`)
    }

    const baseSlug = toSlug(data.name)
    if (!baseSlug) {
      throw new AppError(400, 'Invalid workspace name')
    }
    const slug = await uniqueSlug(baseSlug)

    const publicId = generatePublicId()

    const [newWorkspace] = await db.insert(workspace).values({
      publicId,
      name: data.name,
      slug,
      description: data.description,
      createdBy: userId
    }).returning()

    await db.insert(workspaceMember).values({
      publicId: generatePublicId(),
      userId,
      workspaceId: newWorkspace.id,
      createdBy: userId,
      role: 'admin',
      email: '',
      status: 'active'
    })

    return newWorkspace
  },

  async update(publicId: string, data: { name?: string; description?: string }, userId: string) {
    const ws = await this.getById(publicId, userId)

    if (ws.role !== 'admin') {
      throw new AppError(403, 'Only admins can update workspace')
    }

    const [updated] = await db.update(workspace)
      .set({ ...data, updatedAt: new Date() })
      .where(eq(workspace.id, ws.id))
      .returning()

    return updated
  },

  async delete(publicId: string, userId: string) {
    const ws = await this.getById(publicId, userId)

    if (ws.role !== 'admin') {
      throw new AppError(403, 'Only admins can delete workspace')
    }

    const now = new Date()

    // Collect all board IDs
    const boards = await db.select({ id: board.id }).from(board)
      .where(and(eq(board.workspaceId, ws.id), isNull(board.deletedAt)))
    const boardIds = boards.map(b => b.id)

    if (boardIds.length > 0) {
      // Collect all list IDs
      const lists = await db.select({ id: list.id }).from(list)
        .where(and(inArray(list.boardId, boardIds), isNull(list.deletedAt)))
      const listIds = lists.map(l => l.id)

      // Collect all card IDs
      const cardIds: number[] = []
      if (listIds.length > 0) {
        const cards = await db.select({ id: card.id }).from(card)
          .where(and(inArray(card.listId, listIds), isNull(card.deletedAt)))
        cardIds.push(...cards.map(c => c.id))
      }

      // Cascade soft-delete: cards → lists → boards → labels
      if (cardIds.length > 0) {
        await db.update(card).set({ deletedAt: now, deletedBy: userId })
          .where(inArray(card.id, cardIds))
        await db.delete(cardLabel).where(inArray(cardLabel.cardId, cardIds))
        await db.delete(cardMember).where(inArray(cardMember.cardId, cardIds))
        await db.update(cardChecklist).set({ deletedAt: now, deletedBy: userId })
          .where(inArray(cardChecklist.cardId, cardIds))
        await db.update(cardComment).set({ deletedAt: now, deletedBy: userId })
          .where(inArray(cardComment.cardId, cardIds))

        // Checklist items (via checklists of these cards)
        const checklists = await db.select({ id: cardChecklist.id }).from(cardChecklist)
          .where(and(inArray(cardChecklist.cardId, cardIds), isNull(cardChecklist.deletedAt)))
        const checklistIds = checklists.map(cl => cl.id)
        if (checklistIds.length > 0) {
          await db.update(cardChecklistItem).set({ deletedAt: now, deletedBy: userId })
            .where(inArray(cardChecklistItem.checklistId, checklistIds))
        }

        const attachments = await db.select({ id: cardAttachment.id }).from(cardAttachment)
          .where(and(inArray(cardAttachment.cardId, cardIds), isNull(cardAttachment.deletedAt)))
        if (attachments.length > 0) {
          await db.update(cardAttachment).set({ deletedAt: now })
            .where(inArray(cardAttachment.cardId, cardIds))
        }
      }

      if (listIds.length > 0) {
        await db.update(list).set({ deletedAt: now, deletedBy: userId })
          .where(inArray(list.id, listIds))
      }

      await db.update(board).set({ deletedAt: now, deletedBy: userId })
        .where(inArray(board.id, boardIds))

      await db.update(label).set({ deletedAt: now, deletedBy: userId })
        .where(inArray(label.boardId, boardIds))
    }

    // Remove workspace memberships
    await db.delete(workspaceMember)
      .where(eq(workspaceMember.workspaceId, ws.id))

    // Soft-delete the workspace
    await db.update(workspace)
      .set({ deletedAt: now, deletedBy: userId })
      .where(eq(workspace.id, ws.id))
  }
}
