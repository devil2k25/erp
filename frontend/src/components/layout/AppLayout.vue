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
  <div class="overflow-hidden">
    <Sidebar />
    <Topbar />
    <main class="ml-sidebar-width pt-16 h-screen overflow-y-auto custom-scrollbar bg-background">
      <div class="p-container-padding pb-20">
        <router-view />
      </div>
    </main>
  </div>
</template>
