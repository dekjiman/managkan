<template>
  <header class="h-12 shrink-0 bg-light-50 dark:bg-dark-100 border-b border-light-300 dark:border-dark-400 flex items-center justify-between px-4">
    <div class="flex items-center gap-4">
      <div />
    </div>

    <div class="flex items-center gap-1">
      <button
        @click="toggleTheme"
        class="p-2 text-light-700 hover:text-light-1000 dark:text-dark-600 dark:hover:text-dark-1000 rounded-lg hover:bg-light-200 dark:hover:bg-dark-200 transition-colors"
      >
        <svg v-if="isDark" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
        </svg>
      </button>

      <!-- Notifications -->
      <div class="relative">
        <button
          @click.stop="handleNotificationToggle"
          class="relative p-2 text-light-700 hover:text-light-1000 dark:text-dark-600 dark:hover:text-dark-1000 rounded-lg hover:bg-light-200 dark:hover:bg-dark-200 transition-colors"
        >
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
          <span
            v-if="unreadCount > 0"
            class="absolute -top-0.5 -right-0.5 min-w-[16px] h-4 px-1 flex items-center justify-center text-[10px] font-bold text-white bg-red-500 rounded-full"
          >
            {{ unreadCount > 9 ? '9+' : unreadCount }}
          </span>
        </button>

        <Transition name="dropdown">
          <div
            v-if="isNotificationOpen"
            class="absolute right-0 mt-1 w-80 bg-light-50 dark:bg-dark-200 rounded-lg shadow-lg border border-light-300 dark:border-dark-400 z-50"
            @click.stop
          >
            <div class="flex items-center justify-between px-3 py-2 border-b border-light-300 dark:border-dark-400">
              <span class="text-sm font-semibold text-light-1000 dark:text-dark-1000">Notifications</span>
              <button
                v-if="unreadCount > 0"
                @click="handleMarkAllRead"
                class="text-xs text-primary-600 hover:text-primary-700 dark:text-primary-400"
              >
                Mark all read
              </button>
            </div>

            <div v-if="notifLoading" class="flex justify-center py-6">
              <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-light-1000 dark:border-dark-1000" />
            </div>

            <div v-else-if="notificationList.length === 0" class="py-6 text-center text-sm text-light-700 dark:text-dark-700">
              No notifications yet
            </div>

            <div v-else class="max-h-80 overflow-y-auto">
              <button
                v-for="n in notificationList"
                :key="n.publicId"
                @click="handleNotificationClick(n)"
                class="w-full text-left px-3 py-2.5 border-b border-light-200 dark:border-dark-300 last:border-0 hover:bg-light-200 dark:hover:bg-dark-300 transition-colors"
                :class="{ 'bg-primary-50 dark:bg-primary-900/10': !n.read }"
              >
                <p class="text-xs text-light-1000 dark:text-dark-1000 leading-relaxed" :class="{ 'font-medium': !n.read }">
                  {{ n.title }}
                </p>
                <p class="text-[11px] text-light-700 dark:text-dark-700 mt-0.5">
                  {{ formatTimeAgo(n.createdAt) }}
                </p>
              </button>
            </div>
          </div>
        </Transition>
      </div>

      <div class="relative">
        <button
          @click="showUserMenu = !showUserMenu"
          class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-light-200 dark:hover:bg-dark-200 transition-colors"
        >
          <Avatar :name="authStore.user?.name || 'User'" :src="authStore.user?.image" size="sm" />
          <span class="text-sm text-light-900 dark:text-dark-900 max-w-[100px] truncate">{{ authStore.user?.name }}</span>
        </button>

        <Transition name="dropdown">
          <div v-if="showUserMenu" class="absolute right-0 mt-1 w-44 bg-light-50 dark:bg-dark-200 rounded-lg shadow-lg border border-light-300 dark:border-dark-400 py-1 z-50">
            <router-link
              to="/settings/account"
              class="flex items-center gap-2 px-3 py-2 text-sm text-light-900 dark:text-dark-900 hover:bg-light-200 dark:hover:bg-dark-300 transition-colors"
              @click="showUserMenu = false"
            >
              <svg class="w-4 h-4 text-light-700 dark:text-dark-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              Settings
            </router-link>
            <div class="my-1 border-t border-light-300 dark:border-dark-400" />
            <button
              @click="logout"
              class="w-full flex items-center gap-2 px-3 py-2 text-sm text-light-900 dark:text-dark-900 hover:bg-light-200 dark:hover:bg-dark-300 transition-colors"
            >
              <svg class="w-4 h-4 text-light-700 dark:text-dark-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              Logout
            </button>
          </div>
        </Transition>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useUIStore } from '@/stores/ui'
import { useAuth } from '@/composables/useAuth'
import { useNotifications } from '@/composables/useNotifications'
import { formatTimeAgo } from '@/utils/date'
import type { Notification } from '@/services/notification.service'
import Avatar from '@/components/ui/Avatar.vue'

const router = useRouter()
const authStore = useAuthStore()
const uiStore = useUIStore()
const { logout } = useAuth()

const {
  notifications: notificationList,
  unreadCount,
  isOpen: isNotificationOpen,
  fetchNotifications,
  markRead,
  markAllRead,
  toggle: toggleNotifications,
  close: closeNotifications,
} = useNotifications()

const showUserMenu = ref(false)
const notifLoading = ref(false)

const isDark = computed(() => {
  return uiStore.theme === 'dark' ||
    (uiStore.theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)
})

function toggleTheme() {
  const newTheme = isDark.value ? 'light' : 'dark'
  uiStore.setTheme(newTheme)
}

async function handleNotificationToggle() {
  toggleNotifications()
  if (isNotificationOpen.value) {
    notifLoading.value = true
    await fetchNotifications()
    notifLoading.value = false
  }
}

async function handleMarkAllRead() {
  await markAllRead()
}

function handleNotificationClick(n: Notification) {
  markRead(n.publicId)
  closeNotifications()
  if (n.entityUrl) {
    router.push(n.entityUrl)
  }
}

function handleClickOutside(e: MouseEvent) {
  const target = e.target as HTMLElement
  if (!target.closest('.relative')) {
    showUserMenu.value = false
    closeNotifications()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.15s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
</style>
