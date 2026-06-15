<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth.store'

const route = useRoute()
const authStore = useAuthStore()

const navItems = [
  { label: 'Overview', path: '/dashboard', icon: '⚡' },
  { label: 'Production', path: '/dashboard/production', icon: '🏭' },
  { label: 'Inventory', path: '/dashboard/inventory', icon: '📦' },
  { label: 'Products', path: '/products', icon: '🔧' },
  { label: 'Warehouses', path: '/warehouses', icon: '🏢' },
  { label: 'Machines', path: '/machines', icon: '⚙️' },
  { label: 'GRN', path: '/grn', icon: '📋' },
  { label: 'Inventory', path: '/inventory', icon: '📊' },
  { label: 'Production Plans', path: '/production', icon: '📅' },
  { label: 'Floor View', path: '/production/floor', icon: '🔴' },
  { label: 'OEE Dashboard', path: '/production/oee', icon: '📈' },
  { label: 'Workers', path: '/workers', icon: '👷' },
  { label: 'Attendance', path: '/attendance', icon: '✅' },
]

const isActive = (path: string) => route.path === path || route.path.startsWith(path + '/')
</script>

<template>
  <aside class="w-60 bg-slate-900 border-r border-slate-800 flex flex-col flex-shrink-0">
    <div class="p-4 border-b border-slate-800">
      <h1 class="text-xl font-bold text-blue-400">Factory ERP</h1>
      <p class="text-xs text-slate-500 mt-0.5">{{ authStore.orgSlug }}</p>
    </div>
    <nav class="flex-1 overflow-y-auto py-4">
      <ul class="space-y-0.5 px-2">
        <li v-for="item in navItems" :key="item.path">
          <router-link
            :to="item.path"
            class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors"
            :class="isActive(item.path)
              ? 'bg-blue-600 text-white'
              : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100'"
          >
            <span>{{ item.icon }}</span>
            <span>{{ item.label }}</span>
          </router-link>
        </li>
      </ul>
    </nav>
    <div class="p-4 border-t border-slate-800">
      <p class="text-xs text-slate-400">{{ authStore.user?.first_name }} {{ authStore.user?.last_name }}</p>
      <p class="text-xs text-slate-500">{{ authStore.user?.roles?.[0]?.display_name }}</p>
    </div>
  </aside>
</template>
