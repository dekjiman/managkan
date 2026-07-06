import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { authService } from '@/services/auth.service'

export function useAuth() {
  const router = useRouter()
  const route = useRoute()
  const authStore = useAuthStore()

  async function login(email: string, password: string) {
    const response = await authService.signIn({ email, password })
    if (response.user) {
      authStore.setUser(response.user)
      const redirect = (route.query.redirect as string) || '/dashboard'
      router.push(redirect)
    }
  }

  async function register(name: string, email: string, password: string) {
    const response = await authService.signUp({ name, email, password })
    if (response.user) {
      authStore.setUser(response.user)
      if (!response.user.emailVerified) {
        router.push(`/verify-email-notice?email=${encodeURIComponent(response.user.email)}`)
      } else {
        const redirect = (route.query.redirect as string) || '/dashboard'
        router.push(redirect)
      }
    }
  }

  async function logout() {
    await authService.signOut()
    authStore.clearUser()
    router.push('/login')
  }

  async function checkSession() {
    await authStore.fetchSession()
  }

  return {
    login,
    register,
    logout,
    checkSession,
    user: authStore.user,
    isAuthenticated: authStore.isAuthenticated,
    isLoading: authStore.isLoading
  }
}
