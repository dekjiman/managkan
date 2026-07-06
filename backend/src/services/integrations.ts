import { eq, and } from 'drizzle-orm'
import { db } from '../db'
import { workspaceIntegration } from '../db/schema'

export const integrationService = {
  async getByWorkspace(workspaceId: number) {
    const rows = await db.select()
      .from(workspaceIntegration)
      .where(eq(workspaceIntegration.workspaceId, workspaceId))
    const map: Record<string, { connected: boolean; config?: string }> = {}
    for (const row of rows) {
      map[row.integrationId] = { connected: row.connected, config: row.config || undefined }
    }
    return map
  },

  async setConnected(data: {
    integrationId: string
    workspaceId: number
    connected: boolean
    userId: string
  }) {
    const existing = await db.select()
      .from(workspaceIntegration)
      .where(and(
        eq(workspaceIntegration.integrationId, data.integrationId),
        eq(workspaceIntegration.workspaceId, data.workspaceId)
      ))
      .limit(1)

    if (existing.length > 0) {
      await db.update(workspaceIntegration)
        .set({
          connected: data.connected,
          connectedBy: data.connected ? data.userId : undefined,
          connectedAt: data.connected ? new Date() : undefined,
          updatedAt: new Date(),
        })
        .where(and(
          eq(workspaceIntegration.integrationId, data.integrationId),
          eq(workspaceIntegration.workspaceId, data.workspaceId)
        ))
    } else {
      await db.insert(workspaceIntegration).values({
        integrationId: data.integrationId,
        workspaceId: data.workspaceId,
        connected: data.connected,
        connectedBy: data.userId,
        connectedAt: data.connected ? new Date() : undefined,
      })
    }
  },
}
