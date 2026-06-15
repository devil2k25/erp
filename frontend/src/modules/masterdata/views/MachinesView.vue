<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { MachineService } from '@/services/product.service'

const machines = ref<any[]>([])
const loading = ref(true)

const statusColors: Record<string, string> = {
  running: 'bg-green-500',
  idle: 'bg-slate-500',
  maintenance: 'bg-yellow-500',
  breakdown: 'bg-red-500',
  retired: 'bg-slate-700',
}

onMounted(async () => {
  try {
    const res: any = await MachineService.list()
    machines.value = res.data
  } finally { loading.value = false }
})
</script>

<template>
  <div class="space-y-4">
    <h1 class="text-2xl font-bold text-white">Machines</h1>
    <div v-if="loading" class="text-slate-500 p-8 text-center">Loading...</div>
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="m in machines" :key="m.id" class="bg-slate-900 rounded-xl p-5 border border-slate-800">
        <div class="flex items-center gap-3">
          <span class="w-3 h-3 rounded-full" :class="statusColors[m.status] ?? 'bg-slate-500'" />
          <div>
            <h2 class="text-white font-semibold">{{ m.name }}</h2>
            <p class="text-xs text-slate-500">{{ m.code }} · {{ m.machine_type }}</p>
          </div>
        </div>
        <div class="mt-3 grid grid-cols-2 gap-2 text-xs text-slate-400">
          <div>Manufacturer: <span class="text-slate-300">{{ m.manufacturer ?? '—' }}</span></div>
          <div>Serial: <span class="text-slate-300">{{ m.serial_number ?? '—' }}</span></div>
        </div>
        <div v-if="m.next_maintenance_at" class="mt-2 text-xs text-yellow-400">
          Next maintenance: {{ new Date(m.next_maintenance_at).toLocaleDateString() }}
        </div>
      </div>
    </div>
  </div>
</template>
