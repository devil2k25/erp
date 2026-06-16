import adminApi from './admin-api'

export const AdminService = {
  async login(credentials: { email: string; password: string }) {
    return adminApi.post('/auth/login', credentials)
  },
  async me() {
    return adminApi.get('/auth/me')
  },
  async logout() {
    return adminApi.post('/auth/logout')
  },

  async getOrganizations(params?: Record<string, unknown>) {
    return adminApi.get('/organizations', { params })
  },
  async createOrganization(data: Record<string, unknown>) {
    return adminApi.post('/organizations', data)
  },
  async getOrganization(id: string) {
    return adminApi.get(`/organizations/${id}`)
  },
  async updateOrgStatus(id: string, status: string) {
    return adminApi.patch(`/organizations/${id}/status`, { status })
  },
}
