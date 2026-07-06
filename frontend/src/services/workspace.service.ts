import api from './api'

export const workspaceService = {
  async getAll() {
    const response = await api.get('/workspaces')
    return response.data
  },

  async getById(publicId: string) {
    const response = await api.get(`/workspaces/${publicId}`)
    return response.data
  },

  async checkSlugAvailability(slug: string) {
    const response = await api.get('/workspaces/check-slug-availability', { params: { workspaceSlug: slug } })
    return response.data
  },

  async create(data: { name: string; description?: string }) {
    const response = await api.post('/workspaces', data)
    return response.data
  },

  async update(publicId: string, data: { name?: string; description?: string }) {
    const response = await api.patch(`/workspaces/${publicId}`, data)
    return response.data
  },

  async delete(publicId: string) {
    const response = await api.delete(`/workspaces/${publicId}`)
    return response.data
  }
}
