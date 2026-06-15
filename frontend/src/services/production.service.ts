import api from './api'

export const ProductionPlanService = {
  list: (params?: Record<string, unknown>) => api.get('/production/plans', { params }),
  get: (id: string) => api.get(`/production/plans/${id}`),
  create: (data: Record<string, unknown>) => api.post('/production/plans', data),
  update: (id: string, data: Record<string, unknown>) => api.put(`/production/plans/${id}`, data),
  remove: (id: string) => api.delete(`/production/plans/${id}`),
  approve: (id: string) => api.post(`/production/plans/${id}/approve`),
  start: (id: string) => api.post(`/production/plans/${id}/start`),
}

export const ProductionEntryService = {
  list: (params?: Record<string, unknown>) => api.get('/production/entries', { params }),
  get: (id: string) => api.get(`/production/entries/${id}`),
  create: (data: Record<string, unknown>) => api.post('/production/entries', data),
  update: (id: string, data: Record<string, unknown>) => api.put(`/production/entries/${id}`, data),
  remove: (id: string) => api.delete(`/production/entries/${id}`),
  oeeSummary: (params?: Record<string, unknown>) => api.get('/production/oee-summary', { params }),
}
