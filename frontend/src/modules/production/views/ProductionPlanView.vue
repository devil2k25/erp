<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useProductionPlanStore } from '@/stores/production/productionPlan.store'

const planStore = useProductionPlanStore()
const showModal = ref(false)

const form = ref({
  product_id: '', production_line_id: '', planned_quantity: 0,
  planned_start_date: '', planned_end_date: '', priority: 'medium', notes: '',
})

onMounted(() => planStore.fetchPlans())

async function save() {
  await planStore.createPlan(form.value as any)
  showModal.value = false
}

async function approve(id: string) {
  try {
    await planStore.approvePlan(id)
  } catch (e: any) {
    alert(e?.error ?? 'Approval failed: ' + JSON.stringify(e))
  }
}

async function start(id: string) {
  await planStore.startPlan(id)
}

const statusColors: Record<string, string> = {
  draft: 'bg-slate-800 text-slate-400',
  approved: 'bg-blue-900/50 text-blue-400',
  in_progress: 'bg-green-900/50 text-green-400',
  completed: 'bg-purple-900/50 text-purple-400',
  cancelled: 'bg-red-900/50 text-red-400',
  on_hold: 'bg-yellow-900/50 text-yellow-400',
}

const priorityColors: Record<string, string> = {
  low: 'text-slate-400', medium: 'text-blue-400',
  high: 'text-orange-400', urgent: 'text-red-400',
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-white">Production Plans</h1>
      <button @click="showModal = true" class="bg-blue-600 hover:bg-blue-500 text-white text-sm px-4 py-2 rounded-lg">+ New Plan</button>
    </div>

    <div class="bg-slate-900 rounded-xl border border-slate-800 overflow-hidden">
      <div v-if="planStore.loading" class="p-8 text-center text-slate-500">Loading...</div>
      <table v-else class="w-full text-sm">
        <thead>
          <tr class="border-b border-slate-800 text-xs text-slate-500 uppercase">
            <th class="text-left px-4 py-3">Plan #</th>
            <th class="text-left px-4 py-3">Product</th>
            <th class="text-left px-4 py-3">Line</th>
            <th class="text-right px-4 py-3">Qty</th>
            <th class="text-left px-4 py-3">Dates</th>
            <th class="text-center px-4 py-3">Priority</th>
            <th class="text-center px-4 py-3">Status</th>
            <th class="text-center px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="plan in planStore.plans" :key="plan.id" class="border-b border-slate-800/50 hover:bg-slate-800/30">
            <td class="px-4 py-3 font-mono text-xs text-blue-400">{{ plan.plan_number }}</td>
            <td class="px-4 py-3 text-white">{{ plan.product?.name ?? plan.product_id }}</td>
            <td class="px-4 py-3 text-slate-400">{{ plan.production_line?.name ?? plan.production_line_id }}</td>
            <td class="px-4 py-3 text-right text-white">
              <span class="text-green-400">{{ plan.actual_quantity }}</span> / {{ plan.planned_quantity }}
            </td>
            <td class="px-4 py-3 text-xs text-slate-400">
              {{ plan.planned_start_date?.slice(0, 10) }} → {{ plan.planned_end_date?.slice(0, 10) }}
            </td>
            <td class="px-4 py-3 text-center">
              <span :class="['text-xs capitalize', priorityColors[plan.priority] ?? 'text-slate-400']">{{ plan.priority }}</span>
            </td>
            <td class="px-4 py-3 text-center">
              <span :class="['text-xs px-2 py-0.5 rounded capitalize', statusColors[plan.status] ?? 'bg-slate-800 text-slate-400']">{{ plan.status }}</span>
            </td>
            <td class="px-4 py-3 text-center space-x-2">
              <button v-if="plan.status === 'draft'" @click="approve(plan.id)" class="text-xs text-blue-400 hover:text-blue-300">Approve</button>
              <button v-if="plan.status === 'approved'" @click="start(plan.id)" class="text-xs text-green-400 hover:text-green-300">Start</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="showModal" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
      <div class="bg-slate-900 rounded-2xl p-6 w-full max-w-lg border border-slate-800">
        <h2 class="text-lg font-bold text-white mb-4">New Production Plan</h2>
        <form @submit.prevent="save" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
              <label class="block text-xs text-slate-400 mb-1">Product ID *</label>
              <input v-model="form.product_id" required placeholder="UUID" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" />
            </div>
            <div class="col-span-2">
              <label class="block text-xs text-slate-400 mb-1">Production Line ID *</label>
              <input v-model="form.production_line_id" required placeholder="UUID" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" />
            </div>
            <div>
              <label class="block text-xs text-slate-400 mb-1">Planned Quantity *</label>
              <input v-model.number="form.planned_quantity" required type="number" min="0.01" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" />
            </div>
            <div>
              <label class="block text-xs text-slate-400 mb-1">Priority</label>
              <select v-model="form.priority" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
              </select>
            </div>
            <div>
              <label class="block text-xs text-slate-400 mb-1">Start Date *</label>
              <input v-model="form.planned_start_date" required type="datetime-local" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" />
            </div>
            <div>
              <label class="block text-xs text-slate-400 mb-1">End Date *</label>
              <input v-model="form.planned_end_date" required type="datetime-local" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" />
            </div>
          </div>
          <div class="flex gap-3 justify-end">
            <button type="button" @click="showModal = false" class="px-4 py-2 text-sm text-slate-400">Cancel</button>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-500 text-white rounded-lg">Create Plan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
