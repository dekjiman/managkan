import { integer, pgTable, serial, text, timestamp, varchar } from 'drizzle-orm/pg-core'

export const subscription = pgTable('subscriptions', {
  id: serial('id').primaryKey(),
  publicId: varchar('publicId', { length: 12 }).notNull().unique(),
  workspaceId: integer('workspaceId').notNull(),
  plan: text('plan').notNull().default('free'),
  status: text('status').notNull().default('active'),
  startDate: timestamp('startDate').notNull(),
  endDate: timestamp('endDate'),
  midtransOrderId: text('midtransOrderId').unique(),
  paymentAmount: integer('paymentAmount'),
  createdAt: timestamp('createdAt').defaultNow().notNull(),
  updatedAt: timestamp('updatedAt'),
})
