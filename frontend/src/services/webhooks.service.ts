import api from './api'

export const webhookService = {
  async getByWorkspace(workspaceSlug: string) {
    const res = await api.get('/webhooks', { params: { workspaceSlug } })
    return res.data
  },
  async create(workspaceSlug: string, data: { name: string; url: string; events: string[] }) {
    const res = await api.post('/webhooks', data, { params: { workspaceSlug } })
    return res.data
  },
  async delete(workspaceSlug: string, publicId: string) {
    const res = await api.delete(`/webhooks/${publicId}`, { params: { workspaceSlug } })
    return res.data
  },
  async test(publicId: string) {
    const res = await api.post(`/webhooks/${publicId}/test`)
    return res.data
  }
}
