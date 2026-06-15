<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { useRealtimeStore } from '@/stores/realtime.store'

const realtimeStore = useRealtimeStore()
const metrics = ref<any>(null)
const loading = ref(true)

onMounted(async () => {
  try {
    const response: any = await api.get('/dashboard/production')
    metrics.value = response.data
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-white">Production Dashboard</h1>
      <div class="flex items-center gap-2">
        <span class="w-2 h-2 rounded-full animate-pulse" :class="realtimeStore.connected ? 'bg-green-400' : 'bg-slate-600'" />
        <span class="text-xs text-slate-400">{{ realtimeStore.connected ? 'Live' : 'Disconnected' }}</span>
      </div>
    </div>

    <div v-if="loading" class="space-y-4">
      <div class="grid grid-cols-3 gap-4">
        <div v-for="i in 3" :key="i" class="bg-slate-900 rounded-xl h-32 animate-pulse border border-slate-800" />
      </div>
    </div>

    <div v-else-if="metrics" class="space-y-6">
      <!-- OEE by Line -->
      <div v-if="metrics.oee_by_line?.length" class="bg-slate-900 rounded-xl p-5 border border-slate-800">
        <h2 class="text-sm font-semibold text-slate-300 mb-4">OEE by Production Line</h2>
        <div class="space-y-3">
          <div v-for="line in metrics.oee_by_line" :key="line.name" class="flex items-center gap-3">
            <span class="w-28 text-xs text-slate-400 truncate">{{ line.name }}</span>
            <div class="flex-1 bg-slate-800 rounded-full h-2">
              <div
                class="h-2 rounded-full transition-all"
                :class="line.avg_oee >= 85 ? 'bg-green-500' : line.avg_oee >= 65 ? 'bg-yellow-500' : 'bg-red-500'"
                :style="{ width: `${Math.min(100, line.avg_oee ?? 0)}%` }"
              />
            </div>
            <span class="w-12 text-xs text-right text-white font-medium">{{ (line.avg_oee ?? 0).toFixed(1) }}%</span>
          </div>
        </div>
      </div>

      <!-- Active Production Plans -->
      <div v-if="metrics.active_plans?.length" class="bg-slate-900 rounded-xl p-5 border border-slate-800">
        <h2 class="text-sm font-semibold text-slate-300 mb-4">Active Production Plans</h2>
        <div class="space-y-2">
          <div v-for="plan in metrics.active_plans" :key="plan.id" class="flex items-center justify-between bg-slate-800 rounded-lg p-3">
            <div>
              <p class="text-sm text-white font-medium">{{ plan.product?.name }}</p>
              <p class="text-xs text-slate-400">{{ plan.production_line?.name }} · {{ plan.plan_number }}</p>
            </div>
            <div class="text-right">
              <p class="text-sm font-bold text-blue-400">{{ plan.actual_quantity }} / {{ plan.planned_quantity }}</p>
              <p class="text-xs text-slate-500">produced</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Real-time updates -->
      <div v-if="realtimeStore.productionUpdates.length" class="bg-slate-900 rounded-xl p-5 border border-slate-800">
        <h2 class="text-sm font-semibold text-slate-300 mb-4">Live Production Feed</h2>
        <div class="space-y-2 max-h-48 overflow-y-auto">
          <div v-for="(update, i) in realtimeStore.productionUpdates.slice(0, 10)" :key="i" class="text-xs text-slate-400 flex gap-2">
            <span class="text-green-400">▶</span>
            <span>{{ JSON.stringify(update) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
