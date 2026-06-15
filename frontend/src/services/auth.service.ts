import api from './api'
import type { LoginCredentials } from '@/types/auth.types'

export const AuthService = {
  async login(credentials: LoginCredentials) {
    return api.post('/auth/login', credentials)
  },

  async logout() {
    return api.post('/auth/logout')
  },

  async me() {
    return api.get('/auth/me')
  },

  async register(data: Record<string, unknown>) {
    return api.post('/auth/register', data)
  },
}
