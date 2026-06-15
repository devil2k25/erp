<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useGrnStore } from '@/stores/inventory/grn.store'

const grnStore = useGrnStore()
const showModal = ref(false)
const statusFilter = ref('')

const form = ref({
  vendor_id: '',
  warehouse_id: '',
  received_date: new Date().toISOString().split('T')[0],
  notes: '',
  items: [{ product_id: '', quantity_ordered: 1, quantity_received: 1, unit_id: '', unit_price: 0, batch_number: '' }],
})

function addItem() {
  form.value.items.push({ product_id: '', quantity_ordered: 1, quantity_received: 1, unit_id: '', unit_price: 0, batch_number: '' })
}

function removeItem(i: number) {
  form.value.items.splice(i, 1)
}

async function save() {
  await grnStore.createGrn(form.value as any)
  showModal.value = false
}

async function approve(id: string) {
  if (confirm('Approve this GRN? Inventory will be updated.')) {
    await grnStore.approveGrn(id)
  }
}

onMounted(() => grnStore.fetchGrns())

const statusColors: Record<string, string> = {
  approved: 'bg-green-900/50 text-green-400',
  received: 'bg-yellow-900/50 text-yellow-400',
  rejected: 'bg-red-900/50 text-red-400',
  draft: 'bg-slate-800 text-slate-400',
  quality_check: 'bg-blue-900/50 text-blue-400',
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-white">Goods Receipt Notes</h1>
      <button @click="showModal = true" class="bg-blue-600 hover:bg-blue-500 text-white text-sm px-4 py-2 rounded-lg">+ New GRN</button>
    </div>

    <div class="bg-slate-900 rounded-xl border border-slate-800 overflow-hidden">
      <div v-if="grnStore.loading" class="p-8 text-center text-slate-500">Loading...</div>
      <table v-else class="w-full text-sm">
        <thead>
          <tr class="border-b border-slate-800 text-xs text-slate-500 uppercase">
            <th class="text-left px-4 py-3">GRN #</th>
            <th class="text-left px-4 py-3">Vendor</th>
            <th class="text-left px-4 py-3">Warehouse</th>
            <th class="text-left px-4 py-3">Date</th>
            <th class="text-right px-4 py-3">Amount</th>
            <th class="text-center px-4 py-3">Status</th>
            <th class="text-center px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="grn in grnStore.grns" :key="grn.id" class="border-b border-slate-800/50 hover:bg-slate-800/30">
            <td class="px-4 py-3 text-blue-400 font-mono text-xs">{{ grn.grn_number }}</td>
            <td class="px-4 py-3 text-white">{{ grn.vendor?.name ?? '—' }}</td>
            <td class="px-4 py-3 text-slate-400">{{ grn.warehouse?.name ?? '—' }}</td>
            <td class="px-4 py-3 text-slate-400">{{ grn.received_date }}</td>
            <td class="px-4 py-3 text-right text-white">₹{{ grn.total_amount?.toLocaleString() }}</td>
            <td class="px-4 py-3 text-center">
              <span :class="['text-xs px-2 py-0.5 rounded', statusColors[grn.status] ?? 'bg-slate-800 text-slate-400']">{{ grn.status }}</span>
            </td>
            <td class="px-4 py-3 text-center">
              <button v-if="grn.status === 'received'" @click="approve(grn.id)" class="text-xs text-green-400 hover:text-green-300">Approve</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Create GRN Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
      <div class="bg-slate-900 rounded-2xl p-6 w-full max-w-2xl border border-slate-800 max-h-[90vh] overflow-y-auto">
        <h2 class="text-lg font-bold text-white mb-4">New GRN</h2>
        <form @submit.prevent="save" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs text-slate-400 mb-1">Vendor ID</label>
              <input v-model="form.vendor_id" required placeholder="Vendor UUID" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" />
            </div>
            <div>
              <label class="block text-xs text-slate-400 mb-1">Warehouse ID</label>
              <input v-model="form.warehouse_id" required placeholder="Warehouse UUID" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" />
            </div>
            <div>
              <label class="block text-xs text-slate-400 mb-1">Received Date</label>
              <input v-model="form.received_date" type="date" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" />
            </div>
          </div>

          <div>
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-sm font-medium text-slate-300">Line Items</h3>
              <button type="button" @click="addItem" class="text-xs text-blue-400 hover:text-blue-300">+ Add Item</button>
            </div>
            <div v-for="(item, i) in form.items" :key="i" class="bg-slate-800 rounded-lg p-3 mb-2">
              <div class="grid grid-cols-3 gap-2">
                <input v-model="item.product_id" placeholder="Product UUID" class="bg-slate-700 border border-slate-600 rounded px-2 py-1 text-xs text-white" />
                <input v-model.number="item.quantity_received" type="number" min="0" placeholder="Qty Received" class="bg-slate-700 border border-slate-600 rounded px-2 py-1 text-xs text-white" />
                <input v-model.number="item.unit_price" type="number" min="0" step="0.01" placeholder="Unit Price" class="bg-slate-700 border border-slate-600 rounded px-2 py-1 text-xs text-white" />
              </div>
              <div class="flex justify-end mt-1">
                <button type="button" @click="removeItem(i)" class="text-xs text-red-400">Remove</button>
              </div>
            </div>
          </div>

          <div class="flex gap-3 justify-end">
            <button type="button" @click="showModal = false" class="px-4 py-2 text-sm text-slate-400 hover:text-white">Cancel</button>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-500 text-white rounded-lg">Create GRN</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
