<template>
  <div v-if="isLoading" class="flex justify-center py-12">
    <LoadingSpinner />
  </div>
  <div v-else-if="card" class="flex h-[calc(100vh-5.5rem)]">
    <!-- Left: Card Content -->
    <div class="flex-1 overflow-y-auto">
      <div class="mx-auto w-full max-w-[800px] p-6 md:p-8">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
          <div class="flex items-center gap-1 text-sm">
            <router-link :to="`/${workspaceSlug}`" class="font-bold text-light-900 dark:text-dark-950 hover:underline">{{ board?.name }}</router-link>
            <svg class="w-2.5 h-2.5 text-light-900 dark:text-dark-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
            <span v-if="card.cardNumber" class="font-bold text-light-700 dark:text-dark-800">{{ card.cardNumber }}</span>
          </div>
          <router-link :to="`/${workspaceSlug}/${boardSlug}`" class="flex h-7 w-7 items-center justify-center rounded-md text-light-900 hover:bg-light-200 dark:text-dark-900 dark:hover:bg-dark-200">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
          </router-link>
        </div>

        <!-- Title -->
        <div class="mb-8">
          <textarea
            :value="form.title"
            @blur="updateTitle"
            @input="autoResize"
            rows="1"
            ref="titleRef"
            class="block w-full resize-none overflow-hidden border-0 bg-transparent p-0 py-0 text-[1.2rem] font-bold leading-relaxed text-light-1000 dark:text-dark-1000 focus:ring-0"
          />
        </div>

        <!-- Description -->
        <div class="mb-10">
          <textarea
            v-model="form.description"
            @blur="updateDescription"
            rows="4"
            class="w-full rounded-md border border-light-300 dark:border-dark-400 bg-white dark:bg-dark-300 p-3 text-sm text-light-1000 dark:text-dark-1000 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 resize-none"
            placeholder="Add a more detailed description..."
          />
        </div>

        <!-- Checklists -->
        <div v-if="card?.checklists?.length" class="mb-8">
          <ChecklistsSection :checklists="card.checklists" @update="refreshCard" />
        </div>

        <!-- Attachments -->
        <div class="mb-6">
          <h3 class="text-sm font-medium text-light-1000 dark:text-dark-1000 mb-3">Attachments</h3>

          <!-- Drop Zone -->
          <div
            @drop.prevent="handleDrop"
            @dragover.prevent="dragOver = true"
            @dragleave.prevent="dragOver = false"
            @click="fileInput?.click()"
            class="relative mb-4 cursor-pointer rounded-lg border-2 border-dashed p-6 text-center transition-colors"
            :class="dragOver ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-light-400 dark:border-dark-400 hover:border-light-600 dark:hover:border-dark-300'"
          >
            <input ref="fileInput" type="file" class="hidden" @change="handleFileSelect" accept="*/*" />
            <svg class="mx-auto h-8 w-8 text-light-700 dark:text-dark-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
            <p class="mt-2 text-sm text-light-700 dark:text-dark-700">
              <span class="font-medium text-primary-600 dark:text-primary-400">Click to upload</span>
              or drag and drop
            </p>
            <p class="text-xs text-light-500 dark:text-dark-800 mt-0.5">Max 50MB per file</p>
          </div>

          <!-- Upload Progress -->
          <div v-if="uploadProgress > 0 && uploadProgress < 100" class="mb-3">
            <div class="flex items-center justify-between text-xs text-light-700 dark:text-dark-700 mb-1">
              <span>Uploading...</span>
              <span>{{ uploadProgress }}%</span>
            </div>
            <div class="h-1.5 w-full rounded-full bg-light-200 dark:bg-dark-300">
              <div class="h-1.5 rounded-full bg-primary-500 transition-all duration-200" :style="{ width: uploadProgress + '%' }" />
            </div>
          </div>

          <!-- Attachments List -->
          <div v-if="attachments.length > 0" class="space-y-2">
            <div v-for="att in attachments" :key="att.publicId" class="group flex items-center gap-3 rounded-lg border border-light-300 dark:border-dark-200 bg-light-50 dark:bg-dark-100 px-3 py-2.5">
              <!-- File Type Icon -->
              <div v-if="isImage(att)" class="h-10 w-10 shrink-0 rounded-lg overflow-hidden border border-light-200 dark:border-dark-300">
                <img :src="att.path" :alt="att.originalFilename" class="h-full w-full object-cover cursor-pointer" @click="viewingImage = att" />
              </div>
              <div v-else class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg" :class="fileIconBg(att)">
                <span v-html="fileIcon(att)" />
              </div>

              <!-- Info -->
              <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium text-light-1000 dark:text-dark-1000">{{ att.originalFilename || 'File' }}</p>
                <div class="flex items-center gap-2 text-xs text-light-500 dark:text-dark-800">
                  <span>{{ formatFileSize(att.size) }}</span>
                  <span v-if="att.createdAt">· {{ formatTimeAgo(att.createdAt) }}</span>
                </div>
              </div>

              <!-- Actions -->
              <div class="flex items-center gap-1 shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                <a v-if="!isImage(att)" :href="att.path" download class="flex h-7 w-7 items-center justify-center rounded-md text-light-700 hover:bg-light-200 dark:text-dark-700 dark:hover:bg-dark-300" title="Download">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 11l5 5 5-5M12 4v12" /></svg>
                </a>
                <button @click="deleteAttachment(att.publicId)" class="flex h-7 w-7 items-center justify-center rounded-md text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20" title="Delete">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
              </div>
            </div>
          </div>

          <div v-else class="py-4 text-center text-xs text-light-500 dark:text-dark-800">
            No attachments yet
          </div>
        </div>

        <!-- Activity -->
        <div class="border-t border-light-300 dark:border-dark-300 pt-12">
          <h2 class="text-base pb-4 font-medium text-light-1000 dark:text-dark-1000">Activity</h2>
          <ActivityFeed
            v-if="card?.id"
            :cardId="card.id"
            :workspaceMembers="board?.workspace?.members || []"
            @activity-added="refreshCard"
          />
        </div>
      </div>
    </div>

    <!-- Right Sidebar -->
    <div class="w-[360px] shrink-0 border-l border-light-300 dark:border-dark-300 bg-light-50 dark:bg-dark-50 p-8 overflow-y-auto">
      <div class="space-y-4">
        <div class="mb-4 flex w-full flex-row">
          <p class="my-2 w-[100px] text-sm font-medium text-light-1000 dark:text-dark-1000">List</p>
          <select v-model="selectedListId" @change="moveToList" class="input flex-1 text-sm">
            <option v-for="list in board?.lists" :key="list.publicId" :value="list.publicId">{{ list.name }}</option>
          </select>
        </div>

        <div class="mb-4 flex w-full flex-row">
          <p class="my-2 w-[100px] text-sm font-medium text-light-1000 dark:text-dark-1000">Labels</p>
          <div class="flex-1">
            <div class="flex flex-wrap gap-1 mb-2">
              <span v-for="label in card.labels" :key="label.publicId" class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-medium" :style="{ backgroundColor: label.colourCode + '20', color: label.colourCode }">
                <span class="w-1.5 h-1.5 rounded-full" :style="{ backgroundColor: label.colourCode }" />
                {{ label.name }}
              </span>
            </div>
            <select @change="toggleLabel($event.target.value); $event.target.value = ''" class="input text-sm">
              <option value="">Add label...</option>
              <option v-for="label in availableLabels" :key="label.publicId" :value="label.publicId">{{ label.name }}</option>
            </select>
          </div>
        </div>

        <div class="mb-4 flex w-full flex-row">
          <p class="my-2 w-[100px] text-sm font-medium text-light-1000 dark:text-dark-1000">Members</p>
          <div class="flex-1">
            <div class="flex flex-wrap gap-1 mb-2">
              <div v-for="member in card.members" :key="member.publicId" class="flex items-center gap-1 rounded-full bg-light-200 dark:bg-dark-300 px-2 py-0.5">
                <Avatar :name="member.userName || 'User'" size="sm" />
                <span class="text-xs text-light-1000 dark:text-dark-1000">{{ member.userName }}</span>
              </div>
            </div>
            <select @change="toggleMember($event.target.value); $event.target.value = ''" class="input text-sm">
              <option value="">Add member...</option>
              <option v-for="member in availableMembers" :key="member.publicId" :value="member.publicId">{{ member.userName || member.email }}</option>
            </select>
          </div>
        </div>

        <div class="mb-4 flex w-full flex-row">
          <p class="my-2 w-[100px] text-sm font-medium text-light-1000 dark:text-dark-1000">Due date</p>
          <input type="date" :value="dueDateValue" @change="updateDueDate($event.target.value)" class="input flex-1 text-sm" />
        </div>
      </div>
    </div>
  </div>

  <!-- Image Viewer -->
  <div v-if="viewingImage" class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center" @click.self="viewingImage = null">
    <button @click="viewingImage = null" class="absolute top-4 right-4 text-white hover:text-gray-300">
      <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
    </button>
      <img :src="viewingImage.path" :alt="viewingImage.originalFilename" class="max-h-[90vh] max-w-[90vw] object-contain" />
  </div>

  <!-- Add Checklist Modal -->
  <div v-if="showAddChecklist" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showAddChecklist = false">
    <div class="bg-light-50 dark:bg-dark-200 rounded-lg shadow-xl p-6 w-96">
      <h3 class="text-sm font-semibold text-light-1000 dark:text-dark-1000 mb-4">Add checklist</h3>
      <input v-model="newChecklistName" @keydown.enter="addChecklist" placeholder="Checklist name..." class="input w-full" autofocus />
      <div class="flex justify-end gap-2 mt-4">
        <Button variant="secondary" @click="showAddChecklist = false" size="sm">Cancel</Button>
        <Button @click="addChecklist" :disabled="!newChecklistName.trim()" size="sm">Add</Button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useToast } from '@/composables/useToast'
