import { eq, and, sql, desc } from 'drizzle-orm'
import { db } from '../db'
import { notification } from '../db/schema'
import { generatePublicId } from '../utils/publicId'

export const notificationService = {
  async create(data: {
    userId: string
    workspaceId?: number
    type: string
    title: string
    entityType?: string
    entityId?: string
    entityUrl?: string
    data?: Record<string, any>
    createdBy?: string
  }) {
    const [n] = await db.insert(notification).values({
      publicId: generatePublicId(),
      userId: data.userId,
      workspaceId: data.workspaceId || null,
      type: data.type,
      title: data.title,
      entityType: data.entityType || null,
      entityId: data.entityId || null,
      entityUrl: data.entityUrl || null,
      data: data.data || null,
      createdBy: data.createdBy || null,
    }).returning()

    return n
  },

  async getByUser(userId: string, limit = 20) {
    return await db.select().from(notification)
      .where(eq(notification.userId, userId))
      .orderBy(desc(notification.createdAt))
      .limit(limit)
  },

  async getUnreadCount(userId: string) {
    const [result] = await db.select({ count: sql<number>`count(*)::int` })
      .from(notification)
      .where(and(eq(notification.userId, userId), eq(notification.read, false)))

    return result?.count || 0
  },

  async markRead(publicId: string, userId: string) {
    await db.update(notification)
      .set({ read: true })
      .where(and(
        eq(notification.publicId, publicId),
        eq(notification.userId, userId),
      ))
  },

  async markAllRead(userId: string) {
    await db.update(notification)
      .set({ read: true })
      .where(and(
        eq(notification.userId, userId),
        eq(notification.read, false),
      ))
  },
}
