import type { Router } from 'vue-router'
import { useAuthStore } from '@/stores/auth.store'
import { useAdminStore } from '@/stores/admin.store'

export function setupGuards(router: Router) {
  router.beforeEach(async (to) => {
    const authStore = useAuthStore()
    const adminStore = useAdminStore()

    // Admin routes
    if (to.meta.requiresAdmin) {
      if (!adminStore.isAuthenticated) return { name: 'admin.login' }
      if (!adminStore.admin) {
        try {
          await adminStore.fetchMe()
        } catch {
          await adminStore.logout()
          return { name: 'admin.login' }
        }
      }
      return
    }

    if (to.meta.requiresAdminGuest && adminStore.isAuthenticated) {
      return { name: 'admin.organizations' }
    }

    // Tenant user routes
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
        await authStore.logout()
        return { name: 'login' }
      }
    }

    if (to.meta.roles && !authStore.hasAnyRole(to.meta.roles as string[])) {
      return { path: '/dashboard' }
    }
  })
}
