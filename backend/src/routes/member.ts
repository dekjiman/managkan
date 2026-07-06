import { Router } from 'express'
import { z } from 'zod'
import { eq } from 'drizzle-orm'
import { requireAuth, AuthRequest } from '../middleware/auth'
import { memberService } from '../services/member'
import { userService } from '../services/user'
import { billingService } from '../services/billing'
import { wrap } from '../utils/wrap'
import { sendInviteEmail, sendMemberAddedEmail } from '../email'
import { auth } from '../auth'
import { db } from '../db'
import { user, workspace } from '../db/schema'
import { env } from '../config/env'

const router = Router()

const addSchema = z.object({
  workspaceId: z.coerce.number(),
  email: z.string().email(),
  role: z.enum(['admin', 'member', 'guest']).optional()
})

const updateSchema = z.object({
  role: z.enum(['admin', 'member', 'guest'])
})

const inviteSignupSchema = z.object({
  code: z.string().min(1),
  name: z.string().min(1),
  password: z.string().min(6)
})

router.get('/', requireAuth, wrap(async (req: AuthRequest, res) => {
  const { workspaceId } = req.query
  if (!workspaceId) {
    return res.status(400).json({ message: 'workspaceId is required' })
  }
  const members = await memberService.getByWorkspace(Number(workspaceId))
  res.json({ data: members })
}))

router.get('/invite/:code', wrap(async (req, res) => {
  const member = await memberService.getByInviteCode(req.params.code)
  res.json({
    data: {
      email: member.email,
      role: member.role,
      workspaceName: member.workspaceName
    }
  })
}))

router.post('/invite-signup', wrap(async (req, res) => {
  const data = inviteSignupSchema.parse(req.body)

  const member = await memberService.getByInviteCode(data.code)

  const existingUser = await userService.getByEmail(member.email)
  if (existingUser) {
    return res.status(400).json({ message: 'Email already registered. Please sign in.' })
  }

  const result = await (auth.api as any).signUpEmail({
    body: {
      email: member.email,
      password: data.password,
      name: data.name,
    }
  })

  if (!result?.user) {
    return res.status(500).json({ message: 'Failed to create account' })
  }

  await memberService.activateByInviteCode(data.code, result.user.id)

  await db.update(user)
    .set({ emailVerified: true, updatedAt: new Date() })
    .where(eq(user.id, result.user.id))

  const signInRequest = new Request(`${env.BETTER_AUTH_URL}/api/auth/sign-in/email`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Origin': env.FRONTEND_URL,
    },
    body: JSON.stringify({ email: member.email, password: data.password }),
  })

  const signInResponse = await auth.handler(signInRequest)

  const setCookie = signInResponse.headers.get('set-cookie')
  if (setCookie) {
    const cookies = setCookie.split(', ')
    res.setHeader('Set-Cookie', cookies)
  }

  const sessionData = await signInResponse.json()
  res.status(201).json(sessionData)
}))

router.post('/', requireAuth, wrap(async (req: AuthRequest, res) => {
  const data = addSchema.parse(req.body)

  // Check member limit
  const usage = await billingService.getUsage(data.workspaceId)
  const [ws] = await db.select().from(workspace).where(eq(workspace.id, data.workspaceId))
  const planData = await billingService.getPlanLimits(ws?.plan || 'free')
  if (planData.memberLimit !== -1 && usage.members >= planData.memberLimit) {
    return res.status(403).json({ message: `Member limit reached (${planData.memberLimit}). Upgrade your plan for more members.` })
  }

  const existingUser = await userService.getByEmail(data.email)

  const member = await memberService.add({
    workspaceId: data.workspaceId,
    userId: existingUser?.id || null,
    role: data.role,
    email: data.email,
    createdBy: req.user!.id
  })

  if (existingUser) {
    const [ws] = await db.select().from(workspace).where(eq(workspace.id, data.workspaceId))
    const workspaceName = ws?.name || 'the workspace'
    await sendMemberAddedEmail(data.email, req.user!.name || 'Someone', workspaceName)
  } else {
    await sendInviteEmail(data.email, req.user!.name || 'Someone', member.inviteCode || '')
  }

  res.status(201).json({ data: member, message: 'Member invited' })
}))

router.patch('/:publicId', requireAuth, wrap(async (req: AuthRequest, res) => {
  const data = updateSchema.parse(req.body)
  const member = await memberService.updateRole(req.params.publicId, data.role)
  res.json({ data: member, message: 'Member updated' })
}))

router.delete('/:publicId', requireAuth, wrap(async (req: AuthRequest, res) => {
  await memberService.remove(req.params.publicId)
  res.json({ message: 'Member removed' })
}))

router.post('/:publicId/resend-invite', requireAuth, wrap(async (req: AuthRequest, res) => {
  const member = await memberService.getByIdWithWorkspace(req.params.publicId)

  if (member.status !== 'invited') {
    return res.status(400).json({ message: 'This member has already registered' })
  }

  let inviteCode = member.inviteCode
  if (!inviteCode) {
    const updated = await memberService.regenerateInviteCode(member.publicId)
    inviteCode = updated.inviteCode!
  }

  await sendInviteEmail(member.email, req.user!.name || 'Someone', inviteCode)
  res.json({ message: 'Invite resent' })
}))

export default router
