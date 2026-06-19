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
  raw_material:  'bg-primary-container text-on-primary-container',
  finished_good: 'bg-tertiary-container text-on-tertiary-container',
  semi_finished: 'bg-secondary-container text-on-secondary-container',
  consumable:    'bg-surface-container-high text-on-surface-variant',
  service:       'bg-surface-container text-outline',
}
</script>

<template>
  <div class="space-y-card-gap">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-headline-sm text-on-surface">Products</h1>
        <p class="text-body-md text-outline mt-0.5">Manage your product catalog and inventory items</p>
      </div>
      <button @click="openCreate" class="flex items-center gap-2 px-4 py-2 bg-primary text-on-primary rounded-lg text-label-md hover:opacity-90 transition-opacity">
        <span class="material-symbols-outlined text-[16px]">add</span>
        Add Product
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl overflow-hidden">
      <div class="p-4 border-b border-outline-variant flex flex-wrap gap-3 items-center">
        <div class="relative">
          <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[16px]">search</span>
          <input
            v-model="search"
            @input="search_products"
            type="text"
            placeholder="Search by name or SKU…"
            class="bg-surface-container-low border border-outline-variant rounded-lg pl-9 pr-4 py-2 text-body-sm text-on-surface placeholder-outline focus:outline-none focus:ring-2 focus:ring-primary/20 w-64"
          />
        </div>
        <select
          v-model="filterType"
          @change="search_products"
          class="bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-body-sm text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20"
        >
          <option value="">All Types</option>
          <option v-for="t in productTypes" :key="t" :value="t">{{ t.replace(/_/g, ' ') }}</option>
        </select>
        <span class="text-body-sm text-outline ml-auto">{{ productStore.pagination.total }} products</span>
      </div>

      <!-- Table -->
      <div v-if="productStore.loading" class="p-8 text-center text-outline">
        <span class="material-symbols-outlined text-[32px] text-outline-variant animate-spin">progress_activity</span>
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-surface-container">
              <th class="px-6 py-3 text-label-md text-on-surface border-b border-outline-variant">PRODUCT</th>
              <th class="px-6 py-3 text-label-md text-on-surface border-b border-outline-variant">SKU</th>
              <th class="px-6 py-3 text-label-md text-on-surface border-b border-outline-variant">TYPE</th>
              <th class="px-6 py-3 text-label-md text-on-surface border-b border-outline-variant text-right">COST</th>
              <th class="px-6 py-3 text-label-md text-on-surface border-b border-outline-variant text-right">MIN STOCK</th>
              <th class="px-6 py-3 border-b border-outline-variant"></th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="product in productStore.products"
              :key="product.id"
              class="data-table-row border-b border-outline-variant last:border-0"
            >
              <td class="px-6 py-4">
                <p class="text-body-md font-semibold text-on-surface">{{ product.name }}</p>
                <p class="text-body-sm text-outline">{{ product.unit?.symbol }}</p>
              </td>
              <td class="px-6 py-4 text-mono-data text-secondary">{{ product.sku }}</td>
              <td class="px-6 py-4">
                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold capitalize" :class="typeColors[product.type] ?? 'bg-surface-container text-outline'">
                  {{ product.type.replace(/_/g, ' ') }}
                </span>
              </td>
              <td class="px-6 py-4 text-right text-body-md font-bold text-on-surface">₹{{ product.cost_price?.toLocaleString() }}</td>
              <td class="px-6 py-4 text-right text-body-md text-secondary">{{ product.min_stock_level }}</td>
              <td class="px-6 py-4 text-right">
                <button @click.stop="openEdit(product)" class="text-body-sm text-primary hover:underline mr-4">Edit</button>
                <button @click.stop="remove(product.id)" class="text-body-sm text-error hover:underline">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-if="!productStore.loading && !productStore.products.length" class="p-12 text-center text-outline">
          No products found.
          <button @click="openCreate" class="text-primary underline ml-1">Add your first product.</button>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="productStore.pagination.last_page > 1" class="px-6 py-4 border-t border-outline-variant flex items-center justify-between">
        <span class="text-body-sm text-outline">{{ productStore.pagination.total }} products</span>
        <div class="flex gap-1.5">
          <button
            v-for="p in productStore.pagination.last_page"
            :key="p"
            @click="productStore.fetchProducts({ page: p })"
            class="px-3 py-1 rounded-lg text-body-sm"
            :class="p === productStore.pagination.current_page
              ? 'bg-primary text-on-primary'
              : 'bg-surface-container text-on-surface-variant hover:bg-surface-container-high'"
          >{{ p }}</button>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-on-background/50 flex items-center justify-center z-50 p-4">
      <div class="bg-surface-container-lowest rounded-xl p-6 w-full max-w-lg border border-outline-variant shadow-xl max-h-[90vh] overflow-y-auto custom-scrollbar">
        <h2 class="text-headline-sm text-on-surface mb-5">{{ editingProduct ? 'Edit Product' : 'New Product' }}</h2>
        <form @submit.prevent="save" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
              <label class="block text-label-md text-on-surface mb-1.5">Name *</label>
              <input v-model="form.name" required class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20" />
            </div>
            <div>
              <label class="block text-label-md text-on-surface mb-1.5">SKU *</label>
              <input v-model="form.sku" required class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20" />
            </div>
            <div>
              <label class="block text-label-md text-on-surface mb-1.5">Type *</label>
              <select v-model="form.type" class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20">
                <option v-for="t in productTypes" :key="t" :value="t">{{ t.replace(/_/g, ' ') }}</option>
              </select>
            </div>
            <div>
              <label class="block text-label-md text-on-surface mb-1.5">Cost Price</label>
              <input v-model.number="form.cost_price" type="number" min="0" step="0.01" class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20" />
            </div>
            <div>
              <label class="block text-label-md text-on-surface mb-1.5">Selling Price</label>
              <input v-model.number="form.selling_price" type="number" min="0" step="0.01" class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20" />
            </div>
            <div>
              <label class="block text-label-md text-on-surface mb-1.5">Min Stock Level</label>
              <input v-model.number="form.min_stock_level" type="number" min="0" class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20" />
            </div>
            <div>
              <label class="block text-label-md text-on-surface mb-1.5">Reorder Point</label>
              <input v-model.number="form.reorder_point" type="number" min="0" class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-3 py-2 text-body-md text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/20" />
            </div>
          </div>
          <div class="flex gap-3 justify-end pt-2">
            <button type="button" @click="showModal = false" class="px-4 py-2 text-body-md text-outline hover:text-on-surface border border-outline-variant rounded-lg">Cancel</button>
            <button type="submit" class="px-4 py-2 text-body-md bg-primary text-on-primary rounded-lg hover:opacity-90">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
