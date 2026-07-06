import { integer, pgTable, serial, timestamp, uuid, varchar } from 'drizzle-orm/pg-core'

export const cardAttachment = pgTable('card_attachment', {
  id: serial('id').primaryKey(),
  publicId: varchar('publicId', { length: 12 }).notNull().unique(),
  cardId: integer('cardId').notNull(),
  filename: varchar('filename', { length: 255 }).notNull(),
  originalFilename: varchar('originalFilename', { length: 255 }).notNull(),
  contentType: varchar('contentType', { length: 100 }).notNull(),
  size: integer('size').notNull(),
  path: varchar('path', { length: 500 }).notNull(),
  createdBy: uuid('createdBy'),
  createdAt: timestamp('createdAt').defaultNow().notNull(),
  deletedAt: timestamp('deletedAt'),
})
