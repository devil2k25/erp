<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const metrics = ref<any>(null)
const loading = ref(true)

onMounted(async () => {
  try {
    const response: any = await api.get('/dashboard/inventory')
    metrics.value = response.data
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="space-y-6">
    <h1 class="text-2xl font-bold text-white">Inventory Dashboard</h1>

    <div v-if="loading" class="grid grid-cols-3 gap-4">
      <div v-for="i in 3" :key="i" class="bg-slate-900 rounded-xl h-28 animate-pulse border border-slate-800" />
    </div>

    <div v-else-if="metrics" class="space-y-6">
      <div class="grid grid-cols-3 gap-4">
        <div class="bg-slate-900 rounded-xl p-5 border border-slate-800">
          <p class="text-xs text-slate-500 uppercase tracking-wider">Low Stock Items</p>
          <p class="text-3xl font-bold mt-2" :class="metrics.low_stock_count > 0 ? 'text-red-400' : 'text-green-400'">{{ metrics.low_stock_count }}</p>
        </div>
        <div class="bg-slate-900 rounded-xl p-5 border border-slate-800">
          <p class="text-xs text-slate-500 uppercase tracking-wider">Pending GRNs</p>
          <p class="text-3xl font-bold mt-2 text-yellow-400">{{ metrics.pending_grns }}</p>
        </div>
        <div class="bg-slate-900 rounded-xl p-5 border border-slate-800">
          <p class="text-xs text-slate-500 uppercase tracking-wider">Warehouses</p>
          <p class="text-3xl font-bold mt-2 text-white">{{ metrics.stock_by_warehouse?.length ?? 0 }}</p>
        </div>
      </div>

      <div v-if="metrics.recent_grns?.length" class="bg-slate-900 rounded-xl p-5 border border-slate-800">
        <h2 class="text-sm font-semibold text-slate-300 mb-4">Recent GRNs</h2>
        <table class="w-full text-sm">
          <thead>
            <tr class="text-xs text-slate-500 text-left border-b border-slate-800">
              <th class="pb-2">GRN #</th>
              <th class="pb-2">Vendor</th>
              <th class="pb-2">Amount</th>
              <th class="pb-2">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="grn in metrics.recent_grns" :key="grn.id" class="border-b border-slate-800/50">
              <td class="py-2 text-blue-400">{{ grn.grn_number }}</td>
              <td class="py-2 text-slate-300">{{ grn.vendor?.name }}</td>
              <td class="py-2 text-white">₹{{ grn.total_amount?.toLocaleString() }}</td>
              <td class="py-2">
                <span class="px-2 py-0.5 rounded text-xs" :class="{
                  'bg-green-900/50 text-green-400': grn.status === 'approved',
                  'bg-yellow-900/50 text-yellow-400': grn.status === 'received',
                  'bg-red-900/50 text-red-400': grn.status === 'rejected',
                  'bg-slate-800 text-slate-400': grn.status === 'draft',
                }">{{ grn.status }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
