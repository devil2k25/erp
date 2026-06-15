import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { Product, ProductFilters } from '@/types/product.types'
import { ProductService } from '@/services/product.service'

export const useProductStore = defineStore('products', () => {
  const products = ref<Product[]>([])
  const currentProduct = ref<Product | null>(null)
  const loading = ref(false)
  const pagination = ref({ current_page: 1, last_page: 1, per_page: 15, total: 0 })

  const rawMaterials = computed(() => products.value.filter((p) => p.type === 'raw_material'))
  const finishedGoods = computed(() => products.value.filter((p) => p.type === 'finished_good'))

  async function fetchProducts(filters?: ProductFilters) {
    loading.value = true
    try {
      const response: any = await ProductService.list(filters)
      products.value = response.data
      pagination.value = response.meta
    } finally {
      loading.value = false
    }
  }

  async function fetchProduct(id: string) {
    const response: any = await ProductService.get(id)
    currentProduct.value = response.data
    return response.data
  }

  async function createProduct(payload: Partial<Product>) {
    const response: any = await ProductService.create(payload as Record<string, unknown>)
    products.value.unshift(response.data)
    return response.data
  }

  async function updateProduct(id: string, payload: Partial<Product>) {
    const response: any = await ProductService.update(id, payload as Record<string, unknown>)
    const idx = products.value.findIndex((p) => p.id === id)
    if (idx !== -1) products.value[idx] = response.data
    return response.data
  }

  async function deleteProduct(id: string) {
    await ProductService.remove(id)
    products.value = products.value.filter((p) => p.id !== id)
  }

  return {
    products, currentProduct, loading, pagination,
    rawMaterials, finishedGoods,
    fetchProducts, fetchProduct, createProduct, updateProduct, deleteProduct,
  }
})
