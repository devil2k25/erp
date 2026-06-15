<script setup lang="ts">
import { useAuthStore } from '@/stores/auth.store'
import { useRealtimeStore } from '@/stores/realtime.store'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const realtimeStore = useRealtimeStore()
const router = useRouter()

async function logout() {
  await authStore.logout()
  router.push('/login')
}
</script>

<template>
  <header class="h-14 bg-slate-900 border-b border-slate-800 flex items-center justify-between px-6">
    <div class="flex items-center gap-4">
      <span class="text-sm text-slate-400">{{ new Date().toLocaleDateString('en-IN', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</span>
    </div>
    <div class="flex items-center gap-4">
      <div class="flex items-center gap-2">
        <span
          class="inline-block w-2 h-2 rounded-full"
          :class="realtimeStore.connected ? 'bg-green-400' : 'bg-slate-600'"
        />
        <span class="text-xs text-slate-500">{{ realtimeStore.connected ? 'Live' : 'Offline' }}</span>
      </div>
      <button
        @click="logout"
        class="text-xs text-slate-400 hover:text-white transition-colors px-3 py-1.5 rounded-lg hover:bg-slate-800"
      >
        Logout
      </button>
    </div>
  </header>
</template>
