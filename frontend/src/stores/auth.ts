import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { User } from '@/types'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const isLoading = ref(false)

  const isAuthenticated = computed(() => !!user.value)

  function setUser(userData: User) {
    user.value = userData
  }

  function clearUser() {
    user.value = null
  }

  async function fetchSession() {
    isLoading.value = true
    try {
      const { authService } = await import('@/services/auth.service')
      const response = await authService.getSession()
      if (response.user) {
        setUser(response.user)
      } else {
        clearUser()
      }
    } catch {
      clearUser()
    } finally {
      isLoading.value = false
    }
  }

  return {
    user,
    isLoading,
    isAuthenticated,
    setUser,
    clearUser,
    fetchSession
  }
})
