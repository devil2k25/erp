<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/services/api'

const attendance = ref<any[]>([])
const loading = ref(true)
const selectedDate = ref(new Date().toISOString().split('T')[0])
const showBulkModal = ref(false)

async function load() {
  loading.value = true
  try {
    const res: any = await api.get('/workers/attendance', { params: { date: selectedDate.value, per_page: 50 } })
    attendance.value = res.data?.data ?? res.data
  } finally { loading.value = false }
}

onMounted(load)

const statusColors: Record<string, string> = {
  present: 'bg-green-900/50 text-green-400',
  absent: 'bg-red-900/50 text-red-400',
  half_day: 'bg-yellow-900/50 text-yellow-400',
  late: 'bg-orange-900/50 text-orange-400',
  on_leave: 'bg-blue-900/50 text-blue-400',
  holiday: 'bg-purple-900/50 text-purple-400',
}

const summary = computed(() => ({
  present: attendance.value.filter(a => a.status === 'present').length,
  absent: attendance.value.filter(a => a.status === 'absent').length,
  late: attendance.value.filter(a => a.status === 'late').length,
}))
</script>

<script lang="ts">
import { computed } from 'vue'
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-white">Attendance</h1>
      <div class="flex gap-3">
        <input v-model="selectedDate" @change="load" type="date" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white" />
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-3 gap-4">
      <div class="bg-slate-900 rounded-xl p-4 border border-slate-800 text-center">
        <p class="text-2xl font-bold text-green-400">{{ summary.present }}</p>
        <p class="text-xs text-slate-500 mt-1">Present</p>
      </div>
      <div class="bg-slate-900 rounded-xl p-4 border border-slate-800 text-center">
        <p class="text-2xl font-bold text-red-400">{{ summary.absent }}</p>
        <p class="text-xs text-slate-500 mt-1">Absent</p>
      </div>
      <div class="bg-slate-900 rounded-xl p-4 border border-slate-800 text-center">
        <p class="text-2xl font-bold text-orange-400">{{ summary.late }}</p>
        <p class="text-xs text-slate-500 mt-1">Late</p>
      </div>
    </div>

    <div class="bg-slate-900 rounded-xl border border-slate-800 overflow-hidden">
      <div v-if="loading" class="p-8 text-center text-slate-500">Loading...</div>
      <table v-else class="w-full text-sm">
        <thead>
          <tr class="border-b border-slate-800 text-xs text-slate-500 uppercase">
            <th class="text-left px-4 py-3">Worker</th>
            <th class="text-left px-4 py-3">Shift</th>
            <th class="text-left px-4 py-3">Check In</th>
            <th class="text-left px-4 py-3">Check Out</th>
            <th class="text-right px-4 py-3">Overtime</th>
            <th class="text-center px-4 py-3">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="a in attendance" :key="a.id" class="border-b border-slate-800/50 hover:bg-slate-800/30">
            <td class="px-4 py-3">
              <p class="text-white">{{ a.user?.first_name }} {{ a.user?.last_name }}</p>
              <p class="text-xs text-slate-500">{{ a.user?.employee_code }}</p>
            </td>
            <td class="px-4 py-3 text-slate-400">{{ a.shift?.name }}</td>
            <td class="px-4 py-3 text-slate-300">{{ a.check_in_time ? new Date(a.check_in_time).toLocaleTimeString() : '—' }}</td>
            <td class="px-4 py-3 text-slate-300">{{ a.check_out_time ? new Date(a.check_out_time).toLocaleTimeString() : '—' }}</td>
            <td class="px-4 py-3 text-right text-slate-400">{{ a.overtime_minutes ? a.overtime_minutes + 'm' : '—' }}</td>
            <td class="px-4 py-3 text-center">
              <span :class="['text-xs px-2 py-0.5 rounded capitalize', statusColors[a.status] ?? 'bg-slate-800 text-slate-400']">{{ a.status.replace('_', ' ') }}</span>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="!loading && !attendance.length" class="p-8 text-center text-slate-500">No attendance records for this date.</div>
    </div>
  </div>
</template>
