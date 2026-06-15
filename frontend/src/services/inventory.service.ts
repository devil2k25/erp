import api from './api'

export const InventoryService = {
  listItems: (params?: Record<string, unknown>) => api.get('/inventory/items', { params }),
  getStockSummary: (productId: string) => api.get('/inventory/stock-summary', { params: { product_id: productId } }),
  getLowStockAlerts: () => api.get('/inventory/low-stock-alerts'),
  listTransactions: (params?: Record<string, unknown>) => api.get('/inventory/transactions', { params }),
  createTransaction: (data: Record<string, unknown>) => api.post('/inventory/transactions', data),
}

export const GrnService = {
  list: (params?: Record<string, unknown>) => api.get('/inventory/grn', { params }),
  get: (id: string) => api.get(`/inventory/grn/${id}`),
  create: (data: Record<string, unknown>) => api.post('/inventory/grn', data),
  update: (id: string, data: Record<string, unknown>) => api.put(`/inventory/grn/${id}`, data),
  remove: (id: string) => api.delete(`/inventory/grn/${id}`),
  approve: (id: string) => api.post(`/inventory/grn/${id}/approve`),
}
