import { integer, pgEnum, pgTable, serial, text, timestamp, uuid, varchar } from 'drizzle-orm/pg-core'

export const memberRoleEnum = pgEnum('role', ['admin', 'member', 'guest'])
export const memberStatusEnum = pgEnum('member_status', ['active', 'invited', 'paused'])

export const workspace = pgTable('workspace', {
  id: serial('id').primaryKey(),
  publicId: varchar('publicId', { length: 12 }).notNull().unique(),
  name: varchar('name', { length: 255 }).notNull(),
  slug: varchar('slug', { length: 255 }).notNull().unique(),
  description: text('description'),
  plan: text('plan').notNull().default('free'),
  createdBy: uuid('createdBy'),
  createdAt: timestamp('createdAt').defaultNow().notNull(),
  updatedAt: timestamp('updatedAt'),
  deletedAt: timestamp('deletedAt'),
  deletedBy: uuid('deletedBy'),
})

export const workspaceMember = pgTable('workspace_members', {
  id: serial('id').primaryKey(),
  publicId: varchar('publicId', { length: 12 }).notNull().unique(),
  userId: uuid('userId'),
  workspaceId: integer('workspaceId').notNull(),
  createdBy: uuid('createdBy').notNull(),
  createdAt: timestamp('createdAt').defaultNow().notNull(),
  updatedAt: timestamp('updatedAt'),
  deletedAt: timestamp('deletedAt'),
  deletedBy: uuid('deletedBy'),
  role: memberRoleEnum('role').notNull(),
  status: memberStatusEnum('status').notNull().default('invited'),
  email: varchar('email', { length: 255 }).notNull(),
  roleId: integer('roleId'),
  inviteCode: varchar('inviteCode', { length: 32 }).unique(),
})