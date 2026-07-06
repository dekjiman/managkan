import api from './api'

export const memberService = {
  async getByWorkspace(workspaceId: string) {
    const response = await api.get('/members', { params: { workspaceId } })
    return response.data
  },

  async add(data: { workspaceId: string; email: string; role?: string }) {
    const response = await api.post('/members', data)
    return response.data
  },

  async updateRole(publicId: string, role: string) {
    const response = await api.patch(`/members/${publicId}`, { role })
    return response.data
  },

  async remove(publicId: string) {
    const response = await api.delete(`/members/${publicId}`)
    return response.data
  },

  async resendInvite(publicId: string) {
    const response = await api.post(`/members/${publicId}/resend-invite`)
    return response.data
  }
}