import { useWorkspaceStore } from '@/stores/workspace'
import { boardService } from '@/services/board.service'
import { cardService } from '@/services/card.service'
import { checklistService } from '@/services/checklist.service'
import { attachmentService } from '@/services/attachment.service'
import Avatar from '@/components/ui/Avatar.vue'
import Button from '@/components/ui/Button.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import ChecklistsSection from '@/components/card/ChecklistsSection.vue'
import ActivityFeed from '@/components/card/ActivityFeed.vue'
import { formatTimeAgo } from '@/utils/date'

const route = useRoute()
const toast = useToast()
const workspaceStore = useWorkspaceStore()

const workspaceSlug = computed(() => route.params.workspaceSlug as string)
const boardSlug = computed(() => route.params.boardSlug as string)

const card = ref<any>(null)
const board = ref<any>(null)
const isLoading = ref(true)
const titleRef = ref<HTMLTextAreaElement | null>(null)
const fileInput = ref<HTMLInputElement | null>(null)

const form = reactive({
  title: '',
  description: ''
})

const newChecklistName = ref('')
const showAddChecklist = ref(false)
const viewingImage = ref<any>(null)
const selectedListId = ref('')
const dragOver = ref(false)
const uploadProgress = ref(0)

const attachments = computed(() => {
  const all = [...(card.value?.attachments || [])]
  all.sort((a: any, b: any) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime())
  return all
})

