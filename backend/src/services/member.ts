import { eq, and } from 'drizzle-orm'
import crypto from 'crypto'
import { db } from '../db'
import { workspaceMember, workspace, user } from '../db/schema'
import { generatePublicId } from '../utils/publicId'
import { AppError } from '../middleware/error'
import { notificationService } from './notifications'
import { logger } from '../config/logger'

const log = logger.child({ module: 'member' })

type MemberRole = 'admin' | 'member' | 'guest'

function generateInviteCode(): string {
  return crypto.randomBytes(16).toString('hex')
}

export const memberService = {
  async getByWorkspace(workspaceId: number) {
    const members = await db.select({
      id: workspaceMember.id,
      publicId: workspaceMember.publicId,
      userId: workspaceMember.userId,
      workspaceId: workspaceMember.workspaceId,
      role: workspaceMember.role,
      status: workspaceMember.status,
      email: workspaceMember.email,
      createdAt: workspaceMember.createdAt,
      updatedAt: workspaceMember.updatedAt,
      userName: user.name,
      userImage: user.image,
    })
      .from(workspaceMember)
      .leftJoin(user, eq(workspaceMember.userId, user.id))
      .where(eq(workspaceMember.workspaceId, workspaceId))

    return members
  },

  async getById(publicId: string) {
    const [member] = await db.select().from(workspaceMember)
      .where(eq(workspaceMember.publicId, publicId))

    if (!member) {
      throw new AppError(404, 'Member not found')
    }

    return member
  },

  async add(data: { workspaceId: number; userId: string | null; role?: string; email?: string; createdBy: string }) {
    if (data.userId) {
      const [existing] = await db.select().from(workspaceMember)
        .where(and(
          eq(workspaceMember.workspaceId, data.workspaceId),
          eq(workspaceMember.userId, data.userId)
        ))

      if (existing) {
        throw new AppError(400, 'User is already a member')
      }
    } else {
      const [existing] = await db.select().from(workspaceMember)
        .where(and(
          eq(workspaceMember.workspaceId, data.workspaceId),
          eq(workspaceMember.email, data.email || '')
        ))

      if (existing) {
        throw new AppError(400, 'Email has already been invited')
      }
    }

    const publicId = generatePublicId()

    const [newMember] = await db.insert(workspaceMember).values({
      publicId,
      userId: data.userId,
      workspaceId: data.workspaceId,
      createdBy: data.createdBy,
      role: (data.role || 'member') as MemberRole,
      email: data.email || '',
      status: data.userId ? 'active' : 'invited',
      inviteCode: data.userId ? null : generateInviteCode()
    }).returning()

    // Create notification for the new member
    try {
      const notificationType = data.userId ? 'member_added' : 'member_invited'
      
      // Build entityUrl
      let entityUrl: string | undefined
      try {
        const [wsData] = await db.select().from(workspace)
          .where(eq(workspace.id, data.workspaceId))
          .limit(1)
        if (wsData) {
          entityUrl = `/${wsData.slug}/members`
        }
      } catch {
        // entityUrl is optional
      }

      await notificationService.create({
        userId: data.userId || data.createdBy,
        workspaceId: data.workspaceId,
        type: notificationType,
        title: data.userId 
          ? `You have been added to a workspace`
          : `You have been invited to join a workspace`,
        entityType: 'member',
        entityId: publicId,
        entityUrl,
        createdBy: data.createdBy,
      })
    } catch (error) {
      log.error({ err: error }, 'Failed to create notification')
    }

    return newMember
  },

  async updateRole(publicId: string, role: string) {
    const member = await this.getById(publicId)

    const [updated] = await db.update(workspaceMember)
      .set({ role: role as MemberRole, updatedAt: new Date() })
      .where(eq(workspaceMember.id, member.id))
      .returning()

    return updated
  },

  async remove(publicId: string) {
    const member = await this.getById(publicId)
    await db.delete(workspaceMember).where(eq(workspaceMember.id, member.id))
  },

  async activateByEmail(email: string, userId: string) {
    await db.update(workspaceMember)
      .set({ status: 'active', userId, updatedAt: new Date() })
      .where(and(
        eq(workspaceMember.email, email),
        eq(workspaceMember.status, 'invited')
      ))
  },

  async getByInviteCode(code: string) {
    const [member] = await db.select({
      id: workspaceMember.id,
      publicId: workspaceMember.publicId,
      email: workspaceMember.email,
      role: workspaceMember.role,
      status: workspaceMember.status,
      inviteCode: workspaceMember.inviteCode,
      workspaceId: workspaceMember.workspaceId,
      workspaceName: workspace.name,
    })
      .from(workspaceMember)
      .innerJoin(workspace, eq(workspaceMember.workspaceId, workspace.id))
      .where(eq(workspaceMember.inviteCode, code))

    if (!member) {
      throw new AppError(404, 'Invalid invite code')
    }

    if (member.status !== 'invited') {
      throw new AppError(400, 'This invite has already been used')
    }

    return member
  },

  async activateByInviteCode(code: string, userId: string) {
    const [updated] = await db.update(workspaceMember)
      .set({ status: 'active', userId, inviteCode: null, updatedAt: new Date() })
      .where(eq(workspaceMember.inviteCode, code))
      .returning()

    return updated
  },

  async getByIdWithWorkspace(publicId: string) {
    const [member] = await db.select({
      id: workspaceMember.id,
      publicId: workspaceMember.publicId,
      email: workspaceMember.email,
      role: workspaceMember.role,
      status: workspaceMember.status,
      inviteCode: workspaceMember.inviteCode,
      workspaceId: workspaceMember.workspaceId,
      workspaceName: workspace.name,
    })
      .from(workspaceMember)
      .innerJoin(workspace, eq(workspaceMember.workspaceId, workspace.id))
      .where(eq(workspaceMember.publicId, publicId))

    if (!member) {
      throw new AppError(404, 'Member not found')
    }

    return member
  },

  async regenerateInviteCode(publicId: string) {
    const member = await this.getById(publicId)
    const newCode = generateInviteCode()

    const [updated] = await db.update(workspaceMember)
      .set({ inviteCode: newCode, updatedAt: new Date() })
      .where(eq(workspaceMember.id, member.id))
      .returning()

    return updated
  }
}
