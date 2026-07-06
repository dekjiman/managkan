import { eq, and, isNull, sql } from 'drizzle-orm'
import midtransClient from 'midtrans-client'
import crypto from 'crypto'
import { db } from '../db'
import { subscription, workspace, workspaceMember, board, list, card, cardAttachment, plan } from '../db/schema'
import { generatePublicId } from '../utils/publicId'
import { AppError } from '../middleware/error'
import { env } from '../config/env'
import { notificationService } from './notifications'
import { logger } from '../config/logger'

const log = logger.child({ module: 'billing' })

const snap = new midtransClient.Snap({
  isProduction: env.MIDTRANS_IS_PRODUCTION === 'TRUE',
  serverKey: env.MIDTRANS_SERVER_KEY,
  clientKey: env.MIDTRANS_CLIENT_KEY,
})

function verifyMidtransSignature(notification: Record<string, any>): boolean {
  const orderId = notification.order_id as string
  const statusCode = notification.status_code as string
  const grossAmount = notification.gross_amount as string
  const signatureKey = notification.signature_key as string

  if (!orderId || !statusCode || !grossAmount || !signatureKey) return false

  const input = `${orderId}${statusCode}${grossAmount}${env.MIDTRANS_SERVER_KEY}`
  const hash = crypto.createHash('sha512').update(input).digest('hex')

  return hash === signatureKey
}

