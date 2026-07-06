<template>
  <div class="min-h-screen flex items-center justify-center bg-light-100 dark:bg-dark-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 text-center">
      <div class="w-12 h-12 bg-primary-600 rounded-xl flex items-center justify-center mx-auto">
        <span class="text-white font-bold text-xl">K</span>
      </div>
      <h2 class="mt-6 text-3xl font-bold text-light-1000 dark:text-dark-1000">
        Check your email
      </h2>
      <p class="text-sm text-light-900 dark:text-dark-800">
        We sent a verification email to
        <strong class="text-light-1000 dark:text-dark-1000">{{ email }}</strong>
      </p>
      <p class="text-sm text-light-800 dark:text-dark-700">
        Click the link in the email to verify your account. You can close this page.
      </p>

      <div v-if="resendMessage" class="text-sm p-3 rounded-lg" :class="resendError ? 'text-red-600 bg-red-50 dark:bg-red-900/20' : 'text-green-600 bg-green-50 dark:bg-green-900/20'">
        {{ resendMessage }}
      </div>

      <Button variant="secondary" :loading="isResending" @click="resendEmail">
        Resend verification email
      </Button>

      <p class="text-sm text-light-800 dark:text-dark-700">
        Already verified?
        <router-link to="/login" class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">
          Sign in
        </router-link>
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRoute } from 'vue-router'
import { authService } from '@/services/auth.service'
import Button from '@/components/ui/Button.vue'

const route = useRoute()
const email = ref((route.query.email as string) || '')
const isResending = ref(false)
const resendMessage = ref('')
const resendError = ref(false)

async function resendEmail() {
  if (!email.value) return
  isResending.value = true
  resendMessage.value = ''
  resendError.value = false
  try {
    await authService.sendVerificationEmail(email.value)
    resendMessage.value = 'Verification email sent!'
  } catch {
    resendError.value = true
    resendMessage.value = 'Failed to send. Try again later.'
  } finally {
    isResending.value = false
  }
}
</script>
