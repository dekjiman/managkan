import api from './api'

export const integrationService = {
  async getByWorkspace(workspaceSlug: string) {
    const res = await api.get('/integrations', { params: { workspaceSlug } })
    return res.data
  },
  async setConnected(workspaceSlug: string, data: { integrationId: string; connected: boolean }) {
    const res = await api.put('/integrations', data, { params: { workspaceSlug } })
    return res.data
  }
}
