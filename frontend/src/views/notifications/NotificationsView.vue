<template>
  <div class="max-w-3xl mx-auto py-8 px-4">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-xl font-bold text-light-1000 dark:text-dark-1000">Notifications</h1>
      <button
        v-if="unreadCount > 0"
        @click="handleMarkAllRead"
        class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400"
      >
        Mark all read
      </button>
    </div>

    <div class="flex gap-1 mb-4 border-b border-light-300 dark:border-dark-400">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        @click="activeTab = tab.key"
        class="px-3 py-2 text-sm font-medium border-b-2 transition-colors"
        :class="activeTab === tab.key
          ? 'border-primary-500 text-primary-600 dark:text-primary-400'
          : 'border-transparent text-light-700 dark:text-dark-700 hover:text-light-1000 dark:hover:text-dark-1000'"
      >
        {{ tab.label }}
        <span
          v-if="tab.key === 'unread' && unreadCount > 0"
          class="ml-1 px-1.5 py-0.5 text-[10px] font-bold text-white bg-red-500 rounded-full"
        >
          {{ unreadCount }}
        </span>
      </button>
    </div>

    <div v-if="loading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-light-1000 dark:border-dark-1000" />
    </div>

    <div v-else-if="filteredNotifications.length === 0" class="py-12 text-center">
      <svg class="mx-auto h-10 w-10 text-light-600 dark:text-dark-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
      </svg>
      <p class="mt-3 text-sm text-light-700 dark:text-dark-700">
        {{ activeTab === 'unread' ? 'No unread notifications' : 'No notifications yet' }}
      </p>
    </div>

    <div v-else class="space-y-1">
      <button
        v-for="n in filteredNotifications"
        :key="n.publicId"
        @click="handleClick(n)"
        class="w-full text-left px-4 py-3 rounded-lg transition-colors"
        :class="[
          n.read
            ? 'hover:bg-light-200 dark:hover:bg-dark-300'
            : 'bg-primary-50 dark:bg-primary-900/10 hover:bg-primary-100 dark:hover:bg-primary-900/20',
        ]"
      >
        <div class="flex items-start gap-3">
          <div class="mt-0.5 shrink-0">
            <div v-if="n.type === 'comment_added' || n.type === 'comment_mentioned'" class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
              <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
              </svg>
            </div>
            <div v-else-if="n.type === 'member_added' || n.type === 'member_invited'" class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
              <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
              </svg>
            </div>
            <div v-else-if="n.type === 'payment_success'" class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
              <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div v-else-if="n.type === 'payment_failed'" class="w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
              <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div v-else class="w-8 h-8 rounded-full bg-light-300 dark:bg-dark-400 flex items-center justify-center">
              <svg class="w-4 h-4 text-light-700 dark:text-dark-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
            </div>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm text-light-1000 dark:text-dark-1000" :class="{ 'font-medium': !n.read }">
              {{ n.title }}
            </p>
            <p v-if="(n.type === 'comment_added' || n.type === 'comment_mentioned') && n.data?.comment" class="mt-1 text-xs text-light-800 dark:text-dark-800 bg-light-200 dark:bg-dark-300 rounded px-2 py-1 line-clamp-2">
              "{{ n.data.comment }}"
            </p>
            <p class="text-xs text-light-700 dark:text-dark-700 mt-0.5">
              {{ formatTimeAgo(n.createdAt) }}
            </p>
          </div>
          <div v-if="!n.read" class="mt-2 shrink-0">
            <div class="w-2 h-2 rounded-full bg-primary-500" />
          </div>
        </div>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { notificationService, type Notification } from '@/services/notification.service'
import { formatTimeAgo } from '@/utils/date'
import { useNotifications } from '@/composables/useNotifications'

const router = useRouter()
const { unreadCount, fetchUnreadCount } = useNotifications()

const notifications = ref<Notification[]>([])
const loading = ref(true)
const activeTab = ref<'all' | 'unread'>('all')

const tabs = [
  { key: 'all' as const, label: 'All' },
  { key: 'unread' as const, label: 'Unread' },
]

const filteredNotifications = computed(() => {
  if (activeTab.value === 'unread') {
    return notifications.value.filter(n => !n.read)
  }
  return notifications.value
})

async function loadNotifications() {
  loading.value = true
  try {
    const res = await notificationService.getAll()
    notifications.value = res.data
  } catch {
    // silent
  } finally {
    loading.value = false
  }
}

async function handleClick(n: Notification) {
  if (!n.read) {
    await notificationService.markRead(n.publicId)
    n.read = true
    fetchUnreadCount()
  }
  if (n.entityUrl) {
    router.push(n.entityUrl)
  }
}

async function handleMarkAllRead() {
  await notificationService.markAllRead()
  notifications.value.forEach(n => n.read = true)
  fetchUnreadCount()
}

onMounted(loadNotifications)
</script>
