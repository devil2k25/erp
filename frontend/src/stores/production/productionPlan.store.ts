import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { ProductionPlan } from '@/types/production.types'
import { ProductionPlanService } from '@/services/production.service'

export const useProductionPlanStore = defineStore('productionPlans', () => {
  const plans = ref<ProductionPlan[]>([])
  const currentPlan = ref<ProductionPlan | null>(null)
  const loading = ref(false)
  const pagination = ref({ current_page: 1, last_page: 1, per_page: 15, total: 0 })

  async function fetchPlans(params?: Record<string, unknown>) {
    loading.value = true
    try {
      const response: any = await ProductionPlanService.list(params)
      plans.value = response.data.data ?? response.data
      if (response.meta) pagination.value = response.meta
    } finally {
      loading.value = false
    }
  }

  async function createPlan(data: Record<string, unknown>) {
    const response: any = await ProductionPlanService.create(data)
    plans.value.unshift(response.data)
    return response.data
  }

  async function approvePlan(id: string) {
    const response: any = await ProductionPlanService.approve(id)
    const idx = plans.value.findIndex((p) => p.id === id)
    if (idx !== -1) plans.value[idx] = response.data
    return response.data
  }

  async function startPlan(id: string) {
    const response: any = await ProductionPlanService.start(id)
    const idx = plans.value.findIndex((p) => p.id === id)
    if (idx !== -1) plans.value[idx] = response.data
    return response.data
  }

  return { plans, currentPlan, loading, pagination, fetchPlans, createPlan, approvePlan, startPlan }
})
