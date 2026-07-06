import { boolean, integer, pgTable, serial, text, timestamp, uuid, varchar } from 'drizzle-orm/pg-core'

export const webhook = pgTable('webhooks', {
  id: serial('id').primaryKey(),
  publicId: varchar('publicId', { length: 12 }).notNull().unique(),
  name: varchar('name', { length: 255 }).notNull(),
  url: text('url').notNull(),
  events: text('events').notNull().default('[]'),
  active: boolean('active').notNull().default(true),
  workspaceId: integer('workspaceId').notNull(),
  createdBy: uuid('createdBy'),
  createdAt: timestamp('createdAt').defaultNow().notNull(),
  lastDeliveryAt: timestamp('lastDeliveryAt'),
  lastDeliveryStatus: varchar('lastDeliveryStatus', { length: 20 }),
})
