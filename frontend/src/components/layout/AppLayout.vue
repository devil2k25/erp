<script setup lang="ts">
import { onMounted } from 'vue'
import Sidebar from './Sidebar.vue'
import Topbar from './Topbar.vue'
import { useAuthStore } from '@/stores/auth.store'
import { useRealtimeStore } from '@/stores/realtime.store'

const authStore = useAuthStore()
const realtimeStore = useRealtimeStore()

onMounted(() => {
  if (authStore.token && authStore.orgSlug) {
    realtimeStore.connect(authStore.orgSlug, authStore.token)
  }
})
</script>

<template>
  <div class="flex h-screen bg-slate-900 text-slate-100 overflow-hidden">
    <Sidebar />
    <div class="flex flex-col flex-1 overflow-hidden">
      <Topbar />
      <main class="flex-1 overflow-y-auto p-6 bg-slate-950">
        <router-view />
      </main>
    </div>
  </div>
</template>
