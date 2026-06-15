<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useProductStore } from '@/stores/masterdata/product.store'
import type { Product } from '@/types/product.types'

const productStore = useProductStore()
const showModal = ref(false)
const editingProduct = ref<Product | null>(null)
const search = ref('')
const filterType = ref('')

const form = ref({
  name: '', sku: '', type: 'raw_material', unit_id: '', category_id: '',
  cost_price: 0, selling_price: 0, min_stock_level: 0, reorder_point: 0,
  lead_time_days: 0, description: '', is_trackable: true,
})

const productTypes = ['raw_material', 'semi_finished', 'finished_good', 'consumable', 'service']

onMounted(() => productStore.fetchProducts())

async function search_products() {
  await productStore.fetchProducts({ search: search.value, type: filterType.value || undefined })
}

function openCreate() {
  editingProduct.value = null
  form.value = { name: '', sku: '', type: 'raw_material', unit_id: '', category_id: '', cost_price: 0, selling_price: 0, min_stock_level: 0, reorder_point: 0, lead_time_days: 0, description: '', is_trackable: true }
  showModal.value = true
}

function openEdit(p: Product) {
  editingProduct.value = p
  form.value = { ...p } as any
  showModal.value = true
}

async function save() {
  if (editingProduct.value) {
    await productStore.updateProduct(editingProduct.value.id, form.value)
  } else {
    await productStore.createProduct(form.value)
  }
  showModal.value = false
}

async function remove(id: string) {
  if (confirm('Delete this product?')) await productStore.deleteProduct(id)
}

const typeColors: Record<string, string> = {
  raw_material: 'bg-blue-900/50 text-blue-400',
  finished_good: 'bg-green-900/50 text-green-400',
  semi_finished: 'bg-yellow-900/50 text-yellow-400',
  consumable: 'bg-purple-900/50 text-purple-400',
  service: 'bg-slate-800 text-slate-400',
}
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-white">Products</h1>
      <button @click="openCreate" class="bg-blue-600 hover:bg-blue-500 text-white text-sm px-4 py-2 rounded-lg transition-colors">+ Add Product</button>
    </div>

    <!-- Filters -->
    <div class="flex gap-3">
      <input
        v-model="search"
        @input="search_products"
        type="text"
        placeholder="Search by name or SKU..."
        class="bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 w-64"
      />
      <select
        v-model="filterType"
        @change="search_products"
        class="bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-sm text-white focus:outline-none focus:border-blue-500"
      >
        <option value="">All Types</option>
        <option v-for="t in productTypes" :key="t" :value="t">{{ t.replace('_', ' ') }}</option>
      </select>
    </div>

    <!-- Table -->
    <div class="bg-slate-900 rounded-xl border border-slate-800 overflow-hidden">
      <div v-if="productStore.loading" class="p-8 text-center text-slate-500">Loading...</div>
      <table v-else class="w-full text-sm">
        <thead>
          <tr class="border-b border-slate-800 text-xs text-slate-500 uppercase tracking-wider">
            <th class="text-left px-4 py-3">Product</th>
            <th class="text-left px-4 py-3">SKU</th>
            <th class="text-left px-4 py-3">Type</th>
            <th class="text-right px-4 py-3">Cost</th>
            <th class="text-right px-4 py-3">Min Stock</th>
            <th class="text-center px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="product in productStore.products"
            :key="product.id"
            class="border-b border-slate-800/50 hover:bg-slate-800/30 transition-colors"
          >
            <td class="px-4 py-3">
              <p class="text-white font-medium">{{ product.name }}</p>
              <p class="text-xs text-slate-500">{{ product.unit?.symbol }}</p>
            </td>
            <td class="px-4 py-3 text-slate-400 font-mono text-xs">{{ product.sku }}</td>
            <td class="px-4 py-3">
              <span :class="['text-xs px-2 py-0.5 rounded capitalize', typeColors[product.type] ?? 'bg-slate-800 text-slate-400']">
                {{ product.type.replace('_', ' ') }}
              </span>
            </td>
            <td class="px-4 py-3 text-right text-white">₹{{ product.cost_price?.toLocaleString() }}</td>
            <td class="px-4 py-3 text-right text-slate-400">{{ product.min_stock_level }}</td>
            <td class="px-4 py-3 text-center">
              <button @click="openEdit(product)" class="text-xs text-blue-400 hover:text-blue-300 mr-3">Edit</button>
              <button @click="remove(product.id)" class="text-xs text-red-400 hover:text-red-300">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="!productStore.loading && !productStore.products.length" class="p-8 text-center text-slate-500">
        No products found. <button @click="openCreate" class="text-blue-400 underline">Add your first product.</button>
      </div>
    </div>

    <!-- Pagination -->
    <div class="flex items-center justify-between text-sm text-slate-500">
      <span>{{ productStore.pagination.total }} products</span>
      <div class="flex gap-2">
        <button
          v-for="p in productStore.pagination.last_page"
          :key="p"
          @click="productStore.fetchProducts({ page: p })"
          class="px-3 py-1 rounded"
          :class="p === productStore.pagination.current_page ? 'bg-blue-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-white'"
        >{{ p }}</button>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
      <div class="bg-slate-900 rounded-2xl p-6 w-full max-w-lg border border-slate-800 max-h-[90vh] overflow-y-auto">
        <h2 class="text-lg font-bold text-white mb-4">{{ editingProduct ? 'Edit Product' : 'New Product' }}</h2>
        <form @submit.prevent="save" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
              <label class="block text-xs text-slate-400 mb-1">Name *</label>
              <input v-model="form.name" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" />
            </div>
            <div>
              <label class="block text-xs text-slate-400 mb-1">SKU *</label>
              <input v-model="form.sku" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" />
            </div>
            <div>
              <label class="block text-xs text-slate-400 mb-1">Type *</label>
              <select v-model="form.type" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white">
                <option v-for="t in productTypes" :key="t" :value="t">{{ t.replace('_', ' ') }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs text-slate-400 mb-1">Cost Price</label>
              <input v-model.number="form.cost_price" type="number" min="0" step="0.01" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" />
            </div>
            <div>
              <label class="block text-xs text-slate-400 mb-1">Selling Price</label>
              <input v-model.number="form.selling_price" type="number" min="0" step="0.01" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" />
            </div>
            <div>
              <label class="block text-xs text-slate-400 mb-1">Min Stock Level</label>
              <input v-model.number="form.min_stock_level" type="number" min="0" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" />
            </div>
            <div>
              <label class="block text-xs text-slate-400 mb-1">Reorder Point</label>
              <input v-model.number="form.reorder_point" type="number" min="0" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" />
            </div>
          </div>
          <div class="flex gap-3 justify-end pt-2">
            <button type="button" @click="showModal = false" class="px-4 py-2 text-sm text-slate-400 hover:text-white">Cancel</button>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-500 text-white rounded-lg">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
