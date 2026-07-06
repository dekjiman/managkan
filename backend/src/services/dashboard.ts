import { db } from '../db'
import { workspace, workspaceMember, board, list, card, cardActivity, user } from '../db/schema'
import { eq, and, isNull, sql, gte, lte } from 'drizzle-orm'

export const dashboardService = {
  async getData(userId: string, workspacePublicId?: string) {
    const [summary, cardsPerList, cardsOverTime, overdueCards, dueSoonCards, userActivity, boardsPerWorkspace] = await Promise.all([
      this.getSummary(userId, workspacePublicId),
      this.getCardsPerList(userId, workspacePublicId),
      this.getCardsOverTime(userId, workspacePublicId),
      this.getOverdueCards(userId, workspacePublicId),
      this.getDueSoonCards(userId, workspacePublicId),
      this.getUserActivity(userId, workspacePublicId),
      this.getBoardsPerWorkspace(userId, workspacePublicId),
    ])

    return {
      summary,
      cardsPerList,
      cardsOverTime,
      overdue: overdueCards,
      dueSoon: dueSoonCards,
      userActivity,
      boardsPerWorkspace,
    }
  },

  async getUserWorkspaceIds(userId: string, workspacePublicId?: string) {
    if (workspacePublicId) {
      const [ws] = await db.select({ id: workspace.id })
        .from(workspace)
        .where(eq(workspace.publicId, workspacePublicId))
        .limit(1)
      return ws ? [ws.id] : []
    }

    const memberships = await db.select({ workspaceId: workspaceMember.workspaceId })
      .from(workspaceMember)
      .where(and(
        eq(workspaceMember.userId, userId),
        isNull(workspaceMember.deletedAt)
      ))
    return memberships.map(m => m.workspaceId)
  },

  async getSummary(userId: string, workspacePublicId?: string) {
    const wsIds = await this.getUserWorkspaceIds(userId, workspacePublicId)
    if (wsIds.length === 0) return { workspaces: 0, boards: 0, cards: 0, members: 0 }

    const wsIdList = sql`(${sql.join(wsIds.map(id => sql`${id}`), sql`, `)})`

    const [wsCount] = await db.select({ count: sql<number>`count(*)::int` })
      .from(workspace)
      .where(and(sql`${workspace.id} in ${wsIdList}`, isNull(workspace.deletedAt)))

    const [boardCount] = await db.select({ count: sql<number>`count(*)::int` })
      .from(board)
      .where(and(sql`${board.workspaceId} in ${wsIdList}`, isNull(board.deletedAt)))

    const [cardCount] = await db.select({ count: sql<number>`count(*)::int` })
      .from(card)
      .innerJoin(list, eq(card.listId, list.id))
      .innerJoin(board, eq(list.boardId, board.id))
      .where(and(sql`${board.workspaceId} in ${wsIdList}`, isNull(card.deletedAt)))

    const [memberCount] = await db.select({ count: sql<number>`count(*)::int` })
      .from(workspaceMember)
      .where(and(sql`${workspaceMember.workspaceId} in ${wsIdList}`, isNull(workspaceMember.deletedAt)))

    return {
      workspaces: wsCount?.count || 0,
      boards: boardCount?.count || 0,
      cards: cardCount?.count || 0,
      members: memberCount?.count || 0,
    }
  },

  async getCardsPerList(userId: string, workspacePublicId?: string) {
    const wsIds = await this.getUserWorkspaceIds(userId, workspacePublicId)
    if (wsIds.length === 0) return []

    const wsIdList = sql`(${sql.join(wsIds.map(id => sql`${id}`), sql`, `)})`

    const results = await db.select({
      listName: list.name,
      count: sql<number>`count(*)::int`,
    })
      .from(card)
      .innerJoin(list, eq(card.listId, list.id))
      .innerJoin(board, eq(list.boardId, board.id))
      .where(and(sql`${board.workspaceId} in ${wsIdList}`, isNull(card.deletedAt), isNull(list.deletedAt)))
      .groupBy(list.name)
      .orderBy(sql`count(*) desc`)

    return results.map(r => ({ name: r.listName, count: r.count }))
  },

  async getCardsOverTime(userId: string, workspacePublicId?: string) {
    const wsIds = await this.getUserWorkspaceIds(userId, workspacePublicId)
    if (wsIds.length === 0) return []

    const wsIdList = sql`(${sql.join(wsIds.map(id => sql`${id}`), sql`, `)})`

    const twelveWeeksAgo = new Date()
    twelveWeeksAgo.setDate(twelveWeeksAgo.getDate() - 84)

    const results = await db.select({
      date: sql<string>`to_char(date_trunc('week', ${card.createdAt}), 'YYYY-MM-DD')`,
      count: sql<number>`count(*)::int`,
    })
      .from(card)
      .innerJoin(list, eq(card.listId, list.id))
      .innerJoin(board, eq(list.boardId, board.id))
      .where(and(
        sql`${board.workspaceId} in ${wsIdList}`,
        isNull(card.deletedAt),
        gte(card.createdAt, twelveWeeksAgo)
      ))
      .groupBy(sql`date_trunc('week', ${card.createdAt})`)
      .orderBy(sql`date_trunc('week', ${card.createdAt})`)

    return results.map(r => ({ date: r.date, count: r.count }))
  },

  async getOverdueCards(userId: string, workspacePublicId?: string) {
    const wsIds = await this.getUserWorkspaceIds(userId, workspacePublicId)
    if (wsIds.length === 0) return { count: 0, cards: [] }

    const wsIdList = sql`(${sql.join(wsIds.map(id => sql`${id}`), sql`, `)})`

    const results = await db.select({
      publicId: card.publicId,
      title: card.title,
      dueDate: card.dueDate,
      listName: list.name,
      boardName: board.name,
    })
      .from(card)
      .innerJoin(list, eq(card.listId, list.id))
      .innerJoin(board, eq(list.boardId, board.id))
      .where(and(
        sql`${board.workspaceId} in ${wsIdList}`,
        isNull(card.deletedAt),
        lte(card.dueDate, new Date()),
        sql`${card.dueDate} is not null`
      ))
      .orderBy(sql`${card.dueDate} asc`)
      .limit(10)

    const [countResult] = await db.select({ count: sql<number>`count(*)::int` })
      .from(card)
      .innerJoin(list, eq(card.listId, list.id))
      .innerJoin(board, eq(list.boardId, board.id))
      .where(and(
        sql`${board.workspaceId} in ${wsIdList}`,
        isNull(card.deletedAt),
        lte(card.dueDate, new Date()),
        sql`${card.dueDate} is not null`
      ))

    return { count: countResult?.count || 0, cards: results }
  },

  async getDueSoonCards(userId: string, workspacePublicId?: string) {
    const wsIds = await this.getUserWorkspaceIds(userId, workspacePublicId)
    if (wsIds.length === 0) return { count: 0, cards: [] }

    const wsIdList = sql`(${sql.join(wsIds.map(id => sql`${id}`), sql`, `)})`

    const now = new Date()
    const sevenDaysLater = new Date()
    sevenDaysLater.setDate(sevenDaysLater.getDate() + 7)

    const results = await db.select({
      publicId: card.publicId,
      title: card.title,
      dueDate: card.dueDate,
      listName: list.name,
      boardName: board.name,
    })
      .from(card)
      .innerJoin(list, eq(card.listId, list.id))
      .innerJoin(board, eq(list.boardId, board.id))
      .where(and(
        sql`${board.workspaceId} in ${wsIdList}`,
        isNull(card.deletedAt),
        gte(card.dueDate, now),
        lte(card.dueDate, sevenDaysLater)
      ))
      .orderBy(sql`${card.dueDate} asc`)
      .limit(10)

    const [countResult] = await db.select({ count: sql<number>`count(*)::int` })
      .from(card)
      .innerJoin(list, eq(card.listId, list.id))
      .innerJoin(board, eq(list.boardId, board.id))
      .where(and(
        sql`${board.workspaceId} in ${wsIdList}`,
        isNull(card.deletedAt),
        gte(card.dueDate, now),
        lte(card.dueDate, sevenDaysLater)
      ))

    return { count: countResult?.count || 0, cards: results }
  },

  async getUserActivity(userId: string, workspacePublicId?: string) {
    const wsIds = await this.getUserWorkspaceIds(userId, workspacePublicId)
    if (wsIds.length === 0) return []

    const wsIdList = sql`(${sql.join(wsIds.map(id => sql`${id}`), sql`, `)})`

    const thirtyDaysAgo = new Date()
    thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30)

    const results = await db.select({
      userName: user.name,
      count: sql<number>`count(*)::int`,
    })
      .from(cardActivity)
      .innerJoin(user, eq(cardActivity.createdBy, user.id))
      .innerJoin(card, eq(cardActivity.cardId, card.id))
      .innerJoin(list, eq(card.listId, list.id))
      .innerJoin(board, eq(list.boardId, board.id))
      .where(and(
        sql`${board.workspaceId} in ${wsIdList}`,
        gte(cardActivity.createdAt, thirtyDaysAgo)
      ))
      .groupBy(user.name)
      .orderBy(sql`count(*) desc`)
      .limit(10)

    return results.map(r => ({ name: r.userName || 'Unknown', count: r.count }))
  },

  async getBoardsPerWorkspace(userId: string, workspacePublicId?: string) {
    const wsIds = await this.getUserWorkspaceIds(userId, workspacePublicId)
    if (wsIds.length === 0) return []

    const wsIdList = sql`(${sql.join(wsIds.map(id => sql`${id}`), sql`, `)})`

    const results = await db.select({
      workspaceName: workspace.name,
      count: sql<number>`count(*)::int`,
    })
      .from(board)
      .innerJoin(workspace, eq(board.workspaceId, workspace.id))
      .where(and(sql`${board.workspaceId} in ${wsIdList}`, isNull(board.deletedAt)))
      .groupBy(workspace.name)
      .orderBy(sql`count(*) desc`)

    return results.map(r => ({ name: r.workspaceName, count: r.count }))
  },
}
