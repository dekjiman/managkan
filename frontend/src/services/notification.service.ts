import api from './api'

export interface Notification {
  id: number
  publicId: string
  userId: string
  workspaceId: number | null
  type: string
  title: string
  entityType: string | null
  entityId: string | null
  entityUrl: string | null
  data: {
    comment?: string
    cardTitle?: string
    commenterName?: string
  } | null
  read: boolean
  createdBy: string | null
  createdAt: string
}

export const notificationService = {
  async getAll(): Promise<{ data: Notification[] }> {
    const response = await api.get('/notifications')
    return response.data
  },

  async getUnreadCount(): Promise<{ data: { count: number } }> {
    const response = await api.get('/notifications/unread-count')
    return response.data
  },

  async markRead(publicId: string): Promise<void> {
    await api.patch(`/notifications/${publicId}/read`)
  },

  async markAllRead(): Promise<void> {
    await api.patch('/notifications/read-all')
  },
}
