import { eq, asc } from 'drizzle-orm'
import { db } from '../db'
import { plan } from '../db/schema'

export const planService = {
  async getAll() {
    return await db.select().from(plan)
      .where(eq(plan.isActive, true))
      .orderBy(asc(plan.price))
  },

  async getByName(name: string) {
    const [p] = await db.select().from(plan)
      .where(eq(plan.name, name))
      .limit(1)
    return p || null
  },
}
