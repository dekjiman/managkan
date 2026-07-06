import api from './api'

export interface DashboardData {
  summary: {
    workspaces: number
    boards: number
    cards: number
    members: number
  }
  cardsPerList: { name: string; count: number }[]
  cardsOverTime: { date: string; count: number }[]
  overdue: {
    count: number
    cards: { publicId: string; title: string; dueDate: string; listName: string; boardName: string }[]
  }
  dueSoon: {
    count: number
    cards: { publicId: string; title: string; dueDate: string; listName: string; boardName: string }[]
  }
  userActivity: { name: string; count: number }[]
  boardsPerWorkspace: { name: string; count: number }[]
}

export const dashboardService = {
  async getData(workspacePublicId?: string): Promise<{ data: DashboardData }> {
    const params: Record<string, string> = {}
    if (workspacePublicId) params.workspacePublicId = workspacePublicId
    const response = await api.get('/dashboard', { params })
    return response.data
  },
}
