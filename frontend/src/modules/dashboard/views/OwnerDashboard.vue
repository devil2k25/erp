<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { VueApexCharts } from 'vue3-apexcharts'

const metrics = ref<any>(null)
const loading = ref(true)

const oeeOptions = {
  chart: { type: 'radialBar', background: 'transparent' },
  colors: ['#3b82f6'],
  plotOptions: { radialBar: { dataLabels: { value: { fontSize: '24px', color: '#fff' } } } },
  labels: ['OEE'],
  theme: { mode: 'dark' },
}

const productionOptions = {
  chart: { type: 'bar', background: 'transparent', toolbar: { show: false } },
  colors: ['#3b82f6', '#10b981'],
  xaxis: { categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'], labels: { style: { colors: '#94a3b8' } } },
  yaxis: { labels: { style: { colors: '#94a3b8' } } },
  legend: { labels: { colors: '#94a3b8' } },
  theme: { mode: 'dark' },
  grid: { borderColor: '#1e293b' },
}

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
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-white">Owner Dashboard</h1>
      <span class="text-sm text-slate-400">{{ new Date().toLocaleDateString() }}</span>
    </div>

    <div v-if="loading" class="grid grid-cols-4 gap-4">
      <div v-for="i in 4" :key="i" class="bg-slate-900 rounded-xl h-28 animate-pulse border border-slate-800" />
    </div>

    <div v-else-if="metrics" class="space-y-6">
      <!-- KPI Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-slate-900 rounded-xl p-5 border border-slate-800">
          <p class="text-xs text-slate-500 uppercase tracking-wider">Today's Production</p>
          <p class="text-3xl font-bold text-white mt-2">{{ metrics.production_summary?.today_actual ?? 0 }}</p>
          <p class="text-xs text-slate-400 mt-1">of {{ metrics.production_summary?.today_planned ?? 0 }} planned</p>
        </div>
        <div class="bg-slate-900 rounded-xl p-5 border border-slate-800">
          <p class="text-xs text-slate-500 uppercase tracking-wider">Average OEE</p>
          <p class="text-3xl font-bold text-blue-400 mt-2">{{ (metrics.oee_summary?.avg_oee ?? 0).toFixed(1) }}%</p>
          <p class="text-xs text-slate-400 mt-1">target: 85%</p>
        </div>
        <div class="bg-slate-900 rounded-xl p-5 border border-slate-800">
          <p class="text-xs text-slate-500 uppercase tracking-wider">Workers Present</p>
          <p class="text-3xl font-bold text-green-400 mt-2">{{ metrics.worker_summary?.present_today ?? 0 }}</p>
          <p class="text-xs text-slate-400 mt-1">{{ metrics.worker_summary?.absent_today ?? 0 }} absent</p>
        </div>
        <div class="bg-slate-900 rounded-xl p-5 border border-slate-800">
          <p class="text-xs text-slate-500 uppercase tracking-wider">Inventory Value</p>
          <p class="text-3xl font-bold text-white mt-2">₹{{ ((metrics.inventory_value ?? 0) / 100000).toFixed(1) }}L</p>
          <p class="text-xs text-slate-400 mt-1">total stock value</p>
        </div>
      </div>

      <!-- Machine Status -->
      <div v-if="metrics.machine_status?.length" class="bg-slate-900 rounded-xl p-5 border border-slate-800">
        <h2 class="text-sm font-semibold text-slate-300 mb-4">Machine Status</h2>
        <div class="flex flex-wrap gap-3">
          <div v-for="s in metrics.machine_status" :key="s.status" class="flex items-center gap-2 bg-slate-800 rounded-lg px-3 py-2">
            <span class="w-2 h-2 rounded-full"
              :class="{
                'bg-green-400': s.status === 'running',
                'bg-yellow-400': s.status === 'idle',
                'bg-orange-400': s.status === 'maintenance',
                'bg-red-500': s.status === 'breakdown',
                'bg-slate-500': s.status === 'retired',
              }"
            />
            <span class="text-xs text-slate-300 capitalize">{{ s.status }}</span>
            <span class="text-xs font-bold text-white">{{ s.count }}</span>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-20 text-slate-500">
      <p>Unable to load dashboard data. Please check your connection.</p>
    </div>
  </div>
</template>
