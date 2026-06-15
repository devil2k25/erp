<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { InventoryService } from '@/services/inventory.service'

const items = ref<any[]>([])
const lowStock = ref<any[]>([])
const loading = ref(true)
const activeTab = ref<'all' | 'low'>('all')

onMounted(async () => {
  try {
    const [itemsRes, lowRes]: any[] = await Promise.all([
      InventoryService.listItems(),
      InventoryService.getLowStockAlerts(),
    ])
    items.value = itemsRes.data?.data ?? itemsRes.data
    lowStock.value = lowRes.data
  } finally { loading.value = false }
})
</script>

<template>
  <div class="space-y-4">
    <h1 class="text-2xl font-bold text-white">Inventory</h1>

    <div class="flex gap-2">
      <button @click="activeTab = 'all'" :class="['text-sm px-4 py-1.5 rounded-lg', activeTab === 'all' ? 'bg-blue-600 text-white' : 'bg-slate-800 text-slate-400']">All Stock</button>
      <button @click="activeTab = 'low'" :class="['text-sm px-4 py-1.5 rounded-lg flex items-center gap-2', activeTab === 'low' ? 'bg-red-600 text-white' : 'bg-slate-800 text-slate-400']">
        Low Stock
        <span v-if="lowStock.length" class="bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">{{ lowStock.length }}</span>
      </button>
    </div>

    <div class="bg-slate-900 rounded-xl border border-slate-800 overflow-hidden">
      <div v-if="loading" class="p-8 text-center text-slate-500">Loading...</div>
      <template v-else>
        <table class="w-full text-sm" v-if="activeTab === 'all'">
          <thead>
            <tr class="border-b border-slate-800 text-xs text-slate-500 uppercase">
              <th class="text-left px-4 py-3">Product</th>
              <th class="text-left px-4 py-3">Warehouse</th>
              <th class="text-left px-4 py-3">Batch</th>
              <th class="text-right px-4 py-3">On Hand</th>
              <th class="text-right px-4 py-3">Reserved</th>
              <th class="text-right px-4 py-3">Available</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in items" :key="item.id" class="border-b border-slate-800/50 hover:bg-slate-800/30">
              <td class="px-4 py-3">
                <p class="text-white">{{ item.product?.name }}</p>
                <p class="text-xs text-slate-500">{{ item.product?.sku }}</p>
              </td>
              <td class="px-4 py-3 text-slate-400">{{ item.warehouse?.name }}</td>
              <td class="px-4 py-3 text-slate-400 font-mono text-xs">{{ item.batch_number ?? '—' }}</td>
              <td class="px-4 py-3 text-right text-white">{{ item.quantity_on_hand }}</td>
              <td class="px-4 py-3 text-right text-yellow-400">{{ item.quantity_reserved }}</td>
              <td class="px-4 py-3 text-right text-green-400">{{ (item.quantity_on_hand - item.quantity_reserved).toFixed(2) }}</td>
            </tr>
          </tbody>
        </table>

        <div v-if="activeTab === 'low'" class="p-4 space-y-2">
          <div v-for="p in lowStock" :key="p.id" class="flex items-center justify-between bg-red-900/10 border border-red-900/30 rounded-lg p-3">
            <div>
              <p class="text-white font-medium">{{ p.name }}</p>
              <p class="text-xs text-slate-400">{{ p.sku }} · Reorder at {{ p.reorder_point }} {{ p.unit?.symbol }}</p>
            </div>
            <span class="text-red-400 text-sm font-bold">⚠ Low Stock</span>
          </div>
          <div v-if="!lowStock.length" class="text-center text-slate-500 py-8">All stock levels are healthy!</div>
        </div>
      </template>
    </div>
  </div>
</template>
