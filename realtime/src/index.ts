import { startServer } from './server'

startServer().catch((err) => {
  console.error('Failed to start realtime server:', err)
  process.exit(1)
})
