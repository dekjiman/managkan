<template>
  <nav
    :class="[
      'flex h-full w-64 flex-col justify-between border-r border-light-300 bg-light-100 p-3 dark:border-dark-300 dark:bg-dark-100',
      isCollapsed && 'md:w-auto'
    ]"
  >
    <div>
      <!-- Logo + Collapse Toggle -->
      <div class="hidden h-[45px] items-center justify-between pb-3 md:flex">
        <router-link v-if="!isCollapsed" to="/dashboard" class="block">
          <h1 class="pl-2 text-[16px] font-bold tracking-tight text-light-1000 dark:text-dark-1000">
            ManagPro
          </h1>
        </router-link>
        <button
          @click="toggleCollapse"
          :class="[
            'flex h-8 items-center justify-center rounded-md hover:bg-light-200 dark:hover:bg-dark-200',
            isCollapsed ? 'w-full' : 'w-8'
          ]"
        >
          <svg v-if="isCollapsed" class="w-4 h-4 text-light-900 dark:text-dark-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
          </svg>
          <svg v-else class="w-4 h-4 text-light-900 dark:text-dark-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
          </svg>
        </button>
      </div>
      <div class="mx-1 mb-4 hidden w-auto border-b border-light-300 dark:border-dark-400 md:block" />

      <!-- Workspace Selector -->
      <div class="relative mb-2">
        <button
          @click="showWorkspaceMenu = !showWorkspaceMenu"
          class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-light-1000 dark:text-dark-1000 hover:bg-light-200 dark:hover:bg-dark-200 transition-colors"
        >
          <div class="w-5 h-5 rounded bg-primary-100 dark:bg-primary-200 flex items-center justify-center text-[10px] font-semibold text-primary-700 dark:text-primary-800 shrink-0">
            {{ currentWorkspace?.name?.charAt(0).toUpperCase() || 'W' }}
          </div>
          <span class="truncate flex-1 text-left">{{ currentWorkspace?.name || 'Select workspace' }}</span>
          <svg class="w-4 h-4 text-light-700 dark:text-dark-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
          </svg>
        </button>

        <!-- Workspace Dropdown -->
        <div v-if="showWorkspaceMenu" class="absolute left-0 top-full mt-1 w-full bg-light-50 dark:bg-dark-200 rounded-lg shadow-lg border border-light-300 dark:border-dark-400 py-1 z-50">
          <router-link
            v-for="ws in workspaces"
            :key="ws.publicId"
            :to="`/${ws.slug}`"
            class="flex items-center gap-2 px-3 py-2 text-sm hover:bg-light-200 dark:hover:bg-dark-300 transition-colors"
            :class="{ 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400': currentWorkspace?.publicId === ws.publicId }"
            @click="showWorkspaceMenu = false; emit('close')"
          >
            <div class="w-5 h-5 rounded bg-primary-100 dark:bg-primary-200 flex items-center justify-center text-[10px] font-semibold text-primary-700 dark:text-primary-800 shrink-0">
              {{ ws.name.charAt(0).toUpperCase() }}
            </div>
            <span class="truncate">{{ ws.name }}</span>
          </router-link>
          <div class="border-t border-light-300 dark:border-dark-400 my-1" />
          <button
            @click="showCreateModal = true; showWorkspaceMenu = false"
            :disabled="atWorkspaceLimit"
            class="w-full flex items-center gap-2 px-3 py-2 text-sm transition-colors"
            :class="atWorkspaceLimit
              ? 'text-light-600 dark:text-dark-600 cursor-not-allowed'
              : 'text-light-700 dark:text-dark-600 hover:bg-light-200 dark:hover:bg-dark-300'"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            <span v-if="!isCollapsed">
              {{ atWorkspaceLimit ? 'Limit reached' : 'New workspace' }}
            </span>
          </button>
          <p v-if="!isCollapsed" class="px-3 pb-1 text-[11px] text-light-700 dark:text-dark-700">
            {{ workspaces.length }}/{{ workspaceLimit }} workspaces
          </p>
        </div>
      </div>

      <!-- Navigation Items -->
      <ul role="list" class="space-y-1">
        <li v-for="item in navigation" :key="item.name">
          <router-link
            :to="item.href"
            class="nav-item"
            :class="{ 'nav-item-active': isActive(item.href) }"
            @click="emit('close')"
          >
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" v-html="item.icon" />
            <span v-if="!isCollapsed">{{ item.name }}</span>
            <span
              v-if="item.name === 'Notifications' && unreadCount > 0 && !isCollapsed"
              class="ml-auto min-w-[18px] h-[18px] px-1 flex items-center justify-center text-[10px] font-bold text-white bg-red-500 rounded-full"
            >
              {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
          </router-link>
        </li>
      </ul>
    </div>

    <!-- User Menu (Bottom) -->
    <div class="space-y-2">
      <div class="relative">
        <button
          @click.stop="showUserMenu = !showUserMenu"
          class="flex w-full items-center rounded-md p-1.5 text-light-1000 hover:bg-light-200 dark:text-dark-1000 dark:hover:bg-dark-200 transition-colors"
        >
          <Avatar :name="user?.name || 'User'" :src="user?.image" size="sm" />
          <span v-if="!isCollapsed" class="mx-2 truncate text-sm">{{ user?.name || 'User' }}</span>
        </button>

        <!-- User Dropdown -->
        <Transition name="dropdown">
          <div v-if="showUserMenu" class="absolute bottom-full left-0 mb-2 w-full bg-light-50 dark:bg-dark-200 rounded-md border border-light-600 dark:border-dark-600 shadow-lg py-1 z-50">
            <!-- Theme -->
            <div class="p-1">
              <div class="px-3 py-2 text-xs text-light-800 dark:text-dark-700">Theme</div>
              <button @click="setTheme('system')" class="w-full flex items-center px-3 py-2 text-xs hover:bg-light-200 dark:hover:bg-dark-300 rounded transition-colors">
                <span :class="['mr-4 h-1.5 w-1.5 rounded-full', theme === 'system' ? 'bg-light-900 dark:bg-dark-900' : 'invisible']" />
                System
              </button>
              <button @click="setTheme('dark')" class="w-full flex items-center px-3 py-2 text-xs hover:bg-light-200 dark:hover:bg-dark-300 rounded transition-colors">
                <span :class="['mr-4 h-1.5 w-1.5 rounded-full', theme === 'dark' ? 'bg-light-900 dark:bg-dark-900' : 'invisible']" />
                Dark
              </button>
              <button @click="setTheme('light')" class="w-full flex items-center px-3 py-2 text-xs hover:bg-light-200 dark:hover:bg-dark-300 rounded transition-colors">
                <span :class="['mr-4 h-1.5 w-1.5 rounded-full', theme === 'light' ? 'bg-light-900 dark:bg-dark-900' : 'invisible']" />
                Light
              </button>
            </div>

            <div class="border-t border-light-600 dark:border-dark-600 p-1" />

            <!-- Links -->
            <div class="p-1">
              <router-link to="/settings/account" class="block w-full px-3 py-2 text-xs hover:bg-light-200 dark:hover:bg-dark-300 rounded transition-colors" @click="showUserMenu = false; emit('close')">
                Account
              </router-link>
            </div>

            <div class="border-t border-light-600 dark:border-dark-600 p-1" />

            <!-- Logout -->
            <div class="p-1">
              <button @click="handleLogout" class="w-full px-3 py-2 text-xs text-left hover:bg-light-200 dark:hover:bg-dark-300 rounded transition-colors">
                Logout
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </div>
  </nav>

  <!-- Create Workspace Modal -->
  <Modal :is-open="showCreateModal" @close="showCreateModal = false">
    <template #header>Create Workspace</template>
    <form @submit.prevent="createWorkspace" class="space-y-4">
      <Input v-model="newWorkspace.name" label="Name" placeholder="My Workspace" required />
      <Input v-model="newWorkspace.description" label="Description (optional)" placeholder="Description" />
    </form>
    <template #footer>
      <div class="flex justify-end gap-2">
        <Button variant="secondary" @click="showCreateModal = false" size="sm">Cancel</Button>
        <Button @click="createWorkspace" :loading="isCreating" size="sm">Create</Button>
      </div>
    </template>
  </Modal>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useWorkspaceStore } from '@/stores/workspace'
