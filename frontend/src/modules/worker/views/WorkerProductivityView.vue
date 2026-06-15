<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const workers = ref<any[]>([])
const loading = ref(true)
const search = ref('')

async function load() {
  loading.value = true
  try {
    const res: any = await api.get('/workers/', { params: { search: search.value, per_page: 50 } })
    workers.value = res.data?.data ?? res.data
  } finally { loading.value = false }
}

onMounted(load)
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-white">Workers</h1>
      <input
        v-model="search"
        @input="load"
        type="text"
        placeholder="Search workers..."
        class="bg-slate-800 border border-slate-700 rounded-lg px-4 py-2 text-sm text-white placeholder-slate-500 w-64"
      />
    </div>

    <div class="bg-slate-900 rounded-xl border border-slate-800 overflow-hidden">
      <div v-if="loading" class="p-8 text-center text-slate-500">Loading...</div>
      <table v-else class="w-full text-sm">
        <thead>
          <tr class="border-b border-slate-800 text-xs text-slate-500 uppercase">
            <th class="text-left px-4 py-3">Worker</th>
            <th class="text-left px-4 py-3">Department</th>
            <th class="text-left px-4 py-3">Shift</th>
            <th class="text-left px-4 py-3">Role</th>
            <th class="text-center px-4 py-3">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="w in workers" :key="w.id" class="border-b border-slate-800/50 hover:bg-slate-800/30">
            <td class="px-4 py-3">
              <p class="text-white font-medium">{{ w.first_name }} {{ w.last_name }}</p>
              <p class="text-xs text-slate-500">{{ w.email }}</p>
            </td>
            <td class="px-4 py-3 text-slate-400">{{ w.department?.name ?? '—' }}</td>
            <td class="px-4 py-3 text-slate-400">{{ w.shift?.name ?? '—' }}</td>
            <td class="px-4 py-3 text-slate-400 capitalize">{{ w.roles?.[0]?.display_name ?? '—' }}</td>
            <td class="px-4 py-3 text-center">
              <span :class="['text-xs px-2 py-0.5 rounded', w.is_active ? 'bg-green-900/50 text-green-400' : 'bg-red-900/50 text-red-400']">
                {{ w.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="!loading && !workers.length" class="p-8 text-center text-slate-500">No workers found.</div>
    </div>
  </div>
</template>
