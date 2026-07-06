import api from './api'

export const checklistService = {
  async getByCard(cardId: string) {
    const response = await api.get('/checklists', { params: { cardId } })
    return response.data
  },

  async create(data: { cardId: string; name: string }) {
    const response = await api.post('/checklists', data)
    return response.data
  },

  async update(publicId: string, data: { name?: string }) {
    const response = await api.patch(`/checklists/${publicId}`, data)
    return response.data
  },

  async delete(publicId: string) {
    const response = await api.delete(`/checklists/${publicId}`)
    return response.data
  },

  async createItem(checklistId: string, data: { title: string }) {
    const response = await api.post(`/checklists/${checklistId}/items`, data)
    return response.data
  },

  async updateItem(publicId: string, data: { title?: string; completed?: boolean }) {
    const response = await api.patch(`/checklists/items/${publicId}`, data)
    return response.data
  },

  async deleteItem(publicId: string) {
    const response = await api.delete(`/checklists/items/${publicId}`)
    return response.data
  }
}
