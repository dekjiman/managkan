let app: any

try {
  const mod = await import('../src/index')
  app = mod.default
} catch (error: any) {
  console.error('Failed to load app:', error)
  app = (req: any, res: any) => {
    res.status(500).json({
      error: 'Failed to load application',
      message: error?.message || String(error),
    })
  }
}

export default app
