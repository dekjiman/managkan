import { boolean, integer, pgTable, serial, text, timestamp, uuid, varchar } from 'drizzle-orm/pg-core'

export const workspaceIntegration = pgTable('workspace_integrations', {
  id: serial('id').primaryKey(),
  integrationId: varchar('integrationId', { length: 50 }).notNull(),
  workspaceId: integer('workspaceId').notNull(),
  connected: boolean('connected').notNull().default(false),
  config: text('config'),
  connectedBy: uuid('connectedBy'),
  connectedAt: timestamp('connectedAt'),
  updatedAt: timestamp('updatedAt').defaultNow().notNull(),
})
