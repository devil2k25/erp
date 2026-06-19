<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.store'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({ email: '', password: '' })
const loading = ref(false)
const error = ref('')

async function submit() {
  loading.value = true
  error.value = ''
  try {
    await authStore.login(form.value.email, form.value.password)
    router.push('/')
  } catch (e: any) {
    error.value = e?.error ?? 'Invalid email or password.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-background flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
      <!-- Brand -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-12 h-12 bg-primary rounded-xl mb-4">
          <span class="material-symbols-outlined text-on-primary text-[24px]">factory</span>
        </div>
        <h1 class="text-headline-sm text-on-surface">ModernERP</h1>
        <p class="text-body-md text-outline mt-1">Sign in to your account</p>
      </div>

      <!-- Card -->
      <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-8 shadow-sm">
        <div v-if="error" class="bg-error-container border border-error/30 text-on-error-container text-body-sm p-3 rounded-lg mb-5">
          {{ error }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
          <div>
            <label class="block text-label-md text-on-surface mb-1.5">Email</label>
            <input
              v-model="form.email"
              type="email"
              placeholder="you@yourfactory.com"
              required
              class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-4 py-2.5 text-body-md text-on-surface placeholder-outline focus:outline-none focus:border-primary/50 focus:ring-2 focus:ring-primary/20"
            />
          </div>

          <div>
            <label class="block text-label-md text-on-surface mb-1.5">Password</label>
            <input
              v-model="form.password"
              type="password"
              placeholder="••••••••"
              required
              class="w-full bg-surface-container-low border border-outline-variant rounded-lg px-4 py-2.5 text-body-md text-on-surface placeholder-outline focus:outline-none focus:border-primary/50 focus:ring-2 focus:ring-primary/20"
            />
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-primary text-on-primary font-semibold py-2.5 rounded-lg hover:opacity-90 disabled:opacity-50 transition-opacity text-body-md"
          >
            {{ loading ? 'Signing in…' : 'Sign In' }}
          </button>
        </form>
      </div>

      <p class="text-center text-body-sm text-outline mt-6">
        Contact your administrator if you don't have an account.
      </p>
    </div>
  </div>
</template>
