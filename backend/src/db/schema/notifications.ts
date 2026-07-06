import { boolean, integer, jsonb, pgTable, serial, timestamp, uuid, varchar } from 'drizzle-orm/pg-core'

export const notification = pgTable('notifications', {
  id: serial('id').primaryKey(),
  publicId: varchar('publicId', { length: 12 }).notNull().unique(),
  userId: uuid('userId').notNull(),
  workspaceId: integer('workspaceId'),
  type: varchar('type', { length: 50 }).notNull(),
  title: varchar('title', { length: 255 }).notNull(),
  entityType: varchar('entityType', { length: 50 }),
  entityId: varchar('entityId', { length: 12 }),
  entityUrl: varchar('entityUrl', { length: 500 }),
  data: jsonb('data'),
  read: boolean('read').notNull().default(false),
  createdBy: uuid('createdBy'),
  createdAt: timestamp('createdAt').defaultNow().notNull(),
})
