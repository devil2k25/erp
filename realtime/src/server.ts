import express from 'express'
import { createServer } from 'http'
import { Server } from 'socket.io'
import { createClient } from 'redis'
import cors from 'cors'
import dotenv from 'dotenv'
import { authMiddleware } from './middleware/authMiddleware'

dotenv.config()

export async function startServer() {
  const app = express()
  const httpServer = createServer(app)

  app.use(cors({ origin: process.env.FRONTEND_URL ?? '*', credentials: true }))
  app.get('/health', (_, res) => res.json({ status: 'ok', timestamp: new Date() }))

  const io = new Server(httpServer, {
    cors: { origin: process.env.FRONTEND_URL ?? '*', methods: ['GET', 'POST'], credentials: true },
    transports: ['websocket', 'polling'],
  })

  // Redis subscriber for Laravel broadcast events
  const subscriber = createClient({ url: process.env.REDIS_URL ?? 'redis://localhost:6379' })
  await subscriber.connect()
  console.log('[Redis] Connected')

  // Subscribe to all tenant broadcast channels
  await subscriber.pSubscribe('laravel_database_*', (message, channel) => {
    try {
      const event = JSON.parse(message)
      const channelParts = channel.split('.')

      if (channelParts.length >= 2) {
        const tenantSlug = channelParts[1]
        const module = channelParts[2] ?? 'general'
        const room = `tenant_${tenantSlug}_${module}`

        io.to(room).emit(event.event ?? 'update', event.data ?? event)
        console.log(`[Broadcast] ${channel} → ${room}:`, event.event)
      }
    } catch (err) {
      console.error('[Redis] Failed to parse message:', err)
    }
  })

  // Auth middleware
  io.use(authMiddleware)

  // Connection handler
  io.on('connection', (socket) => {
    const { orgSlug } = (socket as any).data?.tenant ?? {}
    if (!orgSlug) { socket.disconnect(); return }

    console.log(`[Socket] Client connected: ${socket.id} (org: ${orgSlug})`)

    // Join tenant rooms
    socket.join(`tenant_${orgSlug}_production`)
    socket.join(`tenant_${orgSlug}_inventory`)
    socket.join(`tenant_${orgSlug}_alerts`)

    socket.on('disconnect', () => {
      console.log(`[Socket] Client disconnected: ${socket.id}`)
    })

    // Emit connection confirmation
    socket.emit('connected', { orgSlug, socketId: socket.id })
  })

  const port = parseInt(process.env.PORT ?? '3001')
  httpServer.listen(port, () => {
    console.log(`[Realtime] Server running on port ${port}`)
  })

  return { app, io, httpServer }
}
