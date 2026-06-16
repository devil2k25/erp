import axios from 'axios'

const adminApi = axios.create({
  baseURL: `${import.meta.env.VITE_API_URL ?? ''}/api/v1/admin`,
  headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
})

adminApi.interceptors.request.use((config) => {
  const token = localStorage.getItem('erp_admin_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

adminApi.interceptors.response.use(
  (r) => r.data,
  (err) => {
    if (err.response?.status === 401) {
      localStorage.removeItem('erp_admin_token')
      window.location.href = '/admin/login'
    }
    return Promise.reject(err.response?.data ?? err)
  },
)

export default adminApi
