import { boolean, integer, pgTable, serial, timestamp, uuid, varchar } from 'drizzle-orm/pg-core'

export const cardChecklist = pgTable('card_checklist', {
  id: serial('id').primaryKey(),
  publicId: varchar('publicId', { length: 12 }).notNull().unique(),
  name: varchar('name', { length: 255 }).notNull(),
  index: integer('index').notNull(),
  cardId: integer('cardId').notNull(),
  createdBy: uuid('createdBy'),
  createdAt: timestamp('createdAt').defaultNow().notNull(),
  updatedAt: timestamp('updatedAt'),
  deletedAt: timestamp('deletedAt'),
  deletedBy: uuid('deletedBy'),
})

export const cardChecklistItem = pgTable('card_checklist_item', {
  id: serial('id').primaryKey(),
  publicId: varchar('publicId', { length: 12 }).notNull().unique(),
  title: varchar('title', { length: 500 }).notNull(),
  completed: boolean('completed').default(false).notNull(),
  index: integer('index').notNull(),
  checklistId: integer('checklistId').notNull(),
  createdBy: uuid('createdBy'),
  createdAt: timestamp('createdAt').defaultNow().notNull(),
  updatedAt: timestamp('updatedAt'),
  deletedAt: timestamp('deletedAt'),
  deletedBy: uuid('deletedBy'),
})
