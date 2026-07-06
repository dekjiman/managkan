import { eq, and, desc } from 'drizzle-orm'
import { db } from '../db'
import { apiKey } from '../db/schema'
import { generatePublicId } from '../utils/publicId'
import crypto from 'crypto'

function hashKey(key: string): string {
  return crypto.createHash('sha256').update(key).digest('hex')
}

function generateKey(): { fullKey: string; hash: string; prefix: string } {
  const prefix = 'mck_' + generatePublicId()
  const secret = crypto.randomBytes(32).toString('hex')
  const fullKey = `${prefix}.${secret}`
  const hash = hashKey(fullKey)
  return { fullKey, hash, prefix }
}

export const apiKeyService = {
  async getByWorkspace(workspaceId: number) {
    return await db.select({
      id: apiKey.publicId,
      name: apiKey.name,
      keyPrefix: apiKey.keyPrefix,
      active: apiKey.active,
      permissions: apiKey.permissions,
      createdAt: apiKey.createdAt,
      lastUsedAt: apiKey.lastUsedAt,
    })
      .from(apiKey)
      .where(and(
        eq(apiKey.workspaceId, workspaceId),
        eq(apiKey.active, true)
      ))
      .orderBy(desc(apiKey.createdAt))
  },

  async create(data: {
    name: string
    permissions: string[]
    workspaceId: number
    userId: string
  }) {
    const { fullKey, hash, prefix } = generateKey()
    const publicId = generatePublicId()

    await db.insert(apiKey).values({
      publicId,
      name: data.name,
      keyHash: hash,
      keyPrefix: prefix,
      permissions: JSON.stringify(data.permissions),
      workspaceId: data.workspaceId,
      createdBy: data.userId,
    })

    return { publicId, fullKey, prefix }
  },

  async revoke(publicId: string, workspaceId: number) {
    await db.update(apiKey)
      .set({ active: false, revokedAt: new Date() })
      .where(and(
        eq(apiKey.publicId, publicId),
        eq(apiKey.workspaceId, workspaceId)
      ))
  },
}
