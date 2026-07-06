<template>
  <SettingsLayout>
    <div>
      <div class="flex items-center justify-between mb-8">
        <div>
          <h2 class="text-sm font-bold text-light-1000 dark:text-dark-1000">Webhooks</h2>
          <p class="text-sm text-light-800 dark:text-dark-700 mt-1">Receive HTTP notifications when card events happen in this workspace.</p>
        </div>
        <Button size="sm" @click="showCreate = true">
          <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
          Add webhook
        </Button>
      </div>

      <div v-if="isLoading" class="flex justify-center py-12">
        <LoadingSpinner />
      </div>

      <div v-else-if="webhooks.length === 0" class="text-center py-12 text-sm text-light-800 dark:text-dark-700">
        No webhooks configured. Add a webhook to receive notifications.
      </div>

      <div v-else class="space-y-3">
        <div v-for="wh in webhooks" :key="wh.id" class="rounded-lg border border-light-300 dark:border-dark-400 p-4">
          <div class="flex items-start justify-between">
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full" :class="wh.active ? 'bg-green-500' : 'bg-red-500'" />
                <p class="text-sm font-medium text-light-1000 dark:text-dark-1000">{{ wh.name }}</p>
              </div>
              <code class="block text-xs text-light-700 dark:text-dark-700 mt-1.5 font-mono truncate max-w-lg">{{ wh.url }}</code>
              <div class="flex items-center gap-2 mt-2">
                <Badge v-for="ev in parseEvents(wh)" :key="ev" variant="info" class="text-[10px]">{{ ev }}</Badge>
              </div>
              <p class="text-xs text-light-700 dark:text-dark-700 mt-2">
                Created {{ formatDate(wh.createdAt) }}
                <span v-if="wh.lastDeliveryAt" class="ml-2">Last delivery {{ formatDate(wh.lastDeliveryAt) }}</span>
                <span v-if="wh.lastDeliveryStatus" class="ml-2 text-xs" :class="wh.lastDeliveryStatus === 'success' ? 'text-green-500' : 'text-red-500'">({{ wh.lastDeliveryStatus }})</span>
              </p>
            </div>
            <div class="flex items-center gap-1 shrink-0 ml-4">
              <Button variant="ghost" size="sm" @click="testWebhook(wh)" :loading="testingId === wh.id" title="Test">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              </Button>
              <Button v-if="showDelete !== wh.id" variant="ghost" size="sm" @click="showDelete = wh.id" class="text-red-500 hover:text-red-600" title="Delete">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
              </Button>
              <div v-else class="flex items-center gap-2">
                <span class="text-xs text-red-500">Delete?</span>
                <Button variant="danger" size="sm" :loading="deletingId === wh.id" @click="deleteWebhook(wh.id)">Yes</Button>
                <Button variant="ghost" size="sm" @click="showDelete = ''">No</Button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Create Webhook Modal -->
      <Modal :is-open="showCreate" @close="closeCreate">
        <template #header>Add webhook</template>
        <div class="space-y-4">
          <Input v-model="form.name" label="Name" placeholder="e.g. Slack notifications" />
          <Input v-model="form.url" label="Payload URL" placeholder="https://hooks.example.com/webhook" type="url" />
          <div>
            <label class="block text-xs font-medium text-light-900 dark:text-dark-800 mb-1">Events</label>
            <div class="grid grid-cols-2 gap-1.5">
              <label v-for="ev in availableEvents" :key="ev.value" class="flex items-center gap-2 text-sm text-light-900 dark:text-dark-800">
                <input type="checkbox" :value="ev.value" v-model="form.events" class="h-4 w-4" />
                {{ ev.label }}
              </label>
            </div>
          </div>
          <p v-if="createError" class="text-xs text-red-500">{{ createError }}</p>
        </div>
        <template #footer>
          <div class="flex justify-end gap-2">
            <Button variant="secondary" size="sm" @click="closeCreate">Cancel</Button>
            <Button size="sm" :loading="creating" :disabled="!form.name.trim() || !form.url.trim() || form.events.length === 0" @click="createWebhook">Create</Button>
          </div>
        </template>
      </Modal>
    </div>
  </SettingsLayout>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useToast } from '@/composables/useToast'
import { useRequireAdmin } from '@/composables/useRequireAdmin'
import { webhookService } from '@/services/webhooks.service'
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

interface Webhook {
  id: string
  name: string
  url: string
  events: string
  active: boolean
  createdAt: string
  lastDeliveryAt: string | null
  lastDeliveryStatus: string | null
}

const webhooks = ref<Webhook[]>([])
const isLoading = ref(true)

const showCreate = ref(false)
const creating = ref(false)
const createError = ref('')
const form = reactive({ name: '', url: '', events: [] as string[] })

const showDelete = ref('')
const deletingId = ref('')
const testingId = ref('')

const availableEvents = [
  { value: 'card.created', label: 'Card created' },
  { value: 'card.updated', label: 'Card updated' },
  { value: 'card.deleted', label: 'Card deleted' },
  { value: 'card.moved', label: 'Card moved' },
  { value: 'card.updated.comment.added', label: 'Comment added' },
  { value: 'card.checklist.updated', label: 'Checklist updated' },
]

function parseEvents(wh: Webhook): string[] {
  if (Array.isArray(wh.events)) return wh.events
  try { return JSON.parse(wh.events) } catch { return [] }
}

onMounted(fetchWebhooks)

async function fetchWebhooks() {
  isLoading.value = true
  try {
    const res = await webhookService.getByWorkspace(workspaceSlug.value)
    webhooks.value = res.data || []
  } catch {
    webhooks.value = []
  } finally {
    isLoading.value = false
  }
}

function closeCreate() {
  showCreate.value = false
  form.name = ''
  form.url = ''
  form.events = []
  createError.value = ''
}

async function createWebhook() {
  if (!form.name.trim() || !form.url.trim() || form.events.length === 0) return
  creating.value = true
  createError.value = ''
  try {
    const res = await webhookService.create(workspaceSlug.value, {
      name: form.name.trim(),
      url: form.url.trim(),
      events: form.events,
    })
    webhooks.value.unshift(res.data)
    closeCreate()
    toast.success('Webhook created')
  } catch (err: any) {
    createError.value = err?.response?.data?.message || 'Failed to create webhook'
  } finally {
    creating.value = false
  }
}

async function deleteWebhook(id: string) {
  deletingId.value = id
  try {
    await webhookService.delete(workspaceSlug.value, id)
    webhooks.value = webhooks.value.filter((w) => w.id !== id)
    showDelete.value = ''
    toast.success('Webhook deleted')
  } catch (err: any) {
    toast.error(err?.response?.data?.message || 'Failed to delete webhook')
  } finally {
    deletingId.value = ''
  }
}

async function testWebhook(wh: Webhook) {
  testingId.value = wh.id
  try {
    await webhookService.test(wh.id)
    toast.success('Test payload sent')
  } catch (err: any) {
    toast.error(err?.response?.data?.message || 'Test failed')
  } finally {
    testingId.value = ''
  }
}

function formatDate(date: string) {
  return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>
