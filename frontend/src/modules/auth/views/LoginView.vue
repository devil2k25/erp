<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.store'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({ email: '', password: '', org_slug: '' })
const loading = ref(false)
const error = ref('')

async function submit() {
  if (!form.value.org_slug) {
    error.value = 'Organization slug is required'
    return
  }
  loading.value = true
  error.value = ''
  try {
    await authStore.login(form.value.email, form.value.password, form.value.org_slug)
    router.push('/')
  } catch (e: any) {
    error.value = e?.error ?? 'Login failed. Please check your credentials.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-slate-950 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-blue-400">Factory ERP</h1>
        <p class="text-slate-400 mt-2">Sign in to your organization</p>
      </div>
      <form @submit.prevent="submit" class="bg-slate-900 rounded-2xl p-8 space-y-5 shadow-xl border border-slate-800">
        <div v-if="error" class="bg-red-900/30 border border-red-500/50 text-red-400 text-sm p-3 rounded-lg">{{ error }}</div>

        <div>
          <label class="block text-sm font-medium text-slate-300 mb-1.5">Organization Slug</label>
          <input
            v-model="form.org_slug"
            type="text"
            placeholder="my-factory"
            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-300 mb-1.5">Email</label>
          <input
            v-model="form.email"
            type="email"
            placeholder="admin@factory.com"
            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-300 mb-1.5">Password</label>
          <input
            v-model="form.password"
            type="password"
            placeholder="••••••••"
            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
          />
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white font-medium py-2.5 rounded-lg transition-colors text-sm"
        >
          {{ loading ? 'Signing in...' : 'Sign In' }}
        </button>

        <p class="text-center text-sm text-slate-500">
          New organization?
          <router-link to="/register" class="text-blue-400 hover:underline">Register here</router-link>
        </p>
      </form>
    </div>
  </div>
</template>
