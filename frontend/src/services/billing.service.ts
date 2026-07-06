import api from './api'

export interface BillingData {
  plan: string
  subscription: {
    publicId: string
    plan: string
    startDate: string
    endDate: string | null
    paymentAmount: number | null
    status: string
  } | null
  workspaceUsage: {
    count: number
    limit: number
  }
  usage: {
    boards: number
    members: number
    storageBytes: number
  }
}

export interface CheckoutData {
  token: string
  subscriptionId: string
}

export const billingService = {
  async getByWorkspace(workspaceSlug: string): Promise<{ data: BillingData }> {
    const response = await api.get('/billing', { params: { workspaceSlug } })
    return response.data
  },

  async createCheckout(workspaceSlug: string, planName: string): Promise<{ data: CheckoutData }> {
    const response = await api.post('/billing/checkout', { planName }, { params: { workspaceSlug } })
    return response.data
  },
}
