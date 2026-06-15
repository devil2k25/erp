import { createRouter, createWebHistory } from 'vue-router'
import { setupGuards } from './guards'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('@/modules/auth/views/LoginView.vue'),
      meta: { requiresGuest: true },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/modules/auth/views/RegisterView.vue'),
      meta: { requiresGuest: true },
    },
    {
      path: '/',
      component: () => import('@/components/layout/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          redirect: '/dashboard',
        },
        {
          path: 'dashboard',
          name: 'dashboard.owner',
          component: () => import('@/modules/dashboard/views/OwnerDashboard.vue'),
          meta: { requiresAuth: true },
        },
        {
          path: 'dashboard/production',
          name: 'dashboard.production',
          component: () => import('@/modules/dashboard/views/ProductionDashboard.vue'),
          meta: { requiresAuth: true },
        },
        {
          path: 'dashboard/inventory',
          name: 'dashboard.inventory',
          component: () => import('@/modules/dashboard/views/InventoryDashboard.vue'),
          meta: { requiresAuth: true },
        },
        // Master Data
        {
          path: 'products',
          name: 'products',
          component: () => import('@/modules/masterdata/views/ProductsView.vue'),
          meta: { requiresAuth: true },
        },
        {
          path: 'products/:id',
          name: 'product.detail',
          component: () => import('@/modules/masterdata/views/ProductDetailView.vue'),
          meta: { requiresAuth: true },
        },
        {
          path: 'warehouses',
          name: 'warehouses',
          component: () => import('@/modules/masterdata/views/WarehousesView.vue'),
          meta: { requiresAuth: true },
        },
        {
          path: 'machines',
          name: 'machines',
          component: () => import('@/modules/masterdata/views/MachinesView.vue'),
          meta: { requiresAuth: true },
        },
        // Inventory
        {
          path: 'inventory',
          name: 'inventory',
          component: () => import('@/modules/inventory/views/InventoryView.vue'),
          meta: { requiresAuth: true },
        },
        {
          path: 'grn',
          name: 'grn',
          component: () => import('@/modules/inventory/views/GrnView.vue'),
          meta: { requiresAuth: true },
        },
        // Production
        {
          path: 'production',
          name: 'production.plans',
          component: () => import('@/modules/production/views/ProductionPlanView.vue'),
          meta: { requiresAuth: true },
        },
        {
          path: 'production/floor',
          name: 'production.floor',
          component: () => import('@/modules/production/views/ProductionFloorView.vue'),
          meta: { requiresAuth: true },
        },
        {
          path: 'production/oee',
          name: 'production.oee',
          component: () => import('@/modules/production/views/OeeDashboard.vue'),
          meta: { requiresAuth: true },
        },
        // Workers
        {
          path: 'workers',
          name: 'workers',
          component: () => import('@/modules/worker/views/WorkerProductivityView.vue'),
          meta: { requiresAuth: true },
        },
        {
          path: 'attendance',
          name: 'attendance',
          component: () => import('@/modules/worker/views/AttendanceView.vue'),
          meta: { requiresAuth: true },
        },
      ],
    },
    {
      path: '/:pathMatch(.*)*',
      redirect: '/',
    },
  ],
})

setupGuards(router)

export default router
