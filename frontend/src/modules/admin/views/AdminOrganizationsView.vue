<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { AdminService } from '@/services/admin.service'

interface Organization {
  id: string
  name: string
  slug: string
  owner_name: string
  owner_email: string
  owner_phone: string | null
  industry_type: string | null
  status: 'active' | 'trial' | 'suspended' | 'cancelled'
  created_at: string
  subscription?: { plan?: { name: string } }
}

const orgs = ref<Organization[]>([])
const meta = ref<any>(null)
const loading = ref(false)
const showCreate = ref(false)
const creating = ref(false)
const createError = ref('')
const search = ref('')

const form = ref({
  organization_name: '',
  owner_name: '',
  owner_email: '',
  password: '',
  owner_phone: '',
  industry_type: '',
  plan_slug: 'basic',
  timezone: 'UTC',
  status: 'active',
})

async function loadOrgs(page = 1) {
  loading.value = true
  try {
    const res: any = await AdminService.getOrganizations({ page, search: search.value || undefined })
    orgs.value = res.data.data
    meta.value = res.data.meta
  } finally {
    loading.value = false
  }
}

async function createOrg() {
  creating.value = true
  createError.value = ''
  try {
    await AdminService.createOrganization(form.value)
    showCreate.value = false
    resetForm()
    await loadOrgs()
  } catch (e: any) {
    createError.value = e?.error ?? Object.values(e?.errors ?? {}).flat().join(' ') ?? 'Creation failed.'
  } finally {
    creating.value = false
  }
}

async function updateStatus(org: Organization, status: string) {
  await AdminService.updateOrgStatus(org.id, status)
  org.status = status as any
}

function resetForm() {
  form.value = {
    organization_name: '', owner_name: '', owner_email: '', password: '',
    owner_phone: '', industry_type: '', plan_slug: 'basic', timezone: 'UTC', status: 'active',
  }
}

const statusColors: Record<string, string> = {
  active: 'bg-green-500/20 text-green-400 border-green-500/30',
  trial: 'bg-blue-500/20 text-blue-400 border-blue-500/30',
  suspended: 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
  cancelled: 'bg-red-500/20 text-red-400 border-red-500/30',
}

onMounted(() => loadOrgs())
</script>

