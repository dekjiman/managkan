<template>
  <div class="max-w-[1100px] mx-auto px-5 py-6 md:px-28 md:py-12">
    <div class="mb-8 flex w-full justify-between">
      <h1 class="font-bold tracking-tight text-light-1000 dark:text-dark-1000 text-lg">{{ title }}</h1>
    </div>

    <!-- Tabs -->
    <div v-if="showTabs" class="border-b border-light-300 dark:border-dark-400 mb-8">
      <nav class="flex space-x-8 overflow-x-auto">
        <router-link
          v-for="tab in availableTabs"
          :key="tab.key"
          :to="tab.href"
          class="whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors"
          :class="isActive(tab.key) ? 'border-light-1000 text-light-1000 dark:border-dark-1000 dark:text-dark-1000' : 'border-transparent text-light-900 hover:border-light-950 dark:text-dark-900 dark:hover:text-dark-950'"
        >
          {{ tab.label }}
        </router-link>
      </nav>
    </div>

    <!-- Content -->
    <slot />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useWorkspaceStore } from '@/stores/workspace'

withDefaults(defineProps<{
  title?: string
  showTabs?: boolean
}>(), {
  title: 'Settings',
  showTabs: true
})

const route = useRoute()
const workspaceStore = useWorkspaceStore()

const workspace = computed(() => workspaceStore.currentWorkspace)
const isAdmin = computed(() => workspace.value?.role === 'admin')

const slug = computed(() => workspace.value?.slug || route.params.workspaceSlug || '')

const availableTabs = computed(() => {
  const s = slug.value
  if (!s) return []
  return [
    { key: 'account', label: 'Account', href: '/settings/account', condition: true },
    { key: 'workspace', label: 'Workspace', href: `/${s}/settings`, condition: isAdmin.value },
    { key: 'permissions', label: 'Permissions', href: `/${s}/settings/permissions`, condition: isAdmin.value },
    { key: 'billing', label: 'Billing', href: `/${s}/settings/billing`, condition: isAdmin.value },
    { key: 'api', label: 'API', href: `/${s}/settings/api`, condition: isAdmin.value },
    { key: 'webhooks', label: 'Webhooks', href: `/${s}/settings/webhooks`, condition: isAdmin.value },
    { key: 'integrations', label: 'Integrations', href: `/${s}/settings/integrations`, condition: isAdmin.value },
  ].filter(t => t.condition)
})

function isActive(key: string) {
  if (key === 'account') return route.path === '/settings/account'
  return route.path.includes(`/settings/${key}`) || (key === 'workspace' && route.path.endsWith('/settings') && !['permissions', 'billing', 'api', 'webhooks', 'integrations'].some(k => route.path.includes(k)))
}
</script>
