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
  <div class="space-y-card-gap">
    <div>
      <h1 class="text-headline-sm text-on-surface">Warehouses</h1>
      <p class="text-body-md text-outline mt-0.5">Storage locations and distribution centers</p>
    </div>

    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-card-gap">
      <div v-for="i in 3" :key="i" class="bg-surface-container-low rounded-xl h-32 animate-pulse border border-outline-variant" />
    </div>

    <div v-else-if="warehouses.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-card-gap">
      <div v-for="w in warehouses" :key="w.id" class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5 hover:shadow-sm transition-shadow">
        <div class="flex items-start justify-between mb-3">
          <div class="w-10 h-10 bg-primary-container rounded-lg flex items-center justify-center">
            <span class="material-symbols-outlined text-primary-fixed text-[20px]">warehouse</span>
          </div>
          <span class="px-2.5 py-1 rounded-full text-[11px] font-bold capitalize bg-surface-container text-on-surface-variant">
            {{ w.type?.replace(/_/g, ' ') }}
          </span>
        </div>
        <h2 class="text-body-lg font-semibold text-on-surface">{{ w.name }}</h2>
        <p class="text-label-md text-outline mt-0.5">{{ w.code }}</p>
        <p v-if="w.address" class="text-body-sm text-secondary mt-2 line-clamp-2">{{ w.address }}</p>
      </div>
    </div>

    <div v-else class="text-center py-16 text-outline">
      <span class="material-symbols-outlined text-[48px] text-outline-variant block mb-3">warehouse</span>
      <p class="text-body-md">No warehouses configured yet.</p>
    </div>
  </div>
</template>
