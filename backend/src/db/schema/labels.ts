import { integer, pgTable, serial, timestamp, uuid, varchar } from 'drizzle-orm/pg-core'

export const label = pgTable('label', {
  id: serial('id').primaryKey(),
  publicId: varchar('publicId', { length: 12 }).notNull().unique(),
  name: varchar('name', { length: 255 }).notNull(),
  colourCode: varchar('colourCode', { length: 7 }),
  createdBy: uuid('createdBy'),
  createdAt: timestamp('createdAt').defaultNow().notNull(),
  updatedAt: timestamp('updatedAt'),
  boardId: integer('boardId').notNull(),
  deletedAt: timestamp('deletedAt'),
  deletedBy: uuid('deletedBy'),
})

export const cardLabel = pgTable('_card_labels', {
  cardId: integer('cardId').notNull(),
  labelId: integer('labelId').notNull(),
})

export const cardMember = pgTable('_card_workspace_members', {
  cardId: integer('cardId').notNull(),
  workspaceMemberId: integer('workspaceMemberId').notNull(),
})
