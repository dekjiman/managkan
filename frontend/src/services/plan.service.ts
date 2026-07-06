import api from './api'

export interface Plan {
  id: number
  name: string
  displayName: string
  price: number
  currency: string
  boardLimit: number
  memberLimit: number
  workspaceLimit: number
  storageLimit: number
  features: string[]
  isActive: boolean
}

export const planService = {
  async getAll(): Promise<{ data: Plan[] }> {
    const response = await api.get('/plans')
    return response.data
  },

  async getByName(name: string): Promise<{ data: Plan }> {
    const response = await api.get(`/plans/${name}`)
    return response.data
  },
}
