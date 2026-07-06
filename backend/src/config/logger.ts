import pino from 'pino'

const level = process.env.LOG_LEVEL || (process.env.NODE_ENV === 'production' ? 'info' : 'debug')

export const logger = pino({
  level,
  ...(process.env.NODE_ENV === 'production'
    ? { formatters: { level: (label) => ({ level: label }) } }
    : { transport: { target: 'pino-pretty', options: { colorize: true } } }
  ),
})

export const childLogger = (context: Record<string, unknown>) => logger.child(context)
