<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { ProductionEntryService } from '@/services/production.service'

const summary = ref<any>(null)
const loading = ref(true)
const selectedDate = ref(new Date().toISOString().split('T')[0])

async function load() {
  loading.value = true
  try {
    const res: any = await ProductionEntryService.oeeSummary({ date: selectedDate.value })
    summary.value = res.data
  } finally { loading.value = false }
}

onMounted(load)

const oeeGaugeColor = (score: number) => {
  if (score >= 85) return 'text-green-400 border-green-400'
  if (score >= 65) return 'text-yellow-400 border-yellow-400'
  return 'text-red-400 border-red-400'
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-white">OEE Dashboard</h1>
      <input
        v-model="selectedDate"
        @change="load"
        type="date"
        class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white"
      />
    </div>

    <div v-if="loading" class="grid grid-cols-4 gap-4">
      <div v-for="i in 4" :key="i" class="bg-slate-900 rounded-xl h-32 animate-pulse border border-slate-800" />
    </div>

    <div v-else-if="summary" class="space-y-6">
      <!-- OEE Gauge -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-slate-900 rounded-xl p-6 border border-slate-800 text-center">
          <div :class="['text-5xl font-bold mb-1', oeeGaugeColor(summary.avg_oee ?? 0)]">
            {{ (summary.avg_oee ?? 0).toFixed(1) }}%
          </div>
          <p class="text-xs text-slate-500 uppercase tracking-wider">Overall OEE</p>
          <p class="text-xs mt-2" :class="(summary.avg_oee ?? 0) >= 85 ? 'text-green-400' : 'text-yellow-400'">
            {{ (summary.avg_oee ?? 0) >= 85 ? 'World Class' : (summary.avg_oee ?? 0) >= 65 ? 'Average' : 'Below Average' }}
          </p>
        </div>
        <div class="bg-slate-900 rounded-xl p-6 border border-slate-800 text-center">
          <div class="text-4xl font-bold text-blue-400 mb-1">{{ (summary.avg_availability ?? 0).toFixed(1) }}%</div>
          <p class="text-xs text-slate-500 uppercase tracking-wider">Availability</p>
        </div>
        <div class="bg-slate-900 rounded-xl p-6 border border-slate-800 text-center">
          <div class="text-4xl font-bold text-purple-400 mb-1">{{ (summary.avg_performance ?? 0).toFixed(1) }}%</div>
          <p class="text-xs text-slate-500 uppercase tracking-wider">Performance</p>
        </div>
        <div class="bg-slate-900 rounded-xl p-6 border border-slate-800 text-center">
          <div class="text-4xl font-bold text-green-400 mb-1">{{ (summary.avg_quality ?? 0).toFixed(1) }}%</div>
          <p class="text-xs text-slate-500 uppercase tracking-wider">Quality</p>
        </div>
      </div>

      <!-- Production summary -->
      <div class="grid grid-cols-3 gap-4">
        <div class="bg-slate-900 rounded-xl p-5 border border-slate-800">
          <p class="text-xs text-slate-500">Total Produced</p>
          <p class="text-2xl font-bold text-white mt-1">{{ summary.total_produced ?? 0 }}</p>
        </div>
        <div class="bg-slate-900 rounded-xl p-5 border border-slate-800">
          <p class="text-xs text-slate-500">Total Rejected</p>
          <p class="text-2xl font-bold text-red-400 mt-1">{{ summary.total_rejected ?? 0 }}</p>
        </div>
        <div class="bg-slate-900 rounded-xl p-5 border border-slate-800">
          <p class="text-xs text-slate-500">Total Downtime</p>
          <p class="text-2xl font-bold text-yellow-400 mt-1">{{ summary.total_downtime ?? 0 }}m</p>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-16 text-slate-500">No production data for selected date.</div>
  </div>
</template>
