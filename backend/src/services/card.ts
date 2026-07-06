import { eq, and, isNull, max, asc } from 'drizzle-orm'
import { db } from '../db'
import { card, cardLabel, cardMember, label, list, workspaceMember, user, cardChecklist, cardChecklistItem, cardComment, cardAttachment } from '../db/schema'
import { generatePublicId } from '../utils/publicId'
import { AppError } from '../middleware/error'

async function resolveListId(listPublicId: string): Promise<number> {
  const [listData] = await db.select().from(list)
    .where(eq(list.publicId, listPublicId))

  if (!listData) {
    throw new AppError(404, 'List not found')
  }

  return listData.id
}

async function resolveLabelId(labelPublicId: string): Promise<number> {
  const [labelData] = await db.select().from(label)
    .where(eq(label.publicId, labelPublicId))

  if (!labelData) {
    throw new AppError(404, 'Label not found')
  }

  return labelData.id
}

async function resolveMemberId(memberPublicId: string): Promise<number> {
  const [memberData] = await db.select().from(workspaceMember)
    .where(eq(workspaceMember.publicId, memberPublicId))

  if (!memberData) {
    throw new AppError(404, 'Member not found')
  }

  return memberData.id
}

export const cardService = {
  async getByList(listPublicId: string) {
    const listId = await resolveListId(listPublicId)

    const cards = await db.select().from(card)
      .where(and(eq(card.listId, listId), isNull(card.deletedAt)))
      .orderBy(asc(card.index))

    const cardsWithRelations = await Promise.all(
      cards.map(async (c) => {
        const labels = await db.select({
          cardId: cardLabel.cardId,
          labelId: cardLabel.labelId,
          id: label.id,
          publicId: label.publicId,
          name: label.name,
          colourCode: label.colourCode,
        })
          .from(cardLabel)
          .innerJoin(label, eq(cardLabel.labelId, label.id))
          .where(eq(cardLabel.cardId, c.id))

        const members = await db.select({
          cardId: cardMember.cardId,
          workspaceMemberId: cardMember.workspaceMemberId,
          id: workspaceMember.id,
          publicId: workspaceMember.publicId,
          userId: workspaceMember.userId,
          role: workspaceMember.role,
          userName: user.name,
          userImage: user.image,
        })
          .from(cardMember)
          .innerJoin(workspaceMember, eq(cardMember.workspaceMemberId, workspaceMember.id))
          .leftJoin(user, eq(workspaceMember.userId, user.id))
          .where(eq(cardMember.cardId, c.id))

        return { ...c, labels, members }
      })
    )

    return cardsWithRelations
  },

  async getById(publicId: string) {
    const [cardData] = await db.select().from(card)
      .where(and(eq(card.publicId, publicId), isNull(card.deletedAt)))

    if (!cardData) {
      throw new AppError(404, 'Card not found')
    }

    const labels = await db.select({
      cardId: cardLabel.cardId,
      labelId: cardLabel.labelId,
      id: label.id,
      publicId: label.publicId,
      name: label.name,
      colourCode: label.colourCode,
    })
      .from(cardLabel)
      .innerJoin(label, eq(cardLabel.labelId, label.id))
      .where(eq(cardLabel.cardId, cardData.id))

    const members = await db.select({
      cardId: cardMember.cardId,
      workspaceMemberId: cardMember.workspaceMemberId,
      id: workspaceMember.id,
      publicId: workspaceMember.publicId,
      userId: workspaceMember.userId,
      role: workspaceMember.role,
      userName: user.name,
      userImage: user.image,
    })
      .from(cardMember)
      .innerJoin(workspaceMember, eq(cardMember.workspaceMemberId, workspaceMember.id))
      .leftJoin(user, eq(workspaceMember.userId, user.id))
      .where(eq(cardMember.cardId, cardData.id))

    const checklists = await db.select().from(cardChecklist)
      .where(and(eq(cardChecklist.cardId, cardData.id), isNull(cardChecklist.deletedAt)))
      .orderBy(asc(cardChecklist.index))

    const checklistsItems = await Promise.all(
      checklists.map(async (cl) => {
        const items = await db.select().from(cardChecklistItem)
          .where(and(eq(cardChecklistItem.checklistId, cl.id), isNull(cardChecklistItem.deletedAt)))
          .orderBy(asc(cardChecklistItem.index))
        return { ...cl, items }
      })
    )

    const comments = await db.select({
      id: cardComment.id,
      publicId: cardComment.publicId,
      comment: cardComment.comment,
      createdAt: cardComment.createdAt,
      userId: cardComment.createdBy,
      user: {
        id: user.id,
        name: user.name,
        email: user.email,
        image: user.image
      }
    })
      .from(cardComment)
      .leftJoin(user, eq(cardComment.createdBy, user.id))
      .where(and(eq(cardComment.cardId, cardData.id), isNull(cardComment.deletedAt)))
      .orderBy(asc(cardComment.createdAt))

    const attachments = await db.select().from(cardAttachment)
      .where(and(eq(cardAttachment.cardId, cardData.id), isNull(cardAttachment.deletedAt)))

    const [listData] = await db.select().from(list)
      .where(and(eq(list.id, cardData.listId), isNull(list.deletedAt)))

    return { ...cardData, list: listData, labels, members, checklists: checklistsItems, comments, attachments }
  },

  async create(data: {
    title: string
    description?: string
    listPublicId: string
    labelPublicIds?: string[]
    memberPublicIds?: string[]
    position?: 'start' | 'end'
    dueDate?: string | null
  }, userId: string) {
    const listId = await resolveListId(data.listPublicId)

    let nextIndex: number
    if (data.position === 'start') {
      const result = await db.select({ minIndex: card.index })
        .from(card)
        .where(eq(card.listId, listId))
      nextIndex = (result[0]?.minIndex ?? 1) - 1
    } else {
      const result = await db.select({ maxIndex: max(card.index) })
        .from(card)
        .where(eq(card.listId, listId))
      nextIndex = (result[0]?.maxIndex ?? -1) + 1
    }

    const numberResult = await db.select({ maxNumber: max(card.cardNumber) })
      .from(card)
    const nextNumber = (numberResult[0]?.maxNumber ?? 0) + 1
    const publicId = generatePublicId()

    const [newCard] = await db.insert(card).values({
      publicId,
      title: data.title,
      description: data.description || '',
      cardNumber: nextNumber,
      listId,
      index: nextIndex,
      createdBy: userId,
      dueDate: data.dueDate ? new Date(data.dueDate) : null
    }).returning()

    if (data.labelPublicIds?.length) {
      for (const labelPubId of data.labelPublicIds) {
        const labelId = await resolveLabelId(labelPubId)
        await db.insert(cardLabel).values({ cardId: newCard.id, labelId })
      }
    }

    if (data.memberPublicIds?.length) {
      for (const memberPubId of data.memberPublicIds) {
        const memberId = await resolveMemberId(memberPubId)
        await db.insert(cardMember).values({ cardId: newCard.id, workspaceMemberId: memberId })
      }
    }

    return newCard
  },

  async update(publicId: string, data: { title?: string; description?: string; dueDate?: Date | null }) {
    const cardData = await this.getById(publicId)

    const [updated] = await db.update(card)
      .set({ ...data, updatedAt: new Date() })
      .where(eq(card.id, cardData.id))
      .returning()

    return updated
  },

  async move(publicId: string, data: { listPublicId: string; index: number }) {
    const cardData = await this.getById(publicId)
    const listId = await resolveListId(data.listPublicId)

    const [updated] = await db.update(card)
      .set({
        listId,
        index: data.index,
        updatedAt: new Date()
      })
      .where(eq(card.id, cardData.id))
      .returning()

    return updated
  },

  async toggleLabel(cardPublicId: string, labelPublicId: string) {
    const cardData = await this.getById(cardPublicId)
    const labelId = await resolveLabelId(labelPublicId)

    const [existing] = await db.select().from(cardLabel)
      .where(and(eq(cardLabel.cardId, cardData.id), eq(cardLabel.labelId, labelId)))

    if (existing) {
      await db.delete(cardLabel)
        .where(and(eq(cardLabel.cardId, cardData.id), eq(cardLabel.labelId, labelId)))
      return { newLabel: false }
    }

    await db.insert(cardLabel).values({ cardId: cardData.id, labelId })
    return { newLabel: true }
  },

  async toggleMember(cardPublicId: string, memberPublicId: string) {
    const cardData = await this.getById(cardPublicId)
    const memberId = await resolveMemberId(memberPublicId)

    const [existing] = await db.select().from(cardMember)
      .where(and(eq(cardMember.cardId, cardData.id), eq(cardMember.workspaceMemberId, memberId)))

    if (existing) {
      await db.delete(cardMember)
        .where(and(eq(cardMember.cardId, cardData.id), eq(cardMember.workspaceMemberId, memberId)))
      return { newMember: false }
    }

    await db.insert(cardMember).values({ cardId: cardData.id, workspaceMemberId: memberId })
    return { newMember: true }
  },

  async duplicate(publicId: string, userId: string) {
    const cardData = await this.getById(publicId)

    const numberResult = await db.select({ maxNumber: max(card.cardNumber) })
      .from(card)
    const nextNumber = (numberResult[0]?.maxNumber ?? 0) + 1
    const newPublicId = generatePublicId()

    const [newCard] = await db.insert(card).values({
      publicId: newPublicId,
      title: cardData.title + ' (copy)',
      description: cardData.description,
      cardNumber: nextNumber,
      listId: cardData.listId,
      index: (await db.select({ maxIndex: max(card.index) })
        .from(card)
        .where(eq(card.listId, cardData.listId)))[0]?.maxIndex ?? 0,
      createdBy: userId,
      dueDate: cardData.dueDate,
    }).returning()

    for (const label of cardData.labels) {
      await db.insert(cardLabel).values({ cardId: newCard.id, labelId: label.id })
    }

    for (const member of cardData.members) {
      await db.insert(cardMember).values({ cardId: newCard.id, workspaceMemberId: member.id })
    }

    for (const cl of cardData.checklists) {
      await db.insert(cardChecklist).values({
        publicId: generatePublicId(),
        name: cl.name,
        cardId: newCard.id,
        index: cl.index,
        createdBy: userId
      })
    }

    return { ...newCard, labels: cardData.labels, members: cardData.members }
  },

  async delete(publicId: string) {
    const cardData = await this.getById(publicId)

    await db.update(card)
      .set({ deletedAt: new Date() })
      .where(eq(card.id, cardData.id))
  }
}