const dueDateValue = computed(() => {
  if (!card.value?.dueDate) return ''
  return card.value.dueDate.split('T')[0]
})

const availableLabels = computed(() => {
  const cardLabelIds = (card.value?.labels || []).map((l: any) => l.publicId)
  return (board.value?.labels || []).filter((l: any) => !cardLabelIds.includes(l.publicId))
})

const availableMembers = computed(() => {
  const cardMemberIds = (card.value?.members || []).map((m: any) => m.publicId)
  return (board.value?.workspace?.members || []).filter((m: any) => !cardMemberIds.includes(m.publicId))
})

async function loadCard() {
  isLoading.value = true
  try {
    const cardResponse = await cardService.getById(route.params.cardPublicId as string)
    card.value = cardResponse.data
    form.title = card.value.title
    form.description = card.value.description || ''
    selectedListId.value = card.value.list?.publicId || ''

    const boardResponse = await boardService.getById(route.params.boardSlug as string)
    board.value = boardResponse.data

    if (!workspaceStore.currentWorkspace) {
      await workspaceStore.fetchWorkspaces()
    }
  } finally {
    isLoading.value = false
  }
}

onMounted(loadCard)

watch(() => route.params.cardPublicId, loadCard)

function autoResize(e: Event) {
  const target = e.target as HTMLTextAreaElement
  target.style.height = 'auto'
  target.style.height = target.scrollHeight + 'px'
}

async function updateTitle() {
  if (form.title === card.value.title) return
  try {
    await cardService.update(card.value.publicId, { title: form.title })
    toast.success('Title updated')
  } catch (error: any) {
    toast.error('Failed to update title')
  }
}

async function updateDescription() {
  if (form.description === (card.value.description || '')) return
  try {
    await cardService.update(card.value.publicId, { description: form.description })
    toast.success('Description updated')
  } catch (error: any) {
    toast.error('Failed to update description')
  }
}

async function moveToList() {
  try {
    await cardService.move(card.value.publicId, { listPublicId: selectedListId.value, index: 0 })
    await refreshCard()
    toast.success('Card moved')
  } catch (error: any) {
    toast.error('Failed to move card')
  }
}

