/// <reference types="vite/client" />

interface Window {
  snap: {
    pay: (token: string, options?: {
      onSuccess?: (result: any) => void
      onPending?: (result: any) => void
      onError?: (error: any) => void
      onClose?: () => void
    }) => void
  }
}
