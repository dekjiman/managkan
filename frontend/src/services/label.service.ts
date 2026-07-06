import api from './api'

export const labelService = {
  async getByBoard(boardId: string) {
    const response = await api.get('/labels', { params: { boardId } })
    return response.data
  },

  async create(data: { boardId: string; name: string; color: string }) {
    const response = await api.post('/labels', data)
    return response.data
  },

  async update(publicId: string, data: { name?: string; color?: string }) {
    const response = await api.patch(`/labels/${publicId}`, data)
    return response.data
  },

  async delete(publicId: string) {
    const response = await api.delete(`/labels/${publicId}`)
    return response.data
  }
}