<template>
  <div class="min-h-screen bg-slate-950 text-white">
    <!-- Admin Header -->
    <header class="bg-slate-900 border-b border-slate-800 px-6 py-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-purple-600/20 rounded-lg flex items-center justify-center">
          <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
          </svg>
        </div>
        <span class="font-semibold text-white">Admin Panel</span>
        <span class="text-slate-500">/</span>
        <span class="text-slate-300">Organizations</span>
      </div>
      <router-link to="/admin/login" class="text-sm text-slate-400 hover:text-white transition-colors">
        Sign out
      </router-link>
    </header>

    <div class="max-w-7xl mx-auto px-6 py-8">
      <!-- Page header -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-white">Organizations</h1>
          <p class="text-slate-400 text-sm mt-1">Manage all tenant organizations on the platform</p>
        </div>
        <button
          @click="showCreate = true"
          class="flex items-center gap-2 bg-purple-600 hover:bg-purple-500 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          New Organization
        </button>
      </div>

      <!-- Search -->
      <div class="mb-4">
        <input
          v-model="search"
          @input="loadOrgs()"
          type="text"
          placeholder="Search by name or email…"
          class="w-full max-w-sm bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-purple-500"
        />
      </div>

      <!-- Table -->
      <div class="bg-slate-900 rounded-xl border border-slate-800 overflow-hidden">
        <div v-if="loading" class="py-16 text-center text-slate-500 text-sm">Loading…</div>
        <table v-else class="w-full text-sm">
          <thead>
            <tr class="border-b border-slate-800">
              <th class="text-left px-5 py-3 text-slate-400 font-medium">Organization</th>
              <th class="text-left px-5 py-3 text-slate-400 font-medium">Owner</th>
              <th class="text-left px-5 py-3 text-slate-400 font-medium">Plan</th>
              <th class="text-left px-5 py-3 text-slate-400 font-medium">Status</th>
              <th class="text-left px-5 py-3 text-slate-400 font-medium">Created</th>
              <th class="text-left px-5 py-3 text-slate-400 font-medium">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800">
            <tr v-if="!orgs.length">
              <td colspan="6" class="py-12 text-center text-slate-500">No organizations yet</td>
            </tr>
            <tr v-for="org in orgs" :key="org.id" class="hover:bg-slate-800/40 transition-colors">
              <td class="px-5 py-4">
                <div class="font-medium text-white">{{ org.name }}</div>
                <div class="text-xs text-slate-500 mt-0.5">{{ org.slug }}</div>
              </td>
              <td class="px-5 py-4">
                <div class="text-white">{{ org.owner_name }}</div>
                <div class="text-xs text-slate-500 mt-0.5">{{ org.owner_email }}</div>
              </td>
              <td class="px-5 py-4 text-slate-300">
                {{ org.subscription?.plan?.name ?? '—' }}
              </td>
              <td class="px-5 py-4">
                <span
                  :class="statusColors[org.status]"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border"
                >
                  {{ org.status }}
                </span>
              </td>
              <td class="px-5 py-4 text-slate-400 text-xs">
                {{ new Date(org.created_at).toLocaleDateString() }}
              </td>
              <td class="px-5 py-4">
                <select
                  :value="org.status"
                  @change="updateStatus(org, ($event.target as HTMLSelectElement).value)"
                  class="bg-slate-800 border border-slate-700 text-slate-300 text-xs rounded px-2 py-1 focus:outline-none focus:border-purple-500"
                >
                  <option value="active">Set Active</option>
                  <option value="trial">Set Trial</option>
                  <option value="suspended">Suspend</option>
                  <option value="cancelled">Cancel</option>
                </select>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Org Modal -->
    <div
      v-if="showCreate"
      class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4"
      @click.self="showCreate = false"
    >
      <div class="bg-slate-900 rounded-2xl border border-slate-700 w-full max-w-lg shadow-2xl">
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-800">
          <h2 class="font-semibold text-white">Create Organization</h2>
          <button @click="showCreate = false" class="text-slate-400 hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="createOrg" class="px-6 py-5 space-y-4">
          <div v-if="createError" class="bg-red-900/30 border border-red-500/50 text-red-400 text-sm p-3 rounded-lg">
            {{ createError }}
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
              <label class="block text-xs font-medium text-slate-400 mb-1">Organization Name *</label>
              <input v-model="form.organization_name" type="text" required placeholder="Acme Manufacturing"
                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-purple-500" />
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1">Owner Name *</label>
              <input v-model="form.owner_name" type="text" required placeholder="John Smith"
                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-purple-500" />
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1">Phone</label>
              <input v-model="form.owner_phone" type="text" placeholder="+91 98765 43210"
                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-purple-500" />
            </div>
            <div class="col-span-2">
              <label class="block text-xs font-medium text-slate-400 mb-1">Owner Email *</label>
              <input v-model="form.owner_email" type="email" required placeholder="owner@acme.com"
                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-purple-500" />
            </div>
            <div class="col-span-2">
              <label class="block text-xs font-medium text-slate-400 mb-1">Initial Password *</label>
              <input v-model="form.password" type="password" required placeholder="Min 8 characters"
                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-purple-500" />
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1">Industry</label>
              <input v-model="form.industry_type" type="text" placeholder="Automotive"
                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-purple-500" />
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1">Plan</label>
              <select v-model="form.plan_slug"
                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-purple-500">
                <option value="basic">Basic</option>
                <option value="professional">Professional</option>
                <option value="enterprise">Enterprise</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1">Status</label>
              <select v-model="form.status"
                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-purple-500">
                <option value="active">Active</option>
                <option value="trial">Trial</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-medium text-slate-400 mb-1">Timezone</label>
              <input v-model="form.timezone" type="text" placeholder="Asia/Kolkata"
                class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-purple-500" />
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-2">
            <button type="button" @click="showCreate = false"
              class="px-4 py-2 text-sm text-slate-400 hover:text-white transition-colors">
              Cancel
            </button>
            <button type="submit" :disabled="creating"
              class="px-5 py-2 bg-purple-600 hover:bg-purple-500 disabled:opacity-50 text-white text-sm font-medium rounded-lg transition-colors">
              {{ creating ? 'Creating…' : 'Create Organization' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
