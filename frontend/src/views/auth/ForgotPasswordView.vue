<template>
  <div class="min-h-screen flex items-center justify-center bg-light-100 dark:bg-dark-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div v-if="sent" class="text-center space-y-4">
        <div class="w-12 h-12 bg-primary-600 rounded-xl flex items-center justify-center mx-auto">
          <span class="text-white font-bold text-xl">K</span>
        </div>
        <h2 class="text-3xl font-bold text-light-1000 dark:text-dark-1000">
          Check your email
        </h2>
        <p class="text-sm text-light-900 dark:text-dark-800">
          If an account exists with <strong class="text-light-1000 dark:text-dark-1000">{{ email }}</strong>, you'll receive a password reset link shortly.
        </p>
        <p class="text-sm text-light-800 dark:text-dark-700">
          Didn't receive the email? Check your spam folder or
          <button @click="sent = false" class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">
            try again
          </button>
        </p>
      </div>

      <template v-else>
        <div class="text-center">
          <div class="w-12 h-12 bg-primary-600 rounded-xl flex items-center justify-center mx-auto">
            <span class="text-white font-bold text-xl">K</span>
          </div>
          <h2 class="mt-6 text-3xl font-bold text-light-1000 dark:text-dark-1000">
            Forgot your password?
          </h2>
          <p class="mt-2 text-sm text-light-900 dark:text-dark-800">
            Enter your email and we'll send you a link to reset your password.
          </p>
        </div>

        <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
          <Input
            v-model="email"
            label="Email address"
            type="email"
            placeholder="you@example.com"
            required
          />

          <div v-if="error" class="text-sm text-red-600 bg-red-50 dark:bg-red-900/20 p-3 rounded-lg">
            {{ error }}
          </div>

          <Button type="submit" :loading="isLoading" class="w-full">
            Send reset link
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
import { ref } from 'vue'
import { authService } from '@/services/auth.service'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'

const email = ref('')
const isLoading = ref(false)
const error = ref('')
const sent = ref(false)

async function handleSubmit() {
  error.value = ''
  isLoading.value = true
  try {
    await authService.requestPasswordReset(email.value)
    sent.value = true
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Failed to send reset email'
  } finally {
    isLoading.value = false
  }
}
</script>
