import api from './api'

export const boardService = {
  async getByWorkspace(workspacePublicId: string) {
    const response = await api.get(`/workspaces/${workspacePublicId}/boards`)
    return response.data
  },

  async getById(publicIdOrSlug: string) {
    const response = await api.get(`/boards/${publicIdOrSlug}`)
    return response.data
  },

  async getTemplates(workspacePublicId: string) {
    const response = await api.get(`/workspaces/${workspacePublicId}/boards`, {
      params: { type: 'template' }
    })
    return response.data
  },

  async create(workspacePublicId: string, data: { name: string; lists?: string[]; labels?: string[]; type?: string; sourceBoardPublicId?: string }) {
    const response = await api.post(`/workspaces/${workspacePublicId}/boards`, {
      name: data.name,
      lists: data.lists ?? [],
      labels: data.labels ?? [],
      type: data.type ?? 'regular',
      sourceBoardPublicId: data.sourceBoardPublicId
    })
    return response.data
  },

  async update(publicId: string, data: { name?: string; description?: string; visibility?: string }) {
    const response = await api.patch(`/boards/${publicId}`, data)
    return response.data
  },

  async delete(publicId: string) {
    const response = await api.delete(`/boards/${publicId}`)
    return response.data
  }
}