import { useAuthStore } from '@/stores/auth'
import { useUIStore } from '@/stores/ui'
import { useToast } from '@/composables/useToast'
import { useAuth } from '@/composables/useAuth'
import { workspaceService } from '@/services/workspace.service'
import { useNotifications } from '@/composables/useNotifications'
import { planService } from '@/services/plan.service'
import Modal from '@/components/ui/Modal.vue'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'
import Avatar from '@/components/ui/Avatar.vue'

const emit = defineEmits<{
  close: []
}>()

const router = useRouter()
const route = useRoute()
const workspaceStore = useWorkspaceStore()
const authStore = useAuthStore()
const uiStore = useUIStore()
const toast = useToast()
const { logout } = useAuth()
const { unreadCount } = useNotifications()

const currentWorkspace = computed(() => workspaceStore.currentWorkspace)
const workspaces = ref<any[]>([])
const showWorkspaceMenu = ref(false)
const showUserMenu = ref(false)
const showCreateModal = ref(false)
const isCreating = ref(false)
const newWorkspace = ref({ name: '', description: '' })
const isCollapsed = ref(false)
const workspaceLimit = ref(3)

const atWorkspaceLimit = computed(() => workspaces.value.length >= workspaceLimit.value)

const user = computed(() => authStore.user)

