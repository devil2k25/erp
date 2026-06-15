<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRealtimeStore } from '@/stores/realtime.store'
import { ProductionEntryService } from '@/services/production.service'

const realtimeStore = useRealtimeStore()
const todayEntries = ref<any[]>([])
const loading = ref(true)
const showEntryModal = ref(false)

const form = ref({
  production_plan_id: '', shift_id: '', machine_id: '',
  entry_date: new Date().toISOString().split('T')[0],
  start_time: new Date().toISOString().slice(0, 16),
  end_time: '',
  planned_quantity: 0, produced_quantity: 0,
  rejected_quantity: 0, rework_quantity: 0,
  downtime_minutes: 0, downtime_reason: '', notes: '',
})

onMounted(async () => {
  try {
    const res: any = await ProductionEntryService.list({ date: new Date().toISOString().split('T')[0] })
    todayEntries.value = res.data?.data ?? res.data
  } finally { loading.value = false }
})

async function submitEntry() {
  await ProductionEntryService.create(form.value as any)
  showEntryModal.value = false
  const res: any = await ProductionEntryService.list({ date: form.value.entry_date })
  todayEntries.value = res.data?.data ?? res.data
}

const oeeColor = (score: number | null) => {
  if (!score) return 'text-slate-500'
  if (score >= 85) return 'text-green-400'
  if (score >= 65) return 'text-yellow-400'
  return 'text-red-400'
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <h1 class="text-2xl font-bold text-white">Production Floor</h1>
        <span class="flex items-center gap-1.5 text-xs text-slate-400">
          <span class="w-2 h-2 rounded-full" :class="realtimeStore.connected ? 'bg-green-400 animate-pulse' : 'bg-slate-600'" />
          {{ realtimeStore.connected ? 'Live' : 'Offline' }}
        </span>
      </div>
      <button @click="showEntryModal = true" class="bg-green-600 hover:bg-green-500 text-white text-sm px-4 py-2 rounded-lg">+ Submit Entry</button>
    </div>

    <div class="bg-slate-900 rounded-xl border border-slate-800 overflow-hidden">
      <div class="px-4 py-3 border-b border-slate-800 text-xs text-slate-400">Today's Production Entries</div>
      <div v-if="loading" class="p-8 text-center text-slate-500">Loading...</div>
      <table v-else class="w-full text-sm">
        <thead>
          <tr class="border-b border-slate-800 text-xs text-slate-500 uppercase">
            <th class="text-left px-4 py-3">Plan</th>
            <th class="text-left px-4 py-3">Shift</th>
            <th class="text-right px-4 py-3">Produced</th>
            <th class="text-right px-4 py-3">Rejected</th>
            <th class="text-right px-4 py-3">Downtime</th>
            <th class="text-center px-4 py-3">OEE</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="e in todayEntries" :key="e.id" class="border-b border-slate-800/50 hover:bg-slate-800/30">
            <td class="px-4 py-3">
              <p class="text-white">{{ e.plan?.product?.name ?? e.production_plan_id }}</p>
              <p class="text-xs text-slate-500">{{ e.operator?.first_name }} {{ e.operator?.last_name }}</p>
            </td>
            <td class="px-4 py-3 text-slate-400">{{ e.shift?.name ?? '—' }}</td>
            <td class="px-4 py-3 text-right text-green-400 font-bold">{{ e.produced_quantity }}</td>
            <td class="px-4 py-3 text-right text-red-400">{{ e.rejected_quantity }}</td>
            <td class="px-4 py-3 text-right text-yellow-400">{{ e.downtime_minutes }}m</td>
            <td class="px-4 py-3 text-center">
              <span :class="['font-bold', oeeColor(e.oee_score)]">
                {{ e.oee_score != null ? e.oee_score.toFixed(1) + '%' : 'Pending' }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="!loading && !todayEntries.length" class="p-8 text-center text-slate-500">No entries today.</div>
    </div>

    <!-- Live feed -->
    <div v-if="realtimeStore.productionUpdates.length" class="bg-slate-900 rounded-xl border border-green-900/30 p-4">
      <p class="text-xs text-green-400 mb-2">● Real-time feed</p>
      <div class="space-y-1 max-h-32 overflow-y-auto">
        <div v-for="(u, i) in realtimeStore.productionUpdates.slice(0, 5)" :key="i" class="text-xs text-slate-400">
          {{ JSON.stringify(u).slice(0, 100) }}...
        </div>
      </div>
    </div>

    <!-- Submit Entry Modal -->
    <div v-if="showEntryModal" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
      <div class="bg-slate-900 rounded-2xl p-6 w-full max-w-lg border border-slate-800 max-h-[90vh] overflow-y-auto">
        <h2 class="text-lg font-bold text-white mb-4">Submit Production Entry</h2>
        <form @submit.prevent="submitEntry" class="space-y-3">
          <div class="grid grid-cols-2 gap-3">
            <div class="col-span-2">
              <label class="text-xs text-slate-400 mb-1 block">Production Plan ID *</label>
              <input v-model="form.production_plan_id" required placeholder="UUID" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" />
            </div>
            <div>
              <label class="text-xs text-slate-400 mb-1 block">Shift ID *</label>
              <input v-model="form.shift_id" required placeholder="UUID" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" />
            </div>
            <div>
              <label class="text-xs text-slate-400 mb-1 block">Machine ID</label>
              <input v-model="form.machine_id" placeholder="UUID (optional)" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" />
            </div>
            <div>
              <label class="text-xs text-slate-400 mb-1 block">Start Time *</label>
              <input v-model="form.start_time" required type="datetime-local" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" />
            </div>
            <div>
              <label class="text-xs text-slate-400 mb-1 block">End Time</label>
              <input v-model="form.end_time" type="datetime-local" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" />
            </div>
            <div>
              <label class="text-xs text-slate-400 mb-1 block">Planned Qty *</label>
              <input v-model.number="form.planned_quantity" required type="number" min="0" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" />
            </div>
            <div>
              <label class="text-xs text-slate-400 mb-1 block">Produced Qty *</label>
              <input v-model.number="form.produced_quantity" required type="number" min="0" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" />
            </div>
            <div>
              <label class="text-xs text-slate-400 mb-1 block">Rejected Qty</label>
              <input v-model.number="form.rejected_quantity" type="number" min="0" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" />
            </div>
            <div>
              <label class="text-xs text-slate-400 mb-1 block">Downtime (min)</label>
              <input v-model.number="form.downtime_minutes" type="number" min="0" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" />
            </div>
          </div>
          <div class="flex gap-3 justify-end pt-2">
            <button type="button" @click="showEntryModal = false" class="px-4 py-2 text-sm text-slate-400">Cancel</button>
            <button type="submit" class="px-4 py-2 text-sm bg-green-600 hover:bg-green-500 text-white rounded-lg">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
