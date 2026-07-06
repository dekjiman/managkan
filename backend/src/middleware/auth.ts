import { Request, Response, NextFunction } from 'express'
import { auth } from '../auth'

export interface AuthRequest extends Request {
  user?: {
    id: string
    name: string
    email: string
    image?: string | null
  }
  session?: {
    token: string
    expiresAt: Date
  }
}

export const requireAuth = async (
  req: AuthRequest,
  res: Response,
  next: NextFunction
) => {
  try {
    const session = await auth.api.getSession({
      headers: req.headers
    })

    if (!session) {
      return res.status(401).json({ message: 'Unauthorized' })
    }

    req.user = session.user as any
    req.session = session.session as any
    next()
  } catch (error) {
    return res.status(401).json({ message: 'Unauthorized' })
  }
}
