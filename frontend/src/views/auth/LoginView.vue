<template>
  <div class="min-h-screen flex items-center justify-center bg-light-100 dark:bg-dark-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div class="text-center">
        <div class="w-12 h-12 bg-primary-600 rounded-xl flex items-center justify-center mx-auto">
          <span class="text-white font-bold text-xl">M</span>
        </div>
        <h2 class="mt-6 text-3xl font-bold text-light-1000 dark:text-dark-1000">
          Sign in to ManagPro
        </h2>
        <p class="mt-2 text-sm text-light-900 dark:text-dark-800">
          Or
          <router-link to="/register" class="font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">
            create a new account
          </router-link>
        </p>
      </div>

      <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
        <div class="space-y-4">
          <Input
            v-model="form.email"
            label="Email address"
            type="email"
            placeholder="you@example.com"
            required
          />
          <Input
            v-model="form.password"
            label="Password"
            type="password"
            placeholder="••••••••"
            required
          />
          <div class="flex justify-end">
            <router-link to="/forgot-password" class="text-xs font-medium text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300">
              Forgot password?
            </router-link>
          </div>
        </div>

        <div v-if="error" class="text-sm text-red-600 bg-red-50 dark:bg-red-900/20 p-3 rounded-lg">
          {{ error }}
        </div>

        <div v-if="oauthError" class="text-sm text-red-600 bg-red-50 dark:bg-red-900/20 p-3 rounded-lg">
          {{ oauthError }}
        </div>

        <Button type="submit" :loading="isLoading" class="w-full">
          Sign in
        </Button>

        <Button variant="secondary" type="button" class="w-full" @click="loginWithGoogle">
          <svg class="w-5 h-5" viewBox="0 0 24 24">
            <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
            <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
          </svg>
          Continue with Google
        </Button>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRoute } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { useOAuth } from '@/composables/useOAuth'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'

const route = useRoute()
const { login } = useAuth()
const { signInOAuth, oauthError } = useOAuth()

const form = ref({
  email: '',
  password: ''
})
const error = ref('')
const isLoading = ref(false)

async function handleLogin() {
  error.value = ''
  isLoading.value = true
  try {
    await login(form.value.email, form.value.password)
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Login failed'
  } finally {
    isLoading.value = false
  }
}

function loginWithGoogle() { signInOAuth('google', route.query.redirect as string) }
</script>
