<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()
const spendPeriod = ref('Last 12 Months')

/* ── Mock data (replace with API call using route.params.id) ─────── */
const vendor = ref({
  id: route.params.id,
  code: 'VND-99281',
  name: 'Global Logistics Inc.',
  category: 'Logistics & Shipping',
  verified: true,
  rating: 4,
  reliability: 98.4,
  logo: null as string | null,
  kpis: {
    totalSpend: '$1.24M',
    totalSpendTrend: '+12% vs LY',
    onTimeDelivery: '94.2%',
    onTimeLabel: 'Exceeding SLA',
    avgLeadTime: '4.2 Days',
    leadTimeDrift: '+0.5d Drift',
    openPos: 14,
    pendingValue: '$24.5k Pending',
  },
  contact: {
    name: 'Sarah Jenkins',
    title: 'Director of Strategic Accounts',
    email: 's.jenkins@globallogistics.com',
    phone: '+1 (555) 902-3481',
    address: '1200 Logistics Way, Suite 400\nChicago, IL 60601',
    totalContacts: 12,
  },
})

/* 12 months of spend data (aug → jul), height as % */
const spendBars = [
  { month: 'Aug', value: 82,  heightPct: 40 },
  { month: 'Sep', value: 94,  heightPct: 55 },
  { month: 'Oct', value: 88,  heightPct: 45 },
  { month: 'Nov', value: 112, heightPct: 70 },
  { month: 'Dec', value: 142, heightPct: 90 },
  { month: 'Jan', value: 102, heightPct: 65 },
  { month: 'Feb', value: 90,  heightPct: 50 },
  { month: 'Mar', value: 120, heightPct: 75 },
  { month: 'Apr', value: 132, heightPct: 85 },
  { month: 'May', value: 154, heightPct: 95 },
  { month: 'Jun', value: 128, heightPct: 80 },
  { month: 'Jul', value: 148, heightPct: 90 },
]

const purchaseOrders = ref([
  { id: 'PO-2024-8841', issued: 'July 18, 2024', amount: '$12,450.00', delivery: 'July 22, 2024', status: 'IN TRANSIT' },
  { id: 'PO-2024-8839', issued: 'July 15, 2024', amount: '$4,120.00',  delivery: 'July 19, 2024', status: 'DELIVERED' },
  { id: 'PO-2024-8845', issued: 'July 14, 2024', amount: '$22,800.00', delivery: 'July 25, 2024', status: 'PROCESSING' },
  { id: 'PO-2024-8830', issued: 'July 02, 2024', amount: '$1,950.00',  delivery: 'July 05, 2024', status: 'DELAYED' },
])

function poStatusClass(status: string) {
  const map: Record<string, string> = {
    'IN TRANSIT': 'bg-surface-container-high text-on-secondary-container',
    'DELIVERED':  'bg-tertiary-container text-on-tertiary-container',
    'PROCESSING': 'bg-primary-container text-on-primary-container',
    'DELAYED':    'bg-error-container text-on-error-container',
  }
  return map[status] ?? 'bg-surface-container text-on-surface'
}

/* Recent bars get darker blue */
function barClass(index: number) {
  if (index >= 10) return 'bg-primary hover:opacity-90'
  if (index >= 7)  return 'bg-primary/20 hover:bg-primary/40'
  return 'bg-primary/10 hover:bg-primary/30'
}
</script>