export const billingService = {
  async getPlanLimits(planName: string) {
    const [p] = await db.select().from(plan)
      .where(eq(plan.name, planName))
      .limit(1)

    return {
      boardLimit: p?.boardLimit ?? 3,
      memberLimit: p?.memberLimit ?? 3,
      storageLimit: p?.storageLimit ?? 10485760,
    }
  },

  async getByWorkspace(workspaceId: number, userId: string) {
    const [ws] = await db.select().from(workspace)
      .where(eq(workspace.id, workspaceId))

    const [sub] = await db.select().from(subscription)
      .where(eq(subscription.workspaceId, workspaceId))
      .orderBy(sql`${subscription.createdAt} DESC`)
      .limit(1)

    const [freePlan] = await db.select().from(plan)
      .where(eq(plan.name, 'free'))
      .limit(1)

    const workspaceLimit = freePlan?.workspaceLimit ?? 3

    const [countResult] = await db.select({ count: sql<number>`count(*)::int` })
      .from(workspaceMember)
      .innerJoin(workspace, eq(workspaceMember.workspaceId, workspace.id))
      .where(and(
        eq(workspaceMember.userId, userId),
        isNull(workspace.deletedAt)
      ))

    return {
      plan: ws?.plan || 'free',
      subscription: sub || null,
      workspaceUsage: {
        count: countResult?.count || 0,
        limit: workspaceLimit,
      },
    }
  },

  async getUsage(workspaceId: number) {
    const boardCount = await db.select({ count: sql<number>`count(*)::int` })
      .from(board)
      .where(and(eq(board.workspaceId, workspaceId), isNull(board.deletedAt)))

    const memberCount = await db.select({ count: sql<number>`count(*)::int` })
      .from(workspaceMember)
      .where(eq(workspaceMember.workspaceId, workspaceId))

    const storageResult = await db.select({ total: sql<number>`coalesce(sum(${cardAttachment.size}), 0)::int` })
      .from(cardAttachment)
      .innerJoin(card, eq(card.id, cardAttachment.cardId))
      .innerJoin(list, eq(list.id, card.listId))
      .innerJoin(board, eq(board.id, list.boardId))
      .where(and(eq(board.workspaceId, workspaceId), isNull(cardAttachment.deletedAt)))

    return {
      boards: boardCount[0]?.count || 0,
      members: memberCount[0]?.count || 0,
      storageBytes: storageResult[0]?.total || 0,
    }
  },

  async createCheckout(workspaceId: number, userId: string, planName: string) {
    const planData = await db.select().from(plan)
      .where(and(eq(plan.name, planName), eq(plan.isActive, true)))
      .limit(1)

    if (!planData[0]) throw new AppError(404, 'Plan not found')
    if (planData[0].price === 0) throw new AppError(400, 'Cannot checkout for free plan')

    const ws = await db.select().from(workspace)
      .where(eq(workspace.id, workspaceId))
      .limit(1)

    if (!ws[0]) throw new AppError(404, 'Workspace not found')

    const orderId = `MP-${workspaceId}-${Date.now()}`

    const [sub] = await db.insert(subscription).values({
      publicId: generatePublicId(),
      workspaceId,
      plan: planName,
      status: 'pending',
      startDate: new Date(),
      midtransOrderId: orderId,
      paymentAmount: planData[0].price,
    }).returning()

    const parameter = {
      transaction_details: {
        order_id: orderId,
        gross_amount: planData[0].price,
      },
      credit_card: {
        secure: true,
      },
      callbacks: {
        finish: `${env.FRONTEND_URL}/${ws[0].slug}/settings/billing`,
      },
      item_details: [
        {
          id: `${planName}-plan`,
          price: planData[0].price,
          quantity: 1,
          name: `ManagPro ${planData[0].displayName} - Monthly`,
        },
      ],
      customer_details: {
        first_name: ws[0].name,
      },
    }

    const transaction = await snap.createTransaction(parameter)

    return {
      token: transaction.token,
      subscriptionId: sub.publicId,
    }
  },

  async handleNotification(notification: Record<string, any>) {
    if (!verifyMidtransSignature(notification)) {
      throw new AppError(401, 'Invalid Midtrans signature')
    }

    const orderId = notification.order_id as string
    const transactionStatus = notification.transaction_status as string
    const fraudStatus = notification.fraud_status as string | undefined

    const [sub] = await db.select().from(subscription)
      .where(eq(subscription.midtransOrderId, orderId))
      .limit(1)

    if (!sub) {
      throw new AppError(404, 'Subscription not found for order: ' + orderId)
    }

    // Guard: already active, skip duplicate webhook
    if (sub.status === 'active') {
      return { orderId, status: 'active' }
    }

    let newStatus = sub.status

    if (transactionStatus === 'capture' || transactionStatus === 'settlement') {
      if (fraudStatus === 'accept' || !fraudStatus) {
        newStatus = 'active'
        const now = new Date()
        const endDate = new Date(now)
        endDate.setMonth(endDate.getMonth() + 1)

        await db.update(subscription)
          .set({ status: 'active', updatedAt: now, endDate })
          .where(eq(subscription.id, sub.id))

        await db.update(workspace)
          .set({ plan: sub.plan, updatedAt: now })
          .where(eq(workspace.id, sub.workspaceId))

        // Create notification for successful payment
        try {
          const [ws] = await db.select().from(workspace)
            .where(eq(workspace.id, sub.workspaceId))
            .limit(1)

          if (ws && ws.createdBy) {
            const planDisplayName = sub.plan === 'team' ? 'Team' : sub.plan === 'pro' ? 'Pro' : sub.plan
            await notificationService.create({
              userId: ws.createdBy,
              workspaceId: sub.workspaceId,
              type: 'payment_success',
              title: `Payment successful! Your workspace is now on the ${planDisplayName} plan.`,
              entityType: 'subscription',
              entityId: sub.publicId,
              entityUrl: `/${ws.slug}/settings/billing`,
            })
          }
        } catch (error) {
          log.error({ err: error }, 'Failed to create payment success notification')
        }
      }
    } else if (transactionStatus === 'pending') {
      newStatus = 'pending'
      await db.update(subscription)
        .set({ status: 'pending', updatedAt: new Date() })
        .where(eq(subscription.id, sub.id))
    } else if (transactionStatus === 'deny' || transactionStatus === 'expire' || transactionStatus === 'cancel') {
      newStatus = 'failed'
      await db.update(subscription)
        .set({ status: 'failed', updatedAt: new Date() })
        .where(eq(subscription.id, sub.id))

      // Create notification for failed payment
      try {
        const [ws] = await db.select().from(workspace)
          .where(eq(workspace.id, sub.workspaceId))
          .limit(1)

        if (ws && ws.createdBy) {
          const planDisplayName = sub.plan === 'team' ? 'Team' : sub.plan === 'pro' ? 'Pro' : sub.plan
          await notificationService.create({
            userId: ws.createdBy,
            workspaceId: sub.workspaceId,
            type: 'payment_failed',
            title: `Payment failed for ${planDisplayName} plan upgrade. Please try again.`,
            entityType: 'subscription',
            entityId: sub.publicId,
            entityUrl: `/${ws.slug}/settings/billing`,
          })
        }
      } catch (error) {
        log.error({ err: error }, 'Failed to create payment failure notification')
      }
    }

    return { orderId, status: newStatus }
  },
}
