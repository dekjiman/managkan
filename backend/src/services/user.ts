import { eq } from 'drizzle-orm'
import { db } from '../db'
import { user } from '../db/schema'
import { AppError } from '../middleware/error'

export const userService = {
  async getById(id: string) {
    const [userData] = await db.select().from(user).where(eq(user.id, id))

    if (!userData) {
      throw new AppError(404, 'User not found')
    }

    return userData
  },

  async getByEmail(email: string) {
    const [userData] = await db.select().from(user).where(eq(user.email, email))
    return userData
  },

  async update(id: string, data: { name?: string; image?: string }) {
    const userData = await this.getById(id)

    const [updated] = await db.update(user)
      .set({ ...data, updatedAt: new Date() })
      .where(eq(user.id, userData.id))
      .returning()

    return updated
  }
}
