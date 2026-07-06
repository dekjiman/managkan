<template>
  <SettingsLayout>
    <div>
      <div class="flex items-center justify-between mb-6">
        <div>
          <h2 class="text-sm font-bold text-light-1000 dark:text-dark-1000">Integrations</h2>
          <p class="text-sm text-light-800 dark:text-dark-700 mt-1">Connect third-party services to extend your workspace.</p>
        </div>
        <Input v-model="search" placeholder="Search integrations..." class="max-w-[240px]" />
      </div>

      <div v-if="isLoading" class="flex justify-center py-12">
        <LoadingSpinner />
      </div>

      <div v-else class="space-y-4">
        <div v-for="integration in filtered" :key="integration.id" class="flex items-center justify-between rounded-lg border border-light-300 dark:border-dark-400 p-4">
          <div class="flex items-center gap-3 min-w-0">
            <div class="w-9 h-9 rounded-lg shrink-0 flex items-center justify-center" :class="integration.bg">
              <img v-if="integration.icon" :src="integration.icon" class="w-5 h-5" />
              <svg v-else class="w-5 h-5" :class="integration.color" fill="currentColor" viewBox="0 0 24 24"><path :d="integration.svg" /></svg>
            </div>
            <div class="min-w-0">
              <div class="flex items-center gap-2">
                <p class="text-sm font-medium text-light-1000 dark:text-dark-1000">{{ integration.name }}</p>
                <Badge v-if="integration.connected" variant="success">Connected</Badge>
                <Badge v-else variant="default">Available</Badge>
              </div>
              <p class="text-xs text-light-800 dark:text-dark-700 mt-0.5">{{ integration.description }}</p>
            </div>
          </div>
          <div class="flex items-center gap-2 shrink-0 ml-4">
            <template v-if="integration.connected">
              <Button variant="secondary" size="sm" @click="configure(integration)">Configure</Button>
              <Button variant="ghost" size="sm" @click="confirmDisconnect(integration)" class="text-red-500 hover:text-red-600">
                Disconnect
              </Button>
            </template>
            <Button v-else variant="secondary" size="sm" @click="connect(integration)">Connect</Button>
          </div>
        </div>

        <div v-if="filtered.length === 0" class="text-center py-12 text-sm text-light-800 dark:text-dark-700">
          No integrations match your search.
        </div>
      </div>
    </div>
  </SettingsLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useToast } from '@/composables/useToast'
import { useRequireAdmin } from '@/composables/useRequireAdmin'
import { integrationService } from '@/services/integrations.service'
import SettingsLayout from '@/components/layout/SettingsLayout.vue'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'
import Badge from '@/components/ui/Badge.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const route = useRoute()
const toast = useToast()

useRequireAdmin()
const workspaceSlug = computed(() => route.params.workspaceSlug as string)

interface Integration {
  id: string
  name: string
  description: string
  connected: boolean
  bg: string
  color: string
  svg: string
  icon?: string
}

const search = ref('')
const isLoading = ref(true)

const allIntegrations = ref<Integration[]>([])

const filtered = computed(() => {
  if (!search.value.trim()) return allIntegrations.value
  const q = search.value.toLowerCase()
  return allIntegrations.value.filter(
    (i) => i.name.toLowerCase().includes(q) || i.description.toLowerCase().includes(q)
  )
})

