import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { AdminService } from '@/services/admin.service'

export const useAdminStore = defineStore('admin', () => {
  const admin = ref<{ id: string; name: string; email: string } | null>(null)
  const token = ref<string | null>(localStorage.getItem('erp_admin_token'))

  const isAuthenticated = computed(() => !!token.value)

  async function login(email: string, password: string) {
    const response: any = await AdminService.login({ email, password })
    token.value = response.data.token
    admin.value = response.data.admin
    localStorage.setItem('erp_admin_token', response.data.token)
    return response.data
  }

  async function fetchMe() {
    const response: any = await AdminService.me()
    admin.value = response.data
  }

  async function logout() {
    try { await AdminService.logout() } catch {}
    token.value = null
    admin.value = null
    localStorage.removeItem('erp_admin_token')
  }

  return { admin, token, isAuthenticated, login, fetchMe, logout }
})
