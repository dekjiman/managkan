import { integer, pgTable, serial, timestamp, uuid, varchar } from 'drizzle-orm/pg-core'

export const list = pgTable('list', {
  id: serial('id').primaryKey(),
  publicId: varchar('publicId', { length: 12 }).notNull().unique(),
  name: varchar('name', { length: 255 }).notNull(),
  index: integer('index').notNull(),
  createdBy: uuid('createdBy'),
  createdAt: timestamp('createdAt').defaultNow().notNull(),
  updatedAt: timestamp('updatedAt'),
  deletedAt: timestamp('deletedAt'),
  deletedBy: uuid('deletedBy'),
  boardId: integer('boardId').notNull(),
})
