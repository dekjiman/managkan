<template>
  <SettingsLayout>
    <div>
      <div class="flex items-center justify-between mb-8">
        <div>
          <h2 class="text-sm font-bold text-light-1000 dark:text-dark-1000">API Keys</h2>
          <p class="text-sm text-light-800 dark:text-dark-700 mt-1">Manage API keys for programmatic access to your workspace.</p>
        </div>
        <Button size="sm" @click="showCreate = true">
          <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
          Create key
        </Button>
      </div>

      <!-- Loading -->
      <div v-if="isLoading" class="flex justify-center py-12">
        <LoadingSpinner />
      </div>

      <!-- Empty -->
      <div v-else-if="keys.length === 0" class="text-center py-12 text-sm text-light-800 dark:text-dark-700">
        No API keys yet. Create one to get started.
      </div>

      <!-- Key List -->
      <div v-else class="space-y-3">
        <div v-for="key in keys" :key="key.id" class="rounded-lg border border-light-300 dark:border-dark-400 p-4">
          <div class="flex items-start justify-between">
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2">
                <p class="text-sm font-medium text-light-1000 dark:text-dark-1000">{{ key.name }}</p>
                <Badge v-if="!key.active" variant="danger">Revoked</Badge>
              </div>
              <div class="flex items-center gap-2 mt-1.5">
                <code class="text-xs text-light-700 dark:text-dark-700 bg-light-200 dark:bg-dark-300 px-2 py-0.5 rounded font-mono">{{ key.keyPrefix }}...</code>
                <button @click="copyKey(key.keyPrefix + '...')" class="text-light-700 hover:text-light-1000 dark:text-dark-600 dark:hover:text-dark-1000">
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                </button>
              </div>
              <p class="text-xs text-light-700 dark:text-dark-700 mt-1.5">
                Created {{ formatDate(key.createdAt) }}
                <span v-if="key.lastUsedAt" class="ml-2">Last used {{ formatDate(key.lastUsedAt) }}</span>
              </p>
            </div>
            <div v-if="key.active" class="flex items-center gap-2 shrink-0 ml-4">
              <Button v-if="showRevoke !== key.id" variant="ghost" size="sm" @click="showRevoke = key.id" class="text-red-500 hover:text-red-600">
                Revoke
              </Button>
              <div v-else class="flex items-center gap-2">
                <span class="text-xs text-red-500">Are you sure?</span>
                <Button variant="danger" size="sm" :loading="revokingId === key.id" @click="revokeKey(key.id)">Yes</Button>
                <Button variant="ghost" size="sm" @click="showRevoke = ''">No</Button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Create Key Modal -->
      <Modal :is-open="showCreate" @close="closeCreate">
        <template #header>Create API key</template>
        <div class="space-y-4">
          <Input v-model="newKeyName" label="Key name" placeholder="e.g. Production CI" />

          <div>
            <label class="block text-xs font-medium text-light-900 dark:text-dark-800 mb-1">Permissions</label>
            <div class="space-y-2">
              <label class="flex items-center gap-2 text-sm text-light-900 dark:text-dark-800">
                <input type="checkbox" v-model="newKeyPerms" value="read" checked class="h-4 w-4" />
                Read
              </label>
              <label class="flex items-center gap-2 text-sm text-light-900 dark:text-dark-800">
                <input type="checkbox" v-model="newKeyPerms" value="write" class="h-4 w-4" />
                Write
              </label>
              <label class="flex items-center gap-2 text-sm text-light-900 dark:text-dark-800">
                <input type="checkbox" v-model="newKeyPerms" value="admin" class="h-4 w-4" />
                Admin
              </label>
            </div>
          </div>
        </div>
        <template #footer>
          <div class="flex justify-end gap-2">
            <Button variant="secondary" size="sm" @click="closeCreate">Cancel</Button>
            <Button size="sm" :loading="creating" :disabled="!newKeyName.trim() || newKeyPerms.length === 0" @click="createKey">Create</Button>
          </div>
        </template>
      </Modal>

      <!-- New Key Reveal -->
      <Modal :is-open="!!revealedKey" @close="revealedKey = ''">
        <template #header>API key created</template>
        <div>
          <p class="text-sm text-light-800 dark:text-dark-700 mb-3">Copy this key now. You won't be able to see it again.</p>
          <div class="flex items-center gap-2">
            <code class="flex-1 text-xs font-mono bg-light-200 dark:bg-dark-300 px-3 py-2 rounded select-all break-all">{{ revealedKey }}</code>
            <Button size="sm" @click="copyKey(revealedKey)">Copy</Button>
          </div>
        </div>
        <template #footer>
          <div class="flex justify-end">
            <Button variant="secondary" size="sm" @click="revealedKey = ''">Done</Button>
          </div>
        </template>
      </Modal>
    </div>
  </SettingsLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useToast } from '@/composables/useToast'
import { useRequireAdmin } from '@/composables/useRequireAdmin'
import { apiKeyService } from '@/services/apikeys.service'
import SettingsLayout from '@/components/layout/SettingsLayout.vue'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'
import Badge from '@/components/ui/Badge.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import Modal from '@/components/ui/Modal.vue'

const route = useRoute()
const toast = useToast()

useRequireAdmin()
const workspaceSlug = computed(() => route.params.workspaceSlug as string)

interface ApiKey {
  id: string
  name: string
  keyPrefix: string
  active: boolean
  createdAt: string
  lastUsedAt: string | null
}

const keys = ref<ApiKey[]>([])
const isLoading = ref(true)

const showCreate = ref(false)
const newKeyName = ref('')
const newKeyPerms = ref<string[]>(['read'])
const creating = ref(false)
const revealedKey = ref('')
const revealedKeyId = ref('')

const showRevoke = ref('')
const revokingId = ref('')

onMounted(fetchKeys)

async function fetchKeys() {
  isLoading.value = true
  try {
    const res = await apiKeyService.getByWorkspace(workspaceSlug.value)
    keys.value = res.data || []
  } catch {
    keys.value = []
  } finally {
    isLoading.value = false
  }
}

function closeCreate() {
  showCreate.value = false
  newKeyName.value = ''
  newKeyPerms.value = ['read']
}

async function createKey() {
  if (!newKeyName.value.trim() || newKeyPerms.value.length === 0) return
  creating.value = true
  try {
    const res = await apiKeyService.create(workspaceSlug.value, {
      name: newKeyName.value.trim(),
      permissions: newKeyPerms.value,
    })
    revealedKey.value = res.data.fullKey
    revealedKeyId.value = res.data.publicId
    closeCreate()
    await fetchKeys()
    toast.success('API key created')
  } catch (err: any) {
    toast.error(err?.response?.data?.message || 'Failed to create API key')
  } finally {
    creating.value = false
  }
}

async function revokeKey(id: string) {
  revokingId.value = id
  try {
    await apiKeyService.revoke(workspaceSlug.value, id)
    showRevoke.value = ''
    await fetchKeys()
    toast.success('API key revoked')
  } catch (err: any) {
    toast.error(err?.response?.data?.message || 'Failed to revoke key')
  } finally {
    revokingId.value = ''
  }
}

function copyKey(text: string) {
  navigator.clipboard.writeText(text)
  toast.success('Copied to clipboard')
}

function formatDate(date: string) {
  return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>
