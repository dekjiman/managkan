import { boolean, integer, pgTable, serial, text, timestamp, uuid, varchar } from 'drizzle-orm/pg-core'

export const apiKey = pgTable('api_keys', {
  id: serial('id').primaryKey(),
  publicId: varchar('publicId', { length: 12 }).notNull().unique(),
  name: varchar('name', { length: 255 }).notNull(),
  keyHash: text('keyHash').notNull(),
  keyPrefix: varchar('keyPrefix', { length: 16 }).notNull(),
  permissions: text('permissions').notNull().default('["read"]'),
  active: boolean('active').notNull().default(true),
  workspaceId: integer('workspaceId').notNull(),
  createdBy: uuid('createdBy'),
  createdAt: timestamp('createdAt').defaultNow().notNull(),
  lastUsedAt: timestamp('lastUsedAt'),
  revokedAt: timestamp('revokedAt'),
})
