<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { MachineService } from '@/services/product.service'

const machines = ref<any[]>([])
const loading = ref(true)

const statusConfig: Record<string, { dot: string; badge: string; label: string }> = {
  running:     { dot: 'bg-tertiary',       badge: 'bg-tertiary-container text-on-tertiary-container',     label: 'Running' },
  idle:        { dot: 'bg-outline-variant',badge: 'bg-surface-container text-on-surface-variant',         label: 'Idle' },
  maintenance: { dot: 'bg-amber-400',      badge: 'bg-amber-100 text-amber-800',                          label: 'Maintenance' },
  breakdown:   { dot: 'bg-error',          badge: 'bg-error-container text-on-error-container',            label: 'Breakdown' },
  retired:     { dot: 'bg-outline',        badge: 'bg-surface-container-high text-outline',               label: 'Retired' },
}

onMounted(async () => {
  try {
    const res: any = await MachineService.list()
    machines.value = res.data
  } finally { loading.value = false }
})
</script>

<template>
  <div class="space-y-card-gap">
    <div>
      <h1 class="text-headline-sm text-on-surface">Machines</h1>
      <p class="text-body-md text-outline mt-0.5">Production machinery and equipment status</p>
    </div>

    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-card-gap">
      <div v-for="i in 3" :key="i" class="bg-surface-container-low rounded-xl h-36 animate-pulse border border-outline-variant" />
    </div>

    <div v-else-if="machines.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-card-gap">
      <div v-for="m in machines" :key="m.id" class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5 hover:shadow-sm transition-shadow">
        <div class="flex items-start justify-between mb-4">
          <div class="flex items-center gap-3">
            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0 mt-0.5"
              :class="(statusConfig[m.status] ?? statusConfig.idle).dot" />
            <div>
              <h2 class="text-body-lg font-semibold text-on-surface">{{ m.name }}</h2>
              <p class="text-body-sm text-outline">{{ m.code }}</p>
            </div>
          </div>
          <span class="px-2.5 py-1 rounded-full text-[11px] font-bold"
            :class="(statusConfig[m.status] ?? statusConfig.idle).badge">
            {{ (statusConfig[m.status] ?? statusConfig.idle).label }}
          </span>
        </div>

        <div class="space-y-2">
          <div class="flex justify-between text-body-sm">
            <span class="text-outline">Type</span>
            <span class="text-on-surface font-medium">{{ m.machine_type ?? '—' }}</span>
          </div>
          <div class="flex justify-between text-body-sm">
            <span class="text-outline">Manufacturer</span>
            <span class="text-on-surface">{{ m.manufacturer ?? '—' }}</span>
          </div>
          <div class="flex justify-between text-body-sm">
            <span class="text-outline">Serial</span>
            <span class="text-mono-data text-on-surface-variant">{{ m.serial_number ?? '—' }}</span>
          </div>
        </div>

        <div v-if="m.next_maintenance_at" class="mt-4 flex items-center gap-2 p-2.5 bg-amber-50 border border-amber-200 rounded-lg">
          <span class="material-symbols-outlined text-amber-600 text-[14px]">schedule</span>
          <span class="text-body-sm text-amber-700">
            Maintenance: {{ new Date(m.next_maintenance_at).toLocaleDateString() }}
          </span>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-16 text-outline">
      <span class="material-symbols-outlined text-[48px] text-outline-variant block mb-3">precision_manufacturing</span>
      <p class="text-body-md">No machines configured yet.</p>
    </div>
  </div>
</template>
