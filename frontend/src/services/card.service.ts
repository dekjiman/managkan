import api from './api'

export const cardService = {
  async getByList(listPublicId: string) {
    const response = await api.get('/cards', { params: { listPublicId } })
    return response.data
  },

  async getById(publicId: string) {
    const response = await api.get(`/cards/${publicId}`)
    return response.data
  },

  async create(data: {
    title: string
    description?: string
    listPublicId: string
    labelPublicIds?: string[]
    memberPublicIds?: string[]
    position?: 'start' | 'end'
    dueDate?: string | null
  }) {
    const response = await api.post('/cards', data)
    return response.data
  },

  async update(publicId: string, data: { title?: string; description?: string; dueDate?: string | null }) {
    const response = await api.patch(`/cards/${publicId}`, data)
    return response.data
  },

  async move(publicId: string, data: { listPublicId: string; index: number }) {
    const response = await api.put(`/cards/${publicId}/move`, data)
    return response.data
  },

  async duplicate(publicId: string) {
    const response = await api.post(`/cards/${publicId}/duplicate`)
    return response.data
  },

  async delete(publicId: string) {
    const response = await api.delete(`/cards/${publicId}`)
    return response.data
  },

  async toggleLabel(cardPublicId: string, labelPublicId: string) {
    const response = await api.put(`/cards/${cardPublicId}/labels/${labelPublicId}`)
    return response.data
  },

  async toggleMember(cardPublicId: string, memberPublicId: string) {
    const response = await api.put(`/cards/${cardPublicId}/members/${memberPublicId}`)
    return response.data
  }
}