async function toggleLabel(labelPublicId: string) {
  if (!labelPublicId) return
  try {
    await cardService.toggleLabel(card.value.publicId, labelPublicId)
    await refreshCard()
  } catch (error: any) {
    toast.error('Failed to update label')
  }
}

async function toggleMember(memberPublicId: string) {
  if (!memberPublicId) return
  try {
    await cardService.toggleMember(card.value.publicId, memberPublicId)
    await refreshCard()
  } catch (error: any) {
    toast.error('Failed to update member')
  }
}

async function updateDueDate(date: string) {
  try {
    await cardService.update(card.value.publicId, { dueDate: date ? new Date(date).toISOString() : null })
    await refreshCard()
    toast.success('Due date updated')
  } catch (error: any) {
    toast.error('Failed to update due date')
  }
}

async function addChecklist() {
  if (!newChecklistName.value.trim()) return
  try {
    await checklistService.create({ cardId: card.value.id, name: newChecklistName.value.trim() })
    newChecklistName.value = ''
    showAddChecklist.value = false
    await refreshCard()
    toast.success('Checklist added')
  } catch (error: any) {
    toast.error('Failed to add checklist')
  }
}

async function refreshCard() {
  try {
    const response = await cardService.getById(card.value.publicId)
    card.value = response.data
    form.title = card.value.title
    form.description = card.value.description || ''
  } catch {}
}

async function deleteAttachment(publicId: string) {
  try {
    await attachmentService.delete(publicId)
    await refreshCard()
    toast.success('Attachment deleted')
  } catch (error: any) {
    toast.error('Failed to delete attachment')
  }
}

async function uploadFile(file: File) {
  if (!card.value?.id) return
  uploadProgress.value = 1
  try {
    const formData = new FormData()
    formData.append('file', file)
    await attachmentService.upload(card.value.id, file)
    uploadProgress.value = 100
    await refreshCard()
    toast.success('File uploaded')
    setTimeout(() => { uploadProgress.value = 0 }, 1000)
  } catch (error: any) {
    uploadProgress.value = 0
    toast.error(error?.response?.data?.message || 'Failed to upload file')
  }
}

async function handleFileSelect(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0]
  if (!file) return
  input.value = ''
  await uploadFile(file)
}

async function handleDrop(e: DragEvent) {
  dragOver.value = false
  const file = e.dataTransfer?.files?.[0]
  if (!file) return
  await uploadFile(file)
}

function isImage(att: any) {
  return att.contentType?.startsWith('image/') && att.path
}

function fileIcon(att: any): string {
  const ext = (att.originalFilename || '').split('.').pop()?.toLowerCase()
  const cls = 'h-5 w-5'
  if (['pdf'].includes(ext || '')) {
    return `<svg class="${cls}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/><path stroke-linecap="round" stroke-linejoin="round" d="M17 17v2a1 1 0 01-1 1H8a1 1 0 01-1-1v-2M12 3v6a1 1 0 001 1h4"/></svg>`
  }
  if (['doc', 'docx'].includes(ext || '')) {
    return `<svg class="${cls}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v6a1 1 0 001 1h4"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6M9 17h6"/></svg>`
  }
  if (['xls', 'xlsx', 'csv'].includes(ext || '')) {
    return `<svg class="${cls}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v6a1 1 0 001 1h4"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.5 13l5 5M14.5 13l-5 5"/></svg>`
  }
  return `<svg class="${cls}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>`
}

function fileIconBg(att: any) {
  const ext = (att.originalFilename || '').split('.').pop()?.toLowerCase()
  if (['pdf'].includes(ext || '')) return 'bg-red-100 dark:bg-red-900/30 text-red-600'
  if (['doc', 'docx'].includes(ext || '')) return 'bg-blue-100 dark:bg-blue-900/30 text-blue-600'
  if (['xls', 'xlsx', 'csv'].includes(ext || '')) return 'bg-green-100 dark:bg-green-900/30 text-green-600'
  if (['zip', 'rar', 'tar', 'gz'].includes(ext || '')) return 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600'
  return 'bg-light-200 dark:bg-dark-300 text-light-700 dark:text-dark-700'
}

function formatFileSize(bytes: number | null | undefined): string {
  if (!bytes || bytes === 0 || isNaN(bytes)) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return `${(bytes / Math.pow(k, i)).toFixed(1)} ${sizes[i] || 'B'}`
}
</script>
