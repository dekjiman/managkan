import { integer, pgTable, serial, text, timestamp, uuid, varchar } from 'drizzle-orm/pg-core'

export const card = pgTable('card', {
  id: serial('id').primaryKey(),
  publicId: varchar('publicId', { length: 12 }).notNull().unique(),
  title: text('title').notNull(),
  description: text('description'),
  index: integer('index').notNull(),
  createdBy: uuid('createdBy'),
  createdAt: timestamp('createdAt').defaultNow().notNull(),
  updatedAt: timestamp('updatedAt'),
  deletedAt: timestamp('deletedAt'),
  deletedBy: uuid('deletedBy'),
  listId: integer('listId').notNull(),
  dueDate: timestamp('dueDate'),
  cardNumber: integer('cardNumber'),
})