const defaultIntegrations: Integration[] = [
  { id: 'slack', name: 'Slack', description: 'Send card updates to Slack channels', connected: false, bg: 'bg-purple-100 dark:bg-purple-900/30', color: 'text-purple-600', svg: 'M9 1C4.03 1 0 5.03 0 10s4.03 9 9 9c.23 0 .46 0 .68-.03a7.74 7.74 0 01-1.66-2.17A4.91 4.91 0 006.5 12a4.93 4.93 0 013.07-4.57A4.93 4.93 0 0013 2.5 4.92 4.92 0 009 1z' },
  { id: 'github', name: 'GitHub', description: 'Link repositories and sync pull requests', connected: false, bg: 'bg-gray-100 dark:bg-gray-800', color: 'text-gray-900 dark:text-gray-100', svg: 'M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z' },
  { id: 'gitlab', name: 'GitLab', description: 'Connect GitLab repositories and merge requests', connected: false, bg: 'bg-orange-100 dark:bg-orange-900/30', color: 'text-orange-600', svg: 'M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z' },
  { id: 'jira', name: 'Jira', description: 'Link Jira issues to cards', connected: false, bg: 'bg-blue-100 dark:bg-blue-900/30', color: 'text-blue-600', svg: 'M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm-1 8h2v8h-2V8zm4 2h2v6h-2v-6zm-8 2h2v4H7v-4z' },
  { id: 'trello', name: 'Trello', description: 'Import boards and cards from Trello', connected: false, bg: 'bg-blue-100 dark:bg-blue-900/30', color: 'text-blue-600', svg: 'M21 5H3a1 1 0 00-1 1v12a1 1 0 001 1h18a1 1 0 001-1V6a1 1 0 00-1-1zm-1 12H4V7h16v10zM6 9h4v2H6V9zm0 4h6v2H6v-2zm10-4h2v6h-2V9z' },
  { id: 'notion', name: 'Notion', description: 'Sync databases and pages', connected: false, bg: 'bg-black/5 dark:bg-white/10', color: 'text-black dark:text-white', svg: 'M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.5 7.5v9h-3v-9h3zm-5 2v7h-3v-7h3zm-5 2v5h-3v-5h3z' },
  { id: 'figma', name: 'Figma', description: 'Embed Figma designs in cards', connected: false, bg: 'bg-green-100 dark:bg-green-900/30', color: 'text-green-600', svg: 'M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm-1 17.5c0 .828-.672 1.5-1.5 1.5s-1.5-.672-1.5-1.5v-2h3v2zm0-4H8V7h3v6.5zm6 0c0 .828-.672 1.5-1.5 1.5s-1.5-.672-1.5-1.5v-2h3v2zm0-4h-3V7h3v2.5z' },
  { id: 'zapier', name: 'Zapier', description: 'Connect with 3000+ apps via Zapier', connected: false, bg: 'bg-orange-100 dark:bg-orange-900/30', color: 'text-orange-600', svg: 'M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.5 7.5l-7 7-3-3-1.5 1.5 4.5 4.5 8.5-8.5-1.5-1.5z' },
  { id: 'gmail', name: 'Gmail', description: 'Create cards from emails', connected: false, bg: 'bg-red-100 dark:bg-red-900/30', color: 'text-red-600', svg: 'M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm6 15.5c0 .828-.672 1.5-1.5 1.5h-9c-.828 0-1.5-.672-1.5-1.5v-7c0-.828.672-1.5 1.5-1.5h9c.828 0 1.5.672 1.5 1.5v7zm-1.5-6.5h-9v1l4.5 2.5L16.5 10V9z' },
  { id: 'outlook', name: 'Outlook', description: 'Create cards from Outlook emails', connected: false, bg: 'bg-blue-100 dark:bg-blue-900/30', color: 'text-blue-600', svg: 'M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm8 15.5c0 .828-.672 1.5-1.5 1.5h-9c-.828 0-1.5-.672-1.5-1.5v-7c0-.828.672-1.5 1.5-1.5h9c.828 0 1.5.672 1.5 1.5v7z' },
]

onMounted(async () => {
  try {
    const res = await integrationService.getByWorkspace(workspaceSlug.value)
    const state = res.data || {}
    allIntegrations.value = defaultIntegrations.map((d) => ({
      ...d,
      connected: state[d.id]?.connected || false,
    }))
  } catch {
    allIntegrations.value = defaultIntegrations
  } finally {
    isLoading.value = false
  }
})

async function connect(integration: Integration) {
  try {
    await integrationService.setConnected(workspaceSlug.value, { integrationId: integration.id, connected: true })
    integration.connected = true
    toast.success(`Connected to ${integration.name}`)
  } catch (err: any) {
    toast.error(err?.response?.data?.message || 'Failed to connect')
  }
}

async function confirmDisconnect(integration: Integration) {
  if (!confirm(`Disconnect from ${integration.name}?`)) return
  try {
    await integrationService.setConnected(workspaceSlug.value, { integrationId: integration.id, connected: false })
    integration.connected = false
    toast.success(`Disconnected from ${integration.name}`)
  } catch (err: any) {
    toast.error(err?.response?.data?.message || 'Failed to disconnect')
  }
}

function configure(integration: Integration) {
  toast.info(`Configuration for ${integration.name} coming soon`)
}
</script>
