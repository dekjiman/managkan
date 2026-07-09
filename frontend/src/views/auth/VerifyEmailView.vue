<template>
  <div class="min-h-screen flex items-center justify-center bg-light-100 dark:bg-dark-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 text-center">
      <div v-if="loading" class="space-y-4">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mx-auto" />
        <p class="text-sm text-light-800 dark:text-dark-700">Verifying your email...</p>
      </div>

      <div v-else-if="success" class="space-y-4">
        <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center mx-auto">
          <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h2 class="text-3xl font-bold text-light-1000 dark:text-dark-1000">Email verified!</h2>
        <p class="text-sm text-light-800 dark:text-dark-700">Your email has been verified successfully.</p>
        <router-link to="/login" class="inline-block mt-4 px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
          Sign in
        </router-link>
      </div>

      <div v-else class="space-y-4">
        <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center mx-auto">
          <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </div>
        <h2 class="text-3xl font-bold text-light-1000 dark:text-dark-1000">Verification failed</h2>
        <p class="text-sm text-light-800 dark:text-dark-700">{{ errorMessage }}</p>
        <router-link to="/login" class="inline-block mt-4 text-sm font-medium text-primary-600 hover:text-primary-500">
          Back to sign in
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/services/api'

const route = useRoute()
const loading = ref(true)
const success = ref(false)
const errorMessage = ref('')

onMounted(async () => {
  const token = route.query.token as string
  if (!token) {
    loading.value = false
    errorMessage.value = 'No verification token provided.'
    return
  }

  try {
    await api.post('/auth/verify-email', { token })
    success.value = true
  } catch (e: any) {
    errorMessage.value = e.response?.data?.message || 'Verification failed. The link may have expired.'
  } finally {
    loading.value = false
  }
})
</script>
