import { ref } from 'vue'

export function useOAuth() {
  const oauthError = ref('')
  const isOAuthLoading = ref(false)

  async function signInOAuth(provider: string, redirect?: string) {
    oauthError.value = ''
    isOAuthLoading.value = true

    const callbackURL = `${window.location.origin}${redirect || '/dashboard'}`
    const backendUrl = import.meta.env.VITE_API_URL?.replace('/api', '') || 'http://localhost:3000'

    try {
      const res = await fetch(`${backendUrl}/api/auth/sign-in/social`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({ provider, callbackURL })
      })
      const data = await res.json()
      if (data.url) {
        window.location.href = data.url
      } else {
        oauthError.value = 'Failed to start OAuth. Please try again.'
      }
    } catch (err) {
      oauthError.value = 'OAuth connection failed. Please try again.'
    } finally {
      isOAuthLoading.value = false
    }
  }

  return {
    signInOAuth,
    oauthError,
    isOAuthLoading
  }
}
