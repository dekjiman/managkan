import { Request, Response, NextFunction } from 'express'
import { z } from 'zod'
import { logger } from '../config/logger'

const log = logger.child({ module: 'error-handler' })

export class AppError extends Error {
  constructor(
    public statusCode: number,
    message: string,
    public errors?: Record<string, string[]>
  ) {
    super(message)
    this.name = 'AppError'
  }
}

export const errorHandler = (
  err: Error,
  req: Request,
  res: Response,
  next: NextFunction
) => {
  if (err instanceof AppError) {
    return res.status(err.statusCode).json({
      message: err.message,
      errors: err.errors
    })
  }

  if (err instanceof z.ZodError) {
    return res.status(400).json({
      message: 'Validation error',
      errors: err.flatten().fieldErrors
    })
  }

  log.error({ err }, 'Unhandled error')
  return res.status(500).json({ message: 'Internal server error' })
}
