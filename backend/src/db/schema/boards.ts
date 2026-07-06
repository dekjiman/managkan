import { boolean, integer, pgEnum, pgTable, serial, text, timestamp, uuid, varchar } from 'drizzle-orm/pg-core'

export const boardVisibilityEnum = pgEnum('board_visibility', ['private', 'public'])
export const boardTypeEnum = pgEnum('board_type', ['regular', 'template'])

export const board = pgTable('board', {
  id: serial('id').primaryKey(),
  publicId: varchar('publicId', { length: 12 }).notNull().unique(),
  name: varchar('name', { length: 255 }).notNull(),
  slug: varchar('slug', { length: 255 }).notNull(),
  description: text('description'),
  workspaceId: integer('workspaceId').notNull(),
  visibility: boardVisibilityEnum('visibility').notNull().default('private'),
  type: boardTypeEnum('type').notNull().default('regular'),
  createdBy: uuid('createdBy'),
  createdAt: timestamp('createdAt').defaultNow().notNull(),
  updatedAt: timestamp('updatedAt'),
  deletedAt: timestamp('deletedAt'),
  deletedBy: uuid('deletedBy'),
})