declare module 'midtrans-client' {
  interface SnapOptions {
    isProduction: boolean
    serverKey: string
    clientKey: string
  }

  interface TransactionResult {
    token: string
    redirect_url: string
  }

  class Snap {
    constructor(options: SnapOptions)
    createTransaction(parameter: Record<string, any>): Promise<TransactionResult>
  }

  export { Snap }
}