<template>
  <div class="space-y-card-gap">

    <!-- Back nav -->
    <button
      @click="router.push('/vendors')"
      class="flex items-center gap-1 text-body-sm text-outline hover:text-primary transition-colors"
    >
      <span class="material-symbols-outlined text-[16px]">arrow_back</span>
      Back to Vendors
    </button>

    <!-- ── Vendor Header ──────────────────────────────────────────── -->
    <section class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
      <div class="flex items-center gap-6">
        <!-- Logo -->
        <div class="w-20 h-20 bg-surface-container-high rounded-xl flex items-center justify-center border border-outline-variant overflow-hidden flex-shrink-0">
          <span v-if="!vendor.logo" class="material-symbols-outlined text-[36px] text-outline">local_shipping</span>
          <img v-else :src="vendor.logo" :alt="vendor.name" class="w-full h-full object-cover" />
        </div>

        <div>
          <div class="flex items-center gap-3 flex-wrap">
            <h2 class="text-headline-md text-on-surface">{{ vendor.name }}</h2>
            <span v-if="vendor.verified" class="px-2 py-0.5 bg-tertiary-fixed text-on-tertiary-fixed-variant text-[10px] font-bold rounded uppercase tracking-wider">
              Verified Vendor
            </span>
          </div>
          <p class="text-body-md text-secondary mt-1">
            {{ vendor.category }}
            <span class="text-on-surface-variant"> • Vendor ID: {{ vendor.code }}</span>
          </p>
          <div class="flex items-center gap-2 mt-2">
            <div class="flex gap-0.5">
              <span
                v-for="i in 5"
                :key="i"
                class="material-symbols-outlined text-[18px]"
                :class="i <= vendor.rating ? 'text-amber-500' : 'text-outline-variant'"
                style="font-variation-settings: 'FILL' 1"
              >star</span>
            </div>
            <span class="text-tertiary font-bold text-label-md">{{ vendor.reliability }}% Reliability</span>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-wrap gap-3">
        <button class="flex items-center gap-2 px-4 py-2 bg-primary text-on-primary rounded-lg text-label-md hover:opacity-90 transition-opacity active:scale-95">
          <span class="material-symbols-outlined text-[14px]">add</span>
          Create New PO
        </button>
        <button class="flex items-center gap-2 px-4 py-2 border border-outline-variant text-on-surface rounded-lg text-label-md hover:bg-surface-variant transition-colors active:scale-95">
          <span class="material-symbols-outlined text-[14px] text-secondary">mail</span>
          Message Vendor
        </button>
        <button class="p-2 border border-outline-variant text-secondary rounded-lg hover:bg-surface-variant transition-colors">
          <span class="material-symbols-outlined text-[18px]">more_vert</span>
        </button>
      </div>
    </section>

    <!-- ── Bento Grid ─────────────────────────────────────────────── -->
    <div class="grid grid-cols-12 gap-card-gap">

      <!-- Left Column (8 cols) -->
      <div class="col-span-12 lg:col-span-8 space-y-card-gap">

        <!-- KPI Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-card-gap">
          <!-- Total Lifetime Spend -->
          <div class="bg-surface-container-lowest border border-outline-variant p-5 rounded-xl hover:shadow-sm transition-shadow">
            <p class="text-outline text-[11px] font-bold uppercase tracking-wider mb-2">Total Lifetime Spend</p>
            <h3 class="text-headline-sm text-on-surface">{{ vendor.kpis.totalSpend }}</h3>
            <div class="mt-2 flex items-center gap-1 text-tertiary">
              <span class="material-symbols-outlined text-[14px]">trending_up</span>
              <span class="text-[11px] font-bold">{{ vendor.kpis.totalSpendTrend }}</span>
            </div>
          </div>

          <!-- On-time Delivery -->
          <div class="bg-surface-container-lowest border border-outline-variant p-5 rounded-xl hover:shadow-sm transition-shadow">
            <p class="text-outline text-[11px] font-bold uppercase tracking-wider mb-2">On-time Delivery</p>
            <h3 class="text-headline-sm text-on-surface">{{ vendor.kpis.onTimeDelivery }}</h3>
            <div class="mt-2 flex items-center gap-1 text-tertiary">
              <span class="material-symbols-outlined text-[14px]">check_circle</span>
              <span class="text-[11px] font-bold">{{ vendor.kpis.onTimeLabel }}</span>
            </div>
          </div>

          <!-- Avg Lead Time -->
          <div class="bg-surface-container-lowest border border-outline-variant p-5 rounded-xl hover:shadow-sm transition-shadow">
            <p class="text-outline text-[11px] font-bold uppercase tracking-wider mb-2">Avg Lead Time</p>
            <h3 class="text-headline-sm text-on-surface">{{ vendor.kpis.avgLeadTime }}</h3>
            <div class="mt-2 flex items-center gap-1 text-amber-600">
              <span class="material-symbols-outlined text-[14px]">schedule</span>
              <span class="text-[11px] font-bold">{{ vendor.kpis.leadTimeDrift }}</span>
            </div>
          </div>

          <!-- Open POs -->
          <div class="bg-surface-container-lowest border border-outline-variant p-5 rounded-xl hover:shadow-sm transition-shadow">
            <p class="text-outline text-[11px] font-bold uppercase tracking-wider mb-2">Open POs</p>
            <h3 class="text-headline-sm text-on-surface">{{ vendor.kpis.openPos }}</h3>
            <div class="mt-2 flex items-center gap-1 text-primary">
              <span class="material-symbols-outlined text-[14px]">shopping_cart</span>
              <span class="text-[11px] font-bold">{{ vendor.kpis.pendingValue }}</span>
            </div>
          </div>
        </div>

        <!-- Spend Analysis Chart -->
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden">
          <div class="p-5 border-b border-outline-variant flex justify-between items-center">
            <h4 class="text-label-md text-on-surface flex items-center gap-2">
              <span class="material-symbols-outlined text-[16px] text-primary">analytics</span>
              HISTORICAL SPEND ANALYSIS
            </h4>
            <select
              v-model="spendPeriod"
              class="bg-surface-container-low border border-outline-variant rounded-lg text-label-md py-1 px-3 focus:outline-none focus:ring-2 focus:ring-primary/20"
            >
              <option>Last 12 Months</option>
              <option>Year to Date</option>
            </select>
          </div>

          <div class="p-6 h-64 relative flex items-end justify-between gap-2">
            <div
              v-for="(bar, i) in spendBars"
              :key="bar.month"
              class="flex-1 rounded-t transition-all duration-200 group relative"
              :class="barClass(i)"
              :style="{ height: bar.heightPct + '%' }"
            >
              <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-on-background text-surface-container-lowest text-[10px] px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-10">
                {{ bar.month }}: ${{ bar.value }}k
              </div>
            </div>
          </div>
          <div class="px-6 py-3 bg-surface-container border-t border-outline-variant flex justify-between">
            <span class="text-[10px] text-outline font-bold">{{ spendBars[0].month.toUpperCase() }} 2023</span>
            <span class="text-[10px] text-outline font-bold">{{ spendBars[spendBars.length - 1].month.toUpperCase() }} 2024</span>
          </div>
        </div>
      </div>

      <!-- Right Column (4 cols) -->
      <div class="col-span-12 lg:col-span-4 space-y-card-gap">

        <!-- Contact Card -->
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden shadow-sm">
          <div class="p-5 border-b border-outline-variant bg-surface-container">
            <h4 class="text-label-md text-on-surface uppercase tracking-wider">Contact Information</h4>
          </div>
          <div class="p-6 space-y-5">
            <div class="flex items-start gap-4">
              <div class="w-10 h-10 rounded-full bg-secondary-container flex items-center justify-center text-primary flex-shrink-0">
                <span class="material-symbols-outlined text-[18px]">person</span>
              </div>
              <div>
                <p class="text-label-md text-on-surface">{{ vendor.contact.name }}</p>
                <p class="text-body-sm text-secondary">{{ vendor.contact.title }}</p>
              </div>
            </div>
            <div class="space-y-3">
              <div class="flex items-center gap-3 text-body-md text-on-surface-variant">
                <span class="material-symbols-outlined text-[16px] text-primary">mail</span>
                <span class="break-all">{{ vendor.contact.email }}</span>
              </div>
              <div class="flex items-center gap-3 text-body-md text-on-surface-variant">
                <span class="material-symbols-outlined text-[16px] text-primary">phone</span>
                {{ vendor.contact.phone }}
              </div>
              <div class="flex items-start gap-3 text-body-md text-on-surface-variant">
                <span class="material-symbols-outlined text-[16px] text-primary mt-0.5">location_on</span>
                <span class="whitespace-pre-line">{{ vendor.contact.address }}</span>
              </div>
            </div>
            <div class="pt-4 border-t border-outline-variant">
              <button class="w-full text-center text-primary text-label-md py-2 hover:underline">
                View All {{ vendor.contact.totalContacts }} Contacts
              </button>
            </div>
          </div>
        </div>

        <!-- Operations Panel -->
        <div class="bg-on-background text-surface-container-lowest rounded-xl p-6 shadow-xl space-y-4">
          <h4 class="text-label-md uppercase tracking-widest opacity-60">Operations Panel</h4>
          <div class="space-y-2">
            <button class="w-full flex items-center gap-3 px-4 py-3 rounded-lg bg-white/10 hover:bg-white/20 transition-colors text-body-md">
              <span class="material-symbols-outlined text-[18px] text-primary-fixed">description</span>
              Edit Master Contract
            </button>
            <button class="w-full flex items-center gap-3 px-4 py-3 rounded-lg bg-white/10 hover:bg-white/20 transition-colors text-body-md">
              <span class="material-symbols-outlined text-[18px] text-primary-fixed">download</span>
              Download Documents
            </button>
            <button class="w-full flex items-center gap-3 px-4 py-3 rounded-lg bg-white/10 hover:bg-white/20 transition-colors text-body-md">
              <span class="material-symbols-outlined text-[18px] text-primary-fixed">history</span>
              View Audit Logs
            </button>
            <button class="w-full flex items-center gap-3 px-4 py-3 rounded-lg bg-error-container text-on-error-container hover:opacity-90 transition-opacity text-body-md font-bold mt-2">
              <span class="material-symbols-outlined text-[18px]">block</span>
              Suspend Vendor
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Active Purchase Orders Table ──────────────────────────── -->
    <section class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden">
      <div class="p-5 border-b border-outline-variant flex justify-between items-center">
        <h4 class="text-label-md text-on-surface uppercase tracking-wider">Active Purchase Orders</h4>
        <button class="text-primary text-label-md flex items-center gap-1 hover:underline">
          View All
          <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
        </button>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-surface-container">
              <th class="px-6 py-3 text-label-md text-on-surface border-b border-outline-variant">PO NUMBER</th>
              <th class="px-6 py-3 text-label-md text-on-surface border-b border-outline-variant">ISSUE DATE</th>
              <th class="px-6 py-3 text-label-md text-on-surface border-b border-outline-variant">TOTAL AMOUNT</th>
              <th class="px-6 py-3 text-label-md text-on-surface border-b border-outline-variant">EST. DELIVERY</th>
              <th class="px-6 py-3 text-label-md text-on-surface border-b border-outline-variant">STATUS</th>
              <th class="px-6 py-3 border-b border-outline-variant"></th>
            </tr>
          </thead>
          <tbody class="text-body-md">
            <tr
              v-for="po in purchaseOrders"
              :key="po.id"
              class="data-table-row border-b border-outline-variant last:border-0"
            >
              <td class="px-6 py-4 text-mono-data text-primary font-bold">{{ po.id }}</td>
              <td class="px-6 py-4 text-secondary">{{ po.issued }}</td>
              <td class="px-6 py-4 font-bold text-on-surface">{{ po.amount }}</td>
              <td class="px-6 py-4 text-secondary">{{ po.delivery }}</td>
              <td class="px-6 py-4">
                <span class="px-3 py-1 rounded-full text-[11px] font-bold" :class="poStatusClass(po.status)">
                  {{ po.status }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <span class="material-symbols-outlined text-outline text-[18px]">chevron_right</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

  </div>
</template>
