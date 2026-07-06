// Vercel serverless entry point
// Import the Express app with full error handling
let app: any

try {
  console.log('Loading backend app...')
  const mod = await import('../backend/src/index')
  app = mod.default
  console.log('Backend app loaded successfully')
} catch (error: any) {
  console.error('CRITICAL: Failed to load backend app:', error?.message, error?.stack)
  app = (req: any, res: any) => {
    res.status(500).json({
      error: 'Application failed to start',
      message: error?.message || String(error),
    })
  }
}

export default app
