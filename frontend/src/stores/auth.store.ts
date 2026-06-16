import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { User } from '@/types/auth.types'
import { AuthService } from '@/services/auth.service'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('erp_token'))
  const orgSlug = ref<string | null>(localStorage.getItem('erp_org_slug'))

  const isAuthenticated = computed(() => !!token.value)

  function hasRole(role: string): boolean {
    return user.value?.roles.some((r) => r.name === role) ?? false
  }

  function hasAnyRole(roles: string[]): boolean {
    return roles.some((r) => hasRole(r))
  }

  async function login(email: string, password: string) {
    const response: any = await AuthService.login({ email, password })
    const data = response.data

    token.value = data.token
    user.value = data.user
    // org slug comes from the login response — no need to enter it manually
    orgSlug.value = data.organization?.slug ?? null

    localStorage.setItem('erp_token', data.token)
    if (data.organization?.slug) {
      localStorage.setItem('erp_org_slug', data.organization.slug)
    }

    return data
  }

  async function fetchMe() {
    const response: any = await AuthService.me()
    user.value = response.data
  }

  async function logout() {
    try { await AuthService.logout() } catch {}
    token.value = null
    user.value = null
    orgSlug.value = null
    localStorage.removeItem('erp_token')
    localStorage.removeItem('erp_org_slug')
  }

  return { user, token, orgSlug, isAuthenticated, hasRole, hasAnyRole, login, fetchMe, logout }
})
