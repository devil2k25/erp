import { io, type Socket } from 'socket.io-client'

let socket: Socket | null = null

export function connectSocket(orgSlug: string, token: string): Socket {
  if (socket?.connected) return socket

  socket = io(import.meta.env.VITE_SOCKET_URL ?? 'http://localhost:3001', {
    auth: { token, orgSlug },
    transports: ['websocket', 'polling'],
    reconnection: true,
    reconnectionDelay: 1000,
    reconnectionAttempts: 5,
  })

  socket.on('connect', () => console.log('[Socket] Connected'))
  socket.on('disconnect', (reason) => console.log('[Socket] Disconnected:', reason))
  socket.on('connect_error', (err) => console.error('[Socket] Error:', err.message))

  return socket
}

export function disconnectSocket(): void {
  socket?.disconnect()
  socket = null
}

export function getSocket(): Socket | null {
  return socket
}
