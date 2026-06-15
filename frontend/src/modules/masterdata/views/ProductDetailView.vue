<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { ProductService } from '@/services/product.service'
import { InventoryService } from '@/services/inventory.service'

const route = useRoute()
const product = ref<any>(null)
const stockSummary = ref<any>(null)

onMounted(async () => {
  const [pRes, sRes]: any[] = await Promise.all([
    ProductService.get(route.params.id as string),
    InventoryService.getStockSummary(route.params.id as string).catch(() => null),
  ])
  product.value = pRes.data
  stockSummary.value = sRes?.data
})
</script>

<template>
  <div v-if="product" class="space-y-6">
    <div class="flex items-center gap-4">
      <router-link to="/products" class="text-slate-400 hover:text-white text-sm">← Products</router-link>
      <h1 class="text-2xl font-bold text-white">{{ product.name }}</h1>
      <span class="text-xs px-2 py-0.5 bg-slate-800 text-slate-400 rounded">{{ product.sku }}</span>
    </div>

    <div class="grid grid-cols-3 gap-4">
      <div class="bg-slate-900 rounded-xl p-5 border border-slate-800">
        <p class="text-xs text-slate-500">Total On Hand</p>
        <p class="text-3xl font-bold text-white mt-1">{{ stockSummary?.total_on_hand ?? 0 }}</p>
      </div>
      <div class="bg-slate-900 rounded-xl p-5 border border-slate-800">
        <p class="text-xs text-slate-500">Available</p>
        <p class="text-3xl font-bold text-green-400 mt-1">{{ stockSummary?.total_available ?? 0 }}</p>
      </div>
      <div class="bg-slate-900 rounded-xl p-5 border border-slate-800">
        <p class="text-xs text-slate-500">Reserved</p>
        <p class="text-3xl font-bold text-yellow-400 mt-1">{{ stockSummary?.total_reserved ?? 0 }}</p>
      </div>
    </div>

    <div v-if="product.bom_items?.length" class="bg-slate-900 rounded-xl p-5 border border-slate-800">
      <h2 class="text-sm font-semibold text-slate-300 mb-4">Bill of Materials</h2>
      <table class="w-full text-sm">
        <thead>
          <tr class="text-xs text-slate-500 border-b border-slate-800">
            <th class="text-left pb-2">Component</th>
            <th class="text-right pb-2">Quantity</th>
            <th class="text-right pb-2">Scrap %</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="bom in product.bom_items" :key="bom.id" class="border-b border-slate-800/50">
            <td class="py-2 text-white">{{ bom.component?.name }}</td>
            <td class="py-2 text-right text-slate-300">{{ bom.quantity }} {{ bom.unit?.symbol }}</td>
            <td class="py-2 text-right text-slate-400">{{ bom.scrap_percentage }}%</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <div v-else class="p-8 text-center text-slate-500">Loading...</div>
</template>
