import { createRouter, createWebHistory } from 'vue-router'
import { setupGuards } from './guards'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    // ─── User auth ───────────────────────────────────────────────
    {
      path: '/login',
      name: 'login',
      component: () => import('@/modules/auth/views/LoginView.vue'),
      meta: { requiresGuest: true },
    },

    // ─── Admin panel ─────────────────────────────────────────────
    {
      path: '/admin/login',
      name: 'admin.login',
      component: () => import('@/modules/admin/views/AdminLoginView.vue'),
      meta: { requiresAdminGuest: true },
    },
    {
      path: '/admin/organizations',
      name: 'admin.organizations',
      component: () => import('@/modules/admin/views/AdminOrganizationsView.vue'),
      meta: { requiresAdmin: true },
    },
    {
      path: '/admin',
      redirect: '/admin/organizations',
    },

    // ─── Main app (tenant users) ──────────────────────────────────
    {
      path: '/',
      component: () => import('@/components/layout/AppLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        { path: '', redirect: '/dashboard' },
        {
          path: 'dashboard',
          name: 'dashboard.owner',
          component: () => import('@/modules/dashboard/views/OwnerDashboard.vue'),
        },
        {
          path: 'dashboard/production',
          name: 'dashboard.production',
          component: () => import('@/modules/dashboard/views/ProductionDashboard.vue'),
        },
        {
          path: 'dashboard/inventory',
          name: 'dashboard.inventory',
          component: () => import('@/modules/dashboard/views/InventoryDashboard.vue'),
        },
        {
          path: 'products',
          name: 'products',
          component: () => import('@/modules/masterdata/views/ProductsView.vue'),
        },
        {
          path: 'products/:id',
          name: 'product.detail',
          component: () => import('@/modules/masterdata/views/ProductDetailView.vue'),
        },
        {
          path: 'warehouses',
          name: 'warehouses',
          component: () => import('@/modules/masterdata/views/WarehousesView.vue'),
        },
        {
          path: 'machines',
          name: 'machines',
          component: () => import('@/modules/masterdata/views/MachinesView.vue'),
        },
        {
          path: 'vendors',
          name: 'vendors',
          component: () => import('@/modules/masterdata/views/VendorsView.vue'),
        },
        {
          path: 'vendors/:id',
          name: 'vendor.detail',
          component: () => import('@/modules/masterdata/views/VendorDetailView.vue'),
        },
        {
          path: 'inventory',
          name: 'inventory',
          component: () => import('@/modules/inventory/views/InventoryView.vue'),
        },
        {
          path: 'grn',
          name: 'grn',
          component: () => import('@/modules/inventory/views/GrnView.vue'),
        },
        {
          path: 'production',
          name: 'production.plans',
          component: () => import('@/modules/production/views/ProductionPlanView.vue'),
        },
        {
          path: 'production/floor',
          name: 'production.floor',
          component: () => import('@/modules/production/views/ProductionFloorView.vue'),
        },
        {
          path: 'production/oee',
          name: 'production.oee',
          component: () => import('@/modules/production/views/OeeDashboard.vue'),
        },
        {
          path: 'workers',
          name: 'workers',
          component: () => import('@/modules/worker/views/WorkerProductivityView.vue'),
        },
        {
          path: 'attendance',
          name: 'attendance',
          component: () => import('@/modules/worker/views/AttendanceView.vue'),
        },
      ],
    },

    { path: '/:pathMatch(.*)*', redirect: '/' },
  ],
})

setupGuards(router)

export default router
