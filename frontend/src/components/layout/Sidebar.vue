<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth.store'

const route = useRoute()
const authStore = useAuthStore()

const navItems = [
  { label: 'Dashboard',        path: '/dashboard',           icon: 'dashboard' },
  { label: 'Production',       path: '/dashboard/production', icon: 'factory' },
  { label: 'Inv. Dashboard',   path: '/dashboard/inventory',  icon: 'bar_chart' },
  { label: 'Products',         path: '/products',             icon: 'category' },
  { label: 'Vendors',          path: '/vendors',              icon: 'local_shipping' },
  { label: 'Warehouses',       path: '/warehouses',           icon: 'warehouse' },
  { label: 'Machines',         path: '/machines',             icon: 'precision_manufacturing' },
  { label: 'Inventory',        path: '/inventory',            icon: 'inventory_2' },
  { label: 'GRN',              path: '/grn',                  icon: 'receipt_long' },
  { label: 'Prod. Plans',      path: '/production',           icon: 'calendar_month' },
  { label: 'Floor View',       path: '/production/floor',     icon: 'view_timeline' },
  { label: 'OEE Dashboard',    path: '/production/oee',       icon: 'analytics' },
  { label: 'Workers',          path: '/workers',              icon: 'groups' },
  { label: 'Attendance',       path: '/attendance',           icon: 'event_available' },
]

const userName = computed(() =>
  [authStore.user?.first_name, authStore.user?.last_name].filter(Boolean).join(' ') || 'User'
)
const userRole = computed(() => authStore.user?.roles?.[0]?.display_name ?? 'Member')

function isActive(path: string) {
  if (path === '/dashboard') return route.path === '/dashboard'
  if (path === '/production') return route.path === '/production'
  return route.path === path || route.path.startsWith(path + '/')
}
</script>

<template>
  <aside class="fixed left-0 top-0 h-full w-sidebar-width bg-on-background flex flex-col py-6 z-50 overflow-hidden">
    <!-- Brand -->
    <div class="px-6 mb-8">
      <h1 class="text-headline-sm text-surface-container-lowest font-bold tracking-tight">ModernERP</h1>
      <p class="text-[10px] uppercase tracking-[0.2em] text-outline opacity-60 mt-0.5">Enterprise Suite</p>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto custom-scrollbar space-y-0.5 px-0">
      <router-link
        v-for="item in navItems"
        :key="item.path"
        :to="item.path"
        class="flex items-center px-6 py-2.5 text-body-md transition-colors duration-150 no-underline"
        :class="isActive(item.path)
          ? 'border-l-4 border-primary bg-on-secondary-fixed-variant text-primary-fixed'
          : 'border-l-4 border-transparent text-outline-variant hover:text-surface-container-lowest hover:bg-on-secondary-fixed-variant/50'"
      >
        <span class="material-symbols-outlined mr-3 text-[18px]">{{ item.icon }}</span>
        <span>{{ item.label }}</span>
      </router-link>
    </nav>

    <!-- Bottom: User Info -->
    <div class="px-6 pt-4 mt-2 border-t border-white/10">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-primary-container flex items-center justify-center text-primary-fixed text-xs font-bold flex-shrink-0">
          {{ userName.charAt(0).toUpperCase() }}
        </div>
        <div class="min-w-0">
          <p class="text-[13px] font-semibold text-surface-container-lowest truncate">{{ userName }}</p>
          <p class="text-[11px] text-outline truncate">{{ userRole }}</p>
        </div>
      </div>
    </div>
  </aside>
</template>
