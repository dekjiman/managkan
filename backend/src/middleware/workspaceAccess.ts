import { Response, NextFunction } from 'express'
import { eq, and, or, isNull } from 'drizzle-orm'
import { db } from '../db'
import { workspaceMember, board, list, card, workspace } from '../db/schema'
import { AuthRequest } from './auth'

async function isWorkspaceMember(workspaceId: number, userId: string): Promise<boolean> {
  const [membership] = await db.select({ id: workspaceMember.id })
    .from(workspaceMember)
    .where(and(
      eq(workspaceMember.workspaceId, workspaceId),
      eq(workspaceMember.userId, userId),
      isNull(workspaceMember.deletedAt)
    ))
    .limit(1)
  return !!membership
}

async function getWorkspaceIdByBoard(boardPublicIdOrSlug: string): Promise<number | null> {
  const [b] = await db.select({ workspaceId: board.workspaceId })
    .from(board)
    .where(and(
      or(eq(board.publicId, boardPublicIdOrSlug), eq(board.slug, boardPublicIdOrSlug)),
      isNull(board.deletedAt)
    ))
    .limit(1)
  return b?.workspaceId ?? null
}

async function getWorkspaceIdByCard(cardPublicId: string): Promise<number | null> {
  const [c] = await db.select({ workspaceId: board.workspaceId })
    .from(card)
    .innerJoin(list, eq(card.listId, list.id))
    .innerJoin(board, eq(list.boardId, board.id))
    .where(and(eq(card.publicId, cardPublicId), isNull(card.deletedAt)))
    .limit(1)
  return c?.workspaceId ?? null
}

async function getWorkspaceIdByList(listPublicId: string): Promise<number | null> {
  const [l] = await db.select({ workspaceId: board.workspaceId })
    .from(list)
    .innerJoin(board, eq(list.boardId, board.id))
    .where(and(eq(list.publicId, listPublicId), isNull(list.deletedAt)))
    .limit(1)
  return l?.workspaceId ?? null
}

async function getWorkspaceIdByComment(commentPublicId: string): Promise<number | null> {
  const { cardComment } = await import('../db/schema')
  const [c] = await db.select({ workspaceId: board.workspaceId })
    .from(cardComment)
    .innerJoin(card, eq(cardComment.cardId, card.id))
    .innerJoin(list, eq(card.listId, list.id))
    .innerJoin(board, eq(list.boardId, board.id))
    .where(eq(cardComment.publicId, commentPublicId))
    .limit(1)
  return c?.workspaceId ?? null
}

async function getWorkspaceIdByLabel(labelPublicId: string): Promise<number | null> {
  const { label } = await import('../db/schema')
  const [l] = await db.select({ workspaceId: board.workspaceId })
    .from(label)
    .innerJoin(board, eq(label.boardId, board.id))
    .where(eq(label.publicId, labelPublicId))
    .limit(1)
  return l?.workspaceId ?? null
}

export function requireWorkspaceAccess(paramName: string, resolveFn: (paramValue: string) => Promise<number | null>) {
  return async (req: AuthRequest, res: Response, next: NextFunction) => {
    try {
      const paramValue = req.params[paramName] || (req.query[paramName] as string)
      if (!paramValue) {
        return res.status(400).json({ message: `${paramName} is required` })
      }

      const workspaceId = await resolveFn(paramValue)
      if (!workspaceId) {
        return res.status(404).json({ message: 'Resource not found' })
      }

      const hasAccess = await isWorkspaceMember(workspaceId, req.user!.id)
      if (!hasAccess) {
        return res.status(403).json({ message: 'No access to this workspace' })
      }

      next()
    } catch {
      return res.status(500).json({ message: 'Access check failed' })
    }
  }
}

export const requireBoardAccess = requireWorkspaceAccess('boardPublicId', getWorkspaceIdByBoard)
export const requireCardAccess = requireWorkspaceAccess('publicId', getWorkspaceIdByCard)
export const requireListAccess = requireWorkspaceAccess('publicId', getWorkspaceIdByList)
export const requireCommentAccess = requireWorkspaceAccess('publicId', getWorkspaceIdByComment)
export const requireLabelAccess = requireWorkspaceAccess('publicId', getWorkspaceIdByLabel)
