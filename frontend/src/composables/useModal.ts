import { ref } from 'vue'

export function useModal() {
  const isOpen = ref(false)
  const modalData = ref<any>(null)

  function open(data?: any) {
    modalData.value = data || null
    isOpen.value = true
  }

  function close() {
    isOpen.value = false
    modalData.value = null
  }

  return {
    isOpen,
    modalData,
    open,
    close
  }
}
