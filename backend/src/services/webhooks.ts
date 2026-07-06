import { eq, and, desc } from 'drizzle-orm'
import { db } from '../db'
import { webhook } from '../db/schema'
import { generatePublicId } from '../utils/publicId'

function isSafeUrl(urlString: string): boolean {
  try {
    const url = new URL(urlString)
    if (!['http:', 'https:'].includes(url.protocol)) return false
    const hostname = url.hostname.toLowerCase()
    if (
      hostname === 'localhost' ||
      hostname === '127.0.0.1' ||
      hostname === '0.0.0.0' ||
      hostname === '[::1]' ||
      hostname.startsWith('169.254.') ||
      hostname.startsWith('10.') ||
      hostname.startsWith('192.168.') ||
      /^172\.(1[6-9]|2\d|3[01])\./.test(hostname)
    ) return false
    return true
  } catch {
    return false
  }
}

export const webhookService = {
  async getByWorkspace(workspaceId: number) {
    return await db.select({
      id: webhook.publicId,
      name: webhook.name,
      url: webhook.url,
      events: webhook.events,
      active: webhook.active,
      createdAt: webhook.createdAt,
      lastDeliveryAt: webhook.lastDeliveryAt,
      lastDeliveryStatus: webhook.lastDeliveryStatus,
    })
      .from(webhook)
      .where(eq(webhook.workspaceId, workspaceId))
      .orderBy(desc(webhook.createdAt))
  },

  async create(data: {
    name: string
    url: string
    events: string[]
    workspaceId: number
    userId: string
  }) {
    const publicId = generatePublicId()
    const [created] = await db.insert(webhook).values({
      publicId,
      name: data.name,
      url: data.url,
      events: JSON.stringify(data.events),
      workspaceId: data.workspaceId,
      createdBy: data.userId,
    }).returning({ id: webhook.publicId, name: webhook.name, url: webhook.url, events: webhook.events, active: webhook.active, createdAt: webhook.createdAt, lastDeliveryAt: webhook.lastDeliveryAt, lastDeliveryStatus: webhook.lastDeliveryStatus })

    return created
  },

  async delete(publicId: string, workspaceId: number) {
    await db.delete(webhook)
      .where(and(
        eq(webhook.publicId, publicId),
        eq(webhook.workspaceId, workspaceId)
      ))
  },

  async test(publicId: string) {
    const [wh] = await db.select().from(webhook).where(eq(webhook.publicId, publicId)).limit(1)
    if (!wh) throw new Error('Webhook not found')
    if (!isSafeUrl(wh.url)) throw new Error('URL points to a private/internal network')

    try {
      const res = await fetch(wh.url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          event: 'test',
          timestamp: new Date().toISOString(),
        }),
      })
      const status = res.ok ? 'success' : `http_${res.status}`
      await db.update(webhook).set({
        lastDeliveryAt: new Date(),
        lastDeliveryStatus: status,
      }).where(eq(webhook.publicId, publicId))
      return { ok: res.ok, status }
    } catch (err: any) {
      await db.update(webhook).set({
        lastDeliveryAt: new Date(),
        lastDeliveryStatus: 'error',
      }).where(eq(webhook.publicId, publicId))
      throw new Error(err.message || 'Webhook delivery failed')
    }
  },
}
