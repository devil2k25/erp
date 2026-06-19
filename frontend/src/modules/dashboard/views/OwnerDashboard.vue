<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const metrics = ref<any>(null)
const loading = ref(true)

onMounted(async () => {
  try {
    const response: any = await api.get('/dashboard/owner')
    metrics.value = response.data
  } catch (e) {
    console.error('Failed to load dashboard metrics', e)
  } finally {
    loading.value = false
  }
})

function machineStatusColor(status: string) {
  const map: Record<string, string> = {
    running:     'bg-tertiary',
    idle:        'bg-outline-variant',
    maintenance: 'bg-amber-400',
    breakdown:   'bg-error',
    retired:     'bg-outline',
  }
  return map[status] ?? 'bg-outline'
}
</script>

<template>
  <div class="space-y-card-gap">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-headline-sm text-on-surface">Owner Dashboard</h1>
        <p class="text-body-md text-outline mt-0.5">{{ new Date().toLocaleDateString('en-IN', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
      </div>
    </div>

    <!-- Loading skeleton -->
    <div v-if="loading" class="grid grid-cols-2 lg:grid-cols-4 gap-card-gap">
      <div v-for="i in 4" :key="i" class="bg-surface-container-low rounded-xl h-28 animate-pulse border border-outline-variant" />
    </div>

    <template v-else-if="metrics">
      <!-- KPI Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-card-gap">
        <div class="bg-surface-container-lowest border border-outline-variant p-5 rounded-xl hover:shadow-sm transition-shadow">
          <p class="text-outline text-[11px] font-bold uppercase tracking-wider mb-2">Today's Production</p>
          <h3 class="text-headline-sm text-on-surface">{{ metrics.production_summary?.today_actual ?? 0 }}</h3>
          <div class="mt-2 flex items-center gap-1 text-outline">
            <span class="material-symbols-outlined text-[14px]">inventory</span>
            <span class="text-[11px] font-bold">of {{ metrics.production_summary?.today_planned ?? 0 }} planned</span>
          </div>
        </div>

        <div class="bg-surface-container-lowest border border-outline-variant p-5 rounded-xl hover:shadow-sm transition-shadow">
          <p class="text-outline text-[11px] font-bold uppercase tracking-wider mb-2">Average OEE</p>
          <h3 class="text-headline-sm text-primary">{{ (metrics.oee_summary?.avg_oee ?? 0).toFixed(1) }}%</h3>
          <div class="mt-2 flex items-center gap-1 text-outline">
            <span class="material-symbols-outlined text-[14px]">analytics</span>
            <span class="text-[11px] font-bold">Target: 85%</span>
          </div>
        </div>

        <div class="bg-surface-container-lowest border border-outline-variant p-5 rounded-xl hover:shadow-sm transition-shadow">
          <p class="text-outline text-[11px] font-bold uppercase tracking-wider mb-2">Workers Present</p>
          <h3 class="text-headline-sm text-tertiary">{{ metrics.worker_summary?.present_today ?? 0 }}</h3>
          <div class="mt-2 flex items-center gap-1 text-outline">
            <span class="material-symbols-outlined text-[14px]">groups</span>
            <span class="text-[11px] font-bold">{{ metrics.worker_summary?.absent_today ?? 0 }} absent</span>
          </div>
        </div>

        <div class="bg-surface-container-lowest border border-outline-variant p-5 rounded-xl hover:shadow-sm transition-shadow">
          <p class="text-outline text-[11px] font-bold uppercase tracking-wider mb-2">Inventory Value</p>
          <h3 class="text-headline-sm text-on-surface">₹{{ ((metrics.inventory_value ?? 0) / 100000).toFixed(1) }}L</h3>
          <div class="mt-2 flex items-center gap-1 text-outline">
            <span class="material-symbols-outlined text-[14px]">inventory_2</span>
            <span class="text-[11px] font-bold">Total stock value</span>
          </div>
        </div>
      </div>

      <!-- Machine Status -->
      <div v-if="metrics.machine_status?.length" class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5">
        <h2 class="text-label-md text-on-surface uppercase tracking-wider mb-4">Machine Status</h2>
        <div class="flex flex-wrap gap-3">
          <div
            v-for="s in metrics.machine_status"
            :key="s.status"
            class="flex items-center gap-2 bg-surface-container-low border border-outline-variant rounded-lg px-4 py-2"
          >
            <span class="w-2 h-2 rounded-full flex-shrink-0" :class="machineStatusColor(s.status)" />
            <span class="text-body-sm text-on-surface capitalize">{{ s.status }}</span>
            <span class="text-label-md text-on-surface font-bold">{{ s.count }}</span>
          </div>
        </div>
      </div>
    </template>

    <div v-else class="text-center py-20 text-outline">
      <span class="material-symbols-outlined text-[48px] mb-4 block text-outline-variant">wifi_off</span>
      <p class="text-body-md">Unable to load dashboard. Please check your connection.</p>
    </div>
  </div>
</template>
