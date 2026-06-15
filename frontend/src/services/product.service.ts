import api from './api'
import type { ProductFilters } from '@/types/product.types'

export const ProductService = {
  list: (params?: ProductFilters) => api.get('/master/products', { params }),
  get: (id: string) => api.get(`/master/products/${id}`),
  create: (data: Record<string, unknown>) => api.post('/master/products', data),
  update: (id: string, data: Record<string, unknown>) => api.put(`/master/products/${id}`, data),
  remove: (id: string) => api.delete(`/master/products/${id}`),
  getBom: (productId: string) => api.get(`/master/products/${productId}/bom`),
}

export const CategoryService = {
  list: (params?: Record<string, unknown>) => api.get('/master/categories', { params }),
  create: (data: Record<string, unknown>) => api.post('/master/categories', data),
  update: (id: string, data: Record<string, unknown>) => api.put(`/master/categories/${id}`, data),
  remove: (id: string) => api.delete(`/master/categories/${id}`),
}

export const UnitService = {
  list: (params?: Record<string, unknown>) => api.get('/master/units', { params }),
  create: (data: Record<string, unknown>) => api.post('/master/units', data),
  update: (id: string, data: Record<string, unknown>) => api.put(`/master/units/${id}`, data),
}

export const WarehouseService = {
  list: (params?: Record<string, unknown>) => api.get('/master/warehouses', { params }),
  create: (data: Record<string, unknown>) => api.post('/master/warehouses', data),
  update: (id: string, data: Record<string, unknown>) => api.put(`/master/warehouses/${id}`, data),
}

export const VendorService = {
  list: (params?: Record<string, unknown>) => api.get('/master/vendors', { params }),
  create: (data: Record<string, unknown>) => api.post('/master/vendors', data),
  update: (id: string, data: Record<string, unknown>) => api.put(`/master/vendors/${id}`, data),
}

export const MachineService = {
  list: (params?: Record<string, unknown>) => api.get('/master/machines', { params }),
  create: (data: Record<string, unknown>) => api.post('/master/machines', data),
  update: (id: string, data: Record<string, unknown>) => api.put(`/master/machines/${id}`, data),
}

export const BomService = {
  forProduct: (productId: string) => api.get(`/master/products/${productId}/bom`),
  create: (data: Record<string, unknown>) => api.post('/master/bom', data),
  update: (id: string, data: Record<string, unknown>) => api.put(`/master/bom/${id}`, data),
  remove: (id: string) => api.delete(`/master/bom/${id}`),
}
