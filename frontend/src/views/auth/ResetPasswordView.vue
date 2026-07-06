<template>
  <div class="min-h-screen flex items-center justify-center bg-light-100 dark:bg-dark-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div v-if="invalidToken" class="text-center space-y-4">
        <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center mx-auto">
          <span class="text-red-600 dark:text-red-400 font-bold text-xl">!</span>
        </div>
        <h2 class="text-3xl font-bold text-light-1000 dark:text-dark-1000">
          Invalid or expired link
        </h2>
        <p class="text-sm text-light-800 dark:text-dark-700">
          This password reset link is invalid or has expired.
        </p>
        <router-link to="/forgot-password" class="inline-block mt-4 font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">
          Request a new reset link
        </router-link>
      </div>

      <div v-else-if="success" class="text-center space-y-4">
        <div class="w-12 h-12 bg-primary-600 rounded-xl flex items-center justify-center mx-auto">
          <span class="text-white font-bold text-xl">K</span>
        </div>
        <h2 class="text-3xl font-bold text-light-1000 dark:text-dark-1000">
          Password reset successful
        </h2>
        <p class="text-sm text-light-800 dark:text-dark-700">
          Your password has been updated. You can now sign in with your new password.
        </p>
        <router-link to="/login" class="inline-block mt-4 font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">
          Sign in
        </router-link>
      </div>

      <template v-else>
        <div class="text-center">
          <div class="w-12 h-12 bg-primary-600 rounded-xl flex items-center justify-center mx-auto">
            <span class="text-white font-bold text-xl">K</span>
          </div>
          <h2 class="mt-6 text-3xl font-bold text-light-1000 dark:text-dark-1000">
            Set new password
          </h2>
          <p class="mt-2 text-sm text-light-900 dark:text-dark-800">
            Enter your new password below.
          </p>
        </div>

        <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
          <div class="space-y-4">
            <div class="w-full">
              <label class="block text-xs font-medium text-light-900 dark:text-dark-800 mb-1">New password</label>
              <div class="relative">
                <input
                  v-model="password"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="••••••••"
                  required
                  class="input w-full pr-10"
                />
                <button type="button" @click="showPassword = !showPassword" class="absolute right-2 top-1/2 -translate-y-1/2 text-light-700 dark:text-dark-600 hover:text-light-1000 dark:hover:text-dark-1000">
                  <svg v-if="showPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                  </svg>
                  <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </div>
            </div>
            <div class="w-full">
              <label class="block text-xs font-medium text-light-900 dark:text-dark-800 mb-1">Confirm new password</label>
              <div class="relative">
                <input
                  v-model="confirmPassword"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  placeholder="••••••••"
                  required
                  class="input w-full pr-10"
                />
                <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-2 top-1/2 -translate-y-1/2 text-light-700 dark:text-dark-600 hover:text-light-1000 dark:hover:text-dark-1000">
                  <svg v-if="showConfirmPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                  </svg>
                  <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <div v-if="error" class="text-sm text-red-600 bg-red-50 dark:bg-red-900/20 p-3 rounded-lg">
            {{ error }}
          </div>

          <Button type="submit" :loading="isLoading" class="w-full">
            Reset password
          </Button>

          <p class="text-center text-sm text-light-800 dark:text-dark-700">
            <router-link to="/login" class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">
              Back to sign in
            </router-link>
          </p>
        </form>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { authService } from '@/services/auth.service'
import Button from '@/components/ui/Button.vue'

const route = useRoute()

const token = ref('')
const password = ref('')
const confirmPassword = ref('')
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const isLoading = ref(false)
const error = ref('')
const invalidToken = ref(false)
const success = ref(false)

onMounted(() => {
  token.value = (route.query.token as string)
    || (route.params.token as string)
    || ''
  if (!token.value) {
    invalidToken.value = true
  }
})

async function handleSubmit() {
  error.value = ''

  if (password.value.length < 6) {
    error.value = 'Password must be at least 6 characters'
    return
  }

  if (password.value !== confirmPassword.value) {
    error.value = 'Passwords do not match'
    return
  }

  isLoading.value = true
  try {
    await authService.resetPassword(token.value, password.value)
    success.value = true
  } catch (e: any) {
    if (e.response?.data?.message?.includes('expired') || e.response?.data?.message?.includes('invalid')) {
      invalidToken.value = true
    } else {
      error.value = e.response?.data?.message || 'Failed to reset password'
    }
  } finally {
    isLoading.value = false
  }
}
</script>
