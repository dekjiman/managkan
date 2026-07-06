import { bigint, boolean, integer, jsonb, pgTable, serial, text, timestamp, varchar } from 'drizzle-orm/pg-core'

export const plan = pgTable('plans', {
  id: serial('id').primaryKey(),
  name: varchar('name', { length: 20 }).notNull().unique(),
  displayName: varchar('displayName', { length: 50 }).notNull(),
  price: integer('price').notNull().default(0),
  currency: varchar('currency', { length: 3 }).notNull().default('IDR'),
  boardLimit: integer('boardLimit').notNull().default(3),
  memberLimit: integer('memberLimit').notNull().default(3),
  workspaceLimit: integer('workspaceLimit').notNull().default(3),
  storageLimit: bigint('storageLimit', { mode: 'number' }).notNull().default(10485760),
  features: jsonb('features').notNull().default('[]'),
  isActive: boolean('isActive').notNull().default(true),
  createdAt: timestamp('createdAt').defaultNow().notNull(),
  updatedAt: timestamp('updatedAt'),
})
