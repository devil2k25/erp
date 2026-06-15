<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { WarehouseService } from '@/services/product.service'

const warehouses = ref<any[]>([])
const loading = ref(true)

onMounted(async () => {
  try {
    const res: any = await WarehouseService.list()
    warehouses.value = res.data
  } finally { loading.value = false }
})
</script>

<template>
  <div class="space-y-4">
    <h1 class="text-2xl font-bold text-white">Warehouses</h1>
    <div v-if="loading" class="text-slate-500 p-8 text-center">Loading...</div>
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="w in warehouses" :key="w.id" class="bg-slate-900 rounded-xl p-5 border border-slate-800">
        <div class="flex items-start justify-between">
          <div>
            <h2 class="text-white font-semibold">{{ w.name }}</h2>
            <p class="text-xs text-slate-500 mt-0.5">{{ w.code }}</p>
          </div>
          <span class="text-xs px-2 py-0.5 rounded bg-slate-800 text-slate-400 capitalize">{{ w.type.replace('_', ' ') }}</span>
        </div>
        <p v-if="w.address" class="text-sm text-slate-400 mt-3">{{ w.address }}</p>
      </div>
    </div>
  </div>
</template>
