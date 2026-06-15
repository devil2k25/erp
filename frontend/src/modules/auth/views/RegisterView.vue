<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { AuthService } from '@/services/auth.service'

const router = useRouter()
const loading = ref(false)
const error = ref('')
const success = ref('')

const form = ref({
  organization_name: '',
  owner_name: '',
  owner_email: '',
  password: '',
  password_confirmation: '',
  owner_phone: '',
  industry_type: 'Manufacturing',
  timezone: 'Asia/Kolkata',
  plan_slug: 'basic',
})

async function submit() {
  if (form.value.password !== form.value.password_confirmation) {
    error.value = 'Passwords do not match'
    return
  }
  loading.value = true
  error.value = ''
  try {
    const response: any = await AuthService.register(form.value)
    success.value = `Organization created! Your slug is: ${response.data.organization.slug}. Please save it for login.`
    setTimeout(() => router.push('/login'), 3000)
  } catch (e: any) {
    error.value = e?.error ?? 'Registration failed. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-slate-950 flex items-center justify-center p-4">
    <div class="w-full max-w-lg">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-blue-400">Factory ERP</h1>
        <p class="text-slate-400 mt-2">Register your organization</p>
      </div>
      <form @submit.prevent="submit" class="bg-slate-900 rounded-2xl p-8 space-y-4 shadow-xl border border-slate-800">
        <div v-if="error" class="bg-red-900/30 border border-red-500/50 text-red-400 text-sm p-3 rounded-lg">{{ error }}</div>
        <div v-if="success" class="bg-green-900/30 border border-green-500/50 text-green-400 text-sm p-3 rounded-lg">{{ success }}</div>

        <div class="grid grid-cols-2 gap-4">
          <div class="col-span-2">
            <label class="block text-sm font-medium text-slate-300 mb-1.5">Organization Name</label>
            <input v-model="form.organization_name" type="text" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-1.5">Owner Name</label>
            <input v-model="form.owner_name" type="text" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-1.5">Phone</label>
            <input v-model="form.owner_phone" type="tel" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500" />
          </div>
          <div class="col-span-2">
            <label class="block text-sm font-medium text-slate-300 mb-1.5">Email</label>
            <input v-model="form.owner_email" type="email" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-1.5">Password</label>
            <input v-model="form.password" type="password" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-300 mb-1.5">Confirm Password</label>
            <input v-model="form.password_confirmation" type="password" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500" />
          </div>
        </div>

        <button type="submit" :disabled="loading" class="w-full bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white font-medium py-2.5 rounded-lg transition-colors text-sm">
          {{ loading ? 'Creating Organization...' : 'Create Organization' }}
        </button>

        <p class="text-center text-sm text-slate-500">
          Already registered?
          <router-link to="/login" class="text-blue-400 hover:underline">Sign in</router-link>
        </p>
      </form>
    </div>
  </div>
</template>
