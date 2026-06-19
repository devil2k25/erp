<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth.store'
import { useRealtimeStore } from '@/stores/realtime.store'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const realtimeStore = useRealtimeStore()
const router = useRouter()
const searchQuery = ref('')

async function logout() {
  await authStore.logout()
  router.push('/login')
}

const userName = () =>
  [authStore.user?.first_name, authStore.user?.last_name].filter(Boolean).join(' ') || 'User'
const userRole = () => authStore.user?.roles?.[0]?.display_name ?? 'Member'
const userInitial = () => (authStore.user?.first_name?.[0] ?? 'U').toUpperCase()
</script>

<template>
  <header class="fixed top-0 right-0 h-16 glass-header border-b border-outline-variant flex items-center justify-between px-gutter z-40"
    :style="{ left: '260px', width: 'calc(100% - 260px)' }">

    <!-- Left: Search -->
    <div class="flex items-center gap-4 w-full max-w-xs">
      <div class="relative w-full">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search… (Ctrl K)"
          class="w-full bg-surface-container-low border border-outline-variant rounded-lg pl-9 pr-4 py-1.5 text-body-sm text-on-surface placeholder-outline focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40"
        />
      </div>
    </div>

    <!-- Right: Actions + User -->
    <div class="flex items-center gap-5">
      <!-- Realtime status -->
      <div class="flex items-center gap-1.5">
        <span class="inline-block w-1.5 h-1.5 rounded-full"
          :class="realtimeStore.connected ? 'bg-tertiary' : 'bg-outline-variant'" />
        <span class="text-[11px] text-outline">{{ realtimeStore.connected ? 'Live' : 'Offline' }}</span>
      </div>

      <!-- Icon actions -->
      <div class="flex items-center gap-2">
        <button class="w-8 h-8 flex items-center justify-center text-secondary hover:text-primary rounded-lg hover:bg-surface-container transition-colors">
          <span class="material-symbols-outlined text-[20px]">notifications</span>
        </button>
        <button class="w-8 h-8 flex items-center justify-center text-secondary hover:text-primary rounded-lg hover:bg-surface-container transition-colors">
          <span class="material-symbols-outlined text-[20px]">help</span>
        </button>
      </div>

      <div class="w-px h-6 bg-outline-variant" />

      <!-- User -->
      <div class="flex items-center gap-2.5">
        <div class="text-right hidden sm:block">
          <p class="text-[13px] font-semibold text-on-surface leading-tight">{{ userName() }}</p>
          <p class="text-[11px] text-outline leading-tight">{{ userRole() }}</p>
        </div>
        <button
          @click="logout"
          class="w-9 h-9 rounded-full bg-primary-container text-on-background flex items-center justify-center text-sm font-bold hover:opacity-90 transition-opacity border border-outline-variant"
          :title="`Logout (${userName()})`"
        >
          {{ userInitial() }}
        </button>
      </div>
    </div>
  </header>
</template>
