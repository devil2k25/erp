import type { Router } from 'vue-router'
import { useAuthStore } from '@/stores/auth.store'

export function setupGuards(router: Router) {
  router.beforeEach(async (to) => {
    const authStore = useAuthStore()

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
      return { name: 'login', query: { redirect: to.fullPath } }
    }

    if (to.meta.requiresGuest && authStore.isAuthenticated) {
      return { path: '/' }
    }

    if (authStore.isAuthenticated && !authStore.user) {
      try {
        await authStore.fetchMe()
      } catch {
        authStore.logout()
        return { name: 'login' }
      }
    }

    if (to.meta.roles && !authStore.hasAnyRole(to.meta.roles as string[])) {
      return { path: '/dashboard' }
    }
  })
}
