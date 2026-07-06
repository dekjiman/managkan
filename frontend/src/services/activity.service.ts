import api from './api'

export const activityService = {
  async getByCard(cardId: number) {
    const response = await api.get('/activities', { params: { cardId } })
    return response.data
  }
}
