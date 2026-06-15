import type { Socket } from 'socket.io'
import axios from 'axios'

export async function authMiddleware(socket: Socket, next: (err?: Error) => void) {
  const token = socket.handshake.auth?.token
  const orgSlug = socket.handshake.auth?.orgSlug

  if (!token || !orgSlug) {
    return next(new Error('Authentication required'))
  }

  try {
    const response = await axios.get(`${process.env.LARAVEL_API_URL ?? 'http://localhost:8000'}/api/v1/auth/me`, {
      headers: {
        Authorization: `Bearer ${token}`,
        'X-Organization-Slug': orgSlug,
        Accept: 'application/json',
      },
      timeout: 5000,
    })

    if (response.data?.success && response.data?.data) {
      ;(socket as any).data = {
        user: response.data.data,
        tenant: { orgSlug },
      }
      return next()
    }

    return next(new Error('Invalid token'))
  } catch (err: any) {
    console.error('[Auth] Token validation failed:', err.message)
    return next(new Error('Authentication failed'))
  }
}
