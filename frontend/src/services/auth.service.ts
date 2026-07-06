import api from './api'

export const authService = {
  async signUp(data: { name: string; email: string; password: string; callbackURL?: string }) {
    const response = await api.post('/auth/sign-up/email', { ...data, callbackURL: data.callbackURL || window.location.origin })
    return response.data
  },

  async signIn(data: { email: string; password: string }) {
    const response = await api.post('/auth/sign-in/email', data)
    return response.data
  },

  async signOut() {
    const response = await api.post('/auth/sign-out')
    return response.data
  },

  async getSession() {
    const response = await api.get('/auth/get-session')
    return response.data
  },

  async sendVerificationEmail(email: string) {
    const response = await api.post('/auth/send-verification-email', { email, callbackURL: window.location.origin })
    return response.data
  },

  async getInviteInfo(code: string) {
    const response = await api.get(`/members/invite/${code}`)
    return response.data.data
  },

  async inviteSignUp(data: { code: string; name: string; password: string }) {
    const response = await api.post('/members/invite-signup', data)
    return response.data
  },

  async requestPasswordReset(email: string) {
    const redirectTo = `${window.location.origin}/reset-password`
    const response = await api.post('/auth/request-password-reset', { email, redirectTo })
    return response.data
  },

  async resetPassword(token: string, newPassword: string) {
    const response = await api.post('/auth/reset-password', { token, newPassword })
    return response.data
  }
}
