import api from './api'

export const attachmentService = {
  async getByCard(cardId: string) {
    const response = await api.get(`/attachments/${cardId}`)
    return response.data
  },

  async upload(cardId: string, file: File) {
    const formData = new FormData()
    formData.append('file', file)

    const response = await api.post(`/attachments/${cardId}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    return response.data
  },

  async delete(publicId: string) {
    const response = await api.delete(`/attachments/${publicId}`)
    return response.data
  },

  async download(publicId: string) {
    const response = await api.get(`/attachments/download/${publicId}`, {
      responseType: 'blob'
    })
    return response.data
  }
}
