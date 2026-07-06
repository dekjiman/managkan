import api from './api'

export const commentService = {
  async getByCard(cardId: string) {
    const response = await api.get('/comments', { params: { cardId } })
    return response.data
  },

  async create(data: { cardId: number; comment: string; mentionedUserIds?: string[] }) {
    const response = await api.post('/comments', data)
    return response.data
  },

  async update(publicId: string, data: { comment: string }) {
    const response = await api.patch(`/comments/${publicId}`, data)
    return response.data
  },

  async delete(publicId: string) {
    const response = await api.delete(`/comments/${publicId}`)
    return response.data
  }
}
