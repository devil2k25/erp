import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { GrnHeader } from '@/types/inventory.types'
import { GrnService } from '@/services/inventory.service'

export const useGrnStore = defineStore('grn', () => {
  const grns = ref<GrnHeader[]>([])
  const currentGrn = ref<GrnHeader | null>(null)
  const loading = ref(false)
  const pagination = ref({ current_page: 1, last_page: 1, per_page: 15, total: 0 })

  async function fetchGrns(params?: Record<string, unknown>) {
    loading.value = true
    try {
      const response: any = await GrnService.list(params)
      grns.value = response.data.data ?? response.data
      if (response.data.meta) pagination.value = response.data.meta
    } finally {
      loading.value = false
    }
  }

  async function fetchGrn(id: string) {
    const response: any = await GrnService.get(id)
    currentGrn.value = response.data
    return response.data
  }

  async function createGrn(data: Record<string, unknown>) {
    const response: any = await GrnService.create(data)
    grns.value.unshift(response.data)
    return response.data
  }

  async function approveGrn(id: string) {
    const response: any = await GrnService.approve(id)
    const idx = grns.value.findIndex((g) => g.id === id)
    if (idx !== -1) grns.value[idx] = response.data
    return response.data
  }

  return { grns, currentGrn, loading, pagination, fetchGrns, fetchGrn, createGrn, approveGrn }
})
