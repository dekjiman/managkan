import api from './api'

export const userService = {
  async getMe() {
    const response = await api.get('/users/me')
    return response.data
  },

  async updateMe(data: { name?: string; image?: string }) {
    const response = await api.patch('/users/me', data)
    return response.data
  },

  async deleteMe() {
    const response = await api.delete('/users/me')
    return response.data
  }
}