const theme = computed(() => uiStore.theme)

const navigation = computed(() => {
  const slug = currentWorkspace.value?.slug || (route.params.workspaceSlug as string)
  if (!slug) return []
  return [
    { name: 'Boards', href: `/${slug}`, icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />' },
    { name: 'Templates', href: `/${slug}/templates`, icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />' },
    { name: 'Members', href: `/${slug}/members`, icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />' },
    { name: 'Notifications', href: `/${slug}/notifications`, icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />' },
    { name: 'Settings', href: `/${slug}/settings`, icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />' },
  ]
})

onMounted(async () => {
  await workspaceStore.fetchWorkspaces()
  workspaces.value = workspaceStore.workspaces

  try {
    const res = await planService.getByName('free')
    if (res.data) {
      workspaceLimit.value = res.data.workspaceLimit ?? 3
    }
  } catch {
    // use default
  }

  const saved = localStorage.getItem('managpro_sidebar-collapsed')
  if (saved !== null) {
    isCollapsed.value = JSON.parse(saved)
  }
})

onUnmounted(() => {
  localStorage.setItem('managpro_sidebar-collapsed', JSON.stringify(isCollapsed.value))
})

function toggleCollapse() {
  isCollapsed.value = !isCollapsed.value
}

function isActive(href: string) {
  if (href === '') return route.path.includes(`/${currentWorkspace.value?.slug}`)
  return route.path.includes(href)
}

function setTheme(newTheme: string) {
  uiStore.setTheme(newTheme)
  showUserMenu.value = false
}

function handleLogout() {
  showUserMenu.value = false
  emit('close')
  logout()
}

function handleClickOutside(e: MouseEvent) {
  const target = e.target as HTMLElement
  if (!target.closest('.relative')) {
    showWorkspaceMenu.value = false
    showUserMenu.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

async function createWorkspace() {
  if (!newWorkspace.value.name) return

  isCreating.value = true
  try {
    await workspaceService.create(newWorkspace.value)
    await workspaceStore.fetchWorkspaces()
    workspaces.value = workspaceStore.workspaces
    showCreateModal.value = false
    newWorkspace.value = { name: '', description: '' }
    toast.success('Workspace created')
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Failed to create workspace')
  } finally {
    isCreating.value = false
  }
}
</script>

<style scoped>
.nav-item {
  @apply flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-light-1000 dark:text-dark-1000 hover:bg-light-200 dark:hover:bg-dark-200 transition-colors;
}
.nav-item-active {
  @apply !bg-light-200 dark:!bg-dark-200 !text-light-1000 dark:!text-dark-1000;
}
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.15s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(4px);
}
</style>
