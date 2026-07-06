import api from './api'

export const apiKeyService = {
  async getByWorkspace(workspaceSlug: string) {
    const res = await api.get('/api-keys', { params: { workspaceSlug } })
    return res.data
  },
  async create(workspaceSlug: string, data: { name: string; permissions: string[] }) {
    const res = await api.post('/api-keys', data, { params: { workspaceSlug } })
    return res.data
  },
  async revoke(workspaceSlug: string, publicId: string) {
    const res = await api.delete(`/api-keys/${publicId}`, { params: { workspaceSlug } })
    return res.data
  }
}
