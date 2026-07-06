import { ref, onMounted, onUnmounted } from 'vue'
import { notificationService, type Notification } from '@/services/notification.service'

const notifications = ref<Notification[]>([])
const unreadCount = ref(0)
const isOpen = ref(false)
let pollingInterval: ReturnType<typeof setInterval> | null = null

export function useNotifications() {
  async function fetchNotifications() {
    try {
      const res = await notificationService.getAll()
      notifications.value = res.data
    } catch {
      // silent
    }
  }

  async function fetchUnreadCount() {
    try {
      const res = await notificationService.getUnreadCount()
      unreadCount.value = res.data.count
    } catch {
      // silent
    }
  }

  async function markRead(publicId: string) {
    try {
      await notificationService.markRead(publicId)
      const n = notifications.value.find(n => n.publicId === publicId)
      if (n && !n.read) {
        n.read = true
        unreadCount.value = Math.max(0, unreadCount.value - 1)
      }
    } catch {
      // silent
    }
  }

  async function markAllRead() {
    try {
      await notificationService.markAllRead()
      notifications.value.forEach(n => n.read = true)
      unreadCount.value = 0
    } catch {
      // silent
    }
  }

  function toggle() {
    isOpen.value = !isOpen.value
  }

  function close() {
    isOpen.value = false
  }

  function startPolling() {
    fetchUnreadCount()
    pollingInterval = setInterval(() => {
      fetchUnreadCount()
    }, 30000)
  }

  function stopPolling() {
    if (pollingInterval) {
      clearInterval(pollingInterval)
      pollingInterval = null
    }
  }

  onMounted(() => {
    startPolling()
  })

  onUnmounted(() => {
    stopPolling()
  })

  return {
    notifications,
    unreadCount,
    isOpen,
    fetchNotifications,
    fetchUnreadCount,
    markRead,
    markAllRead,
    toggle,
    close,
    startPolling,
    stopPolling,
  }
}
