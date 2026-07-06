import api from './api'

export const listService = {
  async getByBoard(boardPublicId: string) {
    const response = await api.get('/lists', { params: { boardPublicId } })
    return response.data
  },

  async create(data: { boardPublicId: string; name: string }) {
    const response = await api.post('/lists', data)
    return response.data
  },

  async update(publicId: string, data: { name?: string }) {
    const response = await api.patch(`/lists/${publicId}`, data)
    return response.data
  },

  async reorder(boardPublicId: string, listIds: string[]) {
    const response = await api.put('/lists/reorder', { boardPublicId, listIds })
    return response.data
  },

  async delete(publicId: string) {
    const response = await api.delete(`/lists/${publicId}`)
    return response.data
  }
}
