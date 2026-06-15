import axios, { type AxiosInstance } from 'axios'

const api: AxiosInstance = axios.create({
  baseURL: (import.meta.env.VITE_API_URL ?? 'http://localhost') + '/api/v1',
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('erp_token')
  const orgSlug = localStorage.getItem('erp_org_slug')

  if (token) config.headers.Authorization = `Bearer ${token}`
  if (orgSlug) config.headers['X-Organization-Slug'] = orgSlug

  return config
})

api.interceptors.response.use(
  (response) => response.data,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('erp_token')
      localStorage.removeItem('erp_org_slug')
      window.location.href = '/login'
    }
    return Promise.reject(error.response?.data ?? error)
  },
)

export default api
