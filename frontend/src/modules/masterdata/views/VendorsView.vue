<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const search = ref('')
const statusFilter = ref('all')

const vendors = ref([
  { id: '1', code: 'VND-99281', name: 'Global Logistics Inc.', category: 'Logistics & Shipping', contact: 'Sarah Jenkins', email: 's.jenkins@globallogistics.com', status: 'active', reliability: 98.4 },
  { id: '2', code: 'VND-84320', name: 'Precision Parts Co.', category: 'Raw Materials', contact: 'Michael Torres', email: 'm.torres@precisionparts.com', status: 'active', reliability: 91.2 },
  { id: '3', code: 'VND-71055', name: 'TechSupply Ltd.', category: 'Electronics & Components', contact: 'Anna Lee', email: 'a.lee@techsupply.com', status: 'active', reliability: 94.7 },
  { id: '4', code: 'VND-60438', name: 'FastFreight Corp.', category: 'Logistics & Shipping', contact: 'David Kim', email: 'd.kim@fastfreight.com', status: 'suspended', reliability: 72.1 },
  { id: '5', code: 'VND-55902', name: 'Alloy Masters Inc.', category: 'Raw Materials', contact: 'Priya Patel', email: 'p.patel@alloymasters.com', status: 'active', reliability: 88.5 },
])

const filtered = computed(() =>
  vendors.value.filter(v => {
    const matchSearch = !search.value || v.name.toLowerCase().includes(search.value.toLowerCase()) || v.code.toLowerCase().includes(search.value.toLowerCase())
    const matchStatus = statusFilter.value === 'all' || v.status === statusFilter.value
    return matchSearch && matchStatus
  })
)

function statusColor(status: string) {
  return status === 'active' ? 'bg-tertiary-container text-on-tertiary-container' : 'bg-error-container text-on-error-container'
}
</script>

<template>
  <div class="space-y-card-gap">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-headline-sm text-on-surface">Vendors</h1>
        <p class="text-body-md text-outline mt-0.5">Manage supplier relationships and purchase orders</p>
      </div>
      <button class="flex items-center gap-2 px-4 py-2 bg-primary text-on-primary rounded-lg text-label-md hover:opacity-90 transition-opacity active:scale-95">
        <span class="material-symbols-outlined text-[16px]">add</span>
        Add Vendor
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden">
      <div class="p-4 border-b border-outline-variant flex flex-wrap items-center gap-3">
        <div class="relative flex-1 min-w-[200px] max-w-sm">
          <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[16px]">search</span>
          <input
            v-model="search"
            type="text"
            placeholder="Search vendors…"
            class="w-full bg-surface-container-low border border-outline-variant rounded-lg pl-9 pr-4 py-2 text-body-sm text-on-surface placeholder-outline focus:outline-none focus:ring-2 focus:ring-primary/20"
          />
        </div>
        <select
          v-model="statusFilter"
          class="bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-body-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20"
        >
          <option value="all">All Status</option>
          <option value="active">Active</option>
          <option value="suspended">Suspended</option>
        </select>
        <span class="text-body-sm text-outline ml-auto">{{ filtered.length }} vendors</span>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-surface-container">
              <th class="px-6 py-3 text-label-md text-on-surface border-b border-outline-variant">VENDOR ID</th>
              <th class="px-6 py-3 text-label-md text-on-surface border-b border-outline-variant">COMPANY NAME</th>
              <th class="px-6 py-3 text-label-md text-on-surface border-b border-outline-variant">CATEGORY</th>
              <th class="px-6 py-3 text-label-md text-on-surface border-b border-outline-variant">PRIMARY CONTACT</th>
              <th class="px-6 py-3 text-label-md text-on-surface border-b border-outline-variant">RELIABILITY</th>
              <th class="px-6 py-3 text-label-md text-on-surface border-b border-outline-variant">STATUS</th>
              <th class="px-6 py-3 border-b border-outline-variant"></th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="vendor in filtered"
              :key="vendor.id"
              class="data-table-row border-b border-outline-variant last:border-0"
              @click="router.push(`/vendors/${vendor.id}`)"
            >
              <td class="px-6 py-4 text-mono-data text-primary font-bold">{{ vendor.code }}</td>
              <td class="px-6 py-4 text-body-md font-semibold text-on-surface">{{ vendor.name }}</td>
              <td class="px-6 py-4 text-body-md text-secondary">{{ vendor.category }}</td>
              <td class="px-6 py-4">
                <p class="text-body-md text-on-surface">{{ vendor.contact }}</p>
                <p class="text-body-sm text-outline">{{ vendor.email }}</p>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-1.5">
                  <span class="material-symbols-outlined text-[14px]"
                    :class="vendor.reliability >= 90 ? 'text-tertiary' : vendor.reliability >= 80 ? 'text-amber-600' : 'text-error'">
                    {{ vendor.reliability >= 90 ? 'trending_up' : vendor.reliability >= 80 ? 'schedule' : 'trending_down' }}
                  </span>
                  <span class="text-body-md font-semibold text-on-surface">{{ vendor.reliability }}%</span>
                </div>
              </td>
              <td class="px-6 py-4">
                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold uppercase" :class="statusColor(vendor.status)">
                  {{ vendor.status }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <span class="material-symbols-outlined text-outline">chevron_right</span>
              </td>
            </tr>
            <tr v-if="filtered.length === 0">
              <td colspan="7" class="px-6 py-12 text-center text-outline text-body-md">No vendors found</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
