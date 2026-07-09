<template>
  <div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-xl font-semibold text-light-1000 dark:text-dark-1000">Boards</h1>
      </div>
      <Button @click="showCreateModal = true" size="sm" :disabled="atBoardLimit">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        New
      </Button>
    </div>

    <div v-if="atBoardLimit" class="mb-4 rounded-md bg-yellow-50 dark:bg-yellow-900/20 p-3 text-sm text-yellow-700 dark:text-yellow-400">
      You've reached the board limit for your current plan.
      <router-link :to="`/${workspace?.slug}/settings/billing`" class="underline font-medium">Upgrade</router-link> for more boards.
    </div>

    <div v-if="isLoading" class="flex justify-center py-16">
      <LoadingSpinner />
    </div>

    <div v-else-if="boards.length === 0" class="text-center py-16">
      <div class="w-14 h-14 bg-light-200 dark:bg-dark-300 rounded-xl flex items-center justify-center mx-auto mb-4">
        <svg class="w-7 h-7 text-light-700 dark:text-dark-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
        </svg>
      </div>
      <h3 class="text-base font-medium text-light-1000 dark:text-dark-1000 mb-1">No boards yet</h3>
      <p class="text-sm text-light-800 dark:text-dark-700 mb-4">Create your first board to get started</p>
      <Button @click="showCreateModal = true" size="sm" :disabled="atBoardLimit">Create Board</Button>
    </div>

    <template v-else>
      <div v-if="boards.length > 3" class="mb-4">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search boards..."
          class="input w-full max-w-xs"
        />
      </div>

      <div v-if="filteredBoards.length === 0" class="text-center py-12">
        <p class="text-sm text-light-700 dark:text-dark-700">No boards match "{{ searchQuery }}"</p>
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <router-link
          v-for="board in filteredBoards"
        :key="board.publicId"
        :to="`/${workspace?.slug}/${board.slug}`"
        class="group card p-4 hover:shadow-md hover:border-light-400 dark:hover:border-dark-500 transition-all duration-150"
      >
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-lg bg-primary-50 dark:bg-primary-100 flex items-center justify-center shrink-0 group-hover:bg-primary-100 dark:group-hover:bg-primary-200 transition-colors">
            <span class="text-primary-600 dark:text-primary-700 font-semibold text-sm">{{ board.name.charAt(0).toUpperCase() }}</span>
          </div>
          <div class="min-w-0">
            <h3 class="font-medium text-sm text-light-1000 dark:text-dark-1000 truncate">{{ board.name }}</h3>
            <p class="text-xs text-light-800 dark:text-dark-700 truncate">/{{ board.slug }}</p>
          </div>
        </div>
        <div class="mt-3 flex items-center gap-2">
          <Badge :variant="board.visibility === 'public' ? 'success' : 'default'">
            {{ board.visibility }}
          </Badge>
        </div>
      </router-link>
    </div>
    </template>

    <Modal :is-open="showCreateModal" @close="showCreateModal = false">
      <template #header>New board</template>
      <form @submit.prevent="createBoard" class="space-y-4">
        <Input v-model="newBoard.name" placeholder="Name" autofocus />

        <TemplateBoards
          :show="useTemplate"
          :selected-template="selectedTemplate"
          :custom-templates="customTemplates"
          @select="selectedTemplate = $event"
        />
      </form>
      <template #footer>
        <div class="flex items-center justify-between">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="useTemplate" class="rounded border-light-500 dark:border-dark-500 text-primary-600 focus:ring-primary-500" />
            <span class="text-xs text-light-900 dark:text-dark-800">Use template</span>
          </label>
          <Button @click="createBoard" :loading="isCreating" :disabled="!newBoard.name.trim()" size="sm">Create board</Button>
        </div>
      </template>
    </Modal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useWorkspaceStore } from '@/stores/workspace'
import { useToast } from '@/composables/useToast'
import { boardService } from '@/services/board.service'
import { billingService } from '@/services/billing.service'
import { planService } from '@/services/plan.service'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'
import Modal from '@/components/ui/Modal.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import Badge from '@/components/ui/Badge.vue'
import TemplateBoards, { type Template } from '@/components/board/TemplateBoards.vue'

const route = useRoute()
const router = useRouter()
const workspaceStore = useWorkspaceStore()
const toast = useToast()

const workspace = ref<any>(null)
const boards = ref<any[]>([])
const isLoading = ref(true)
const showCreateModal = ref(false)
const isCreating = ref(false)
const newBoard = ref({ name: '' })
const useTemplate = ref(false)
const selectedTemplate = ref<Template | null>(null)
const customTemplates = ref<Template[]>([])
const searchQuery = ref('')
const boardLimit = ref(3)

const atBoardLimit = computed(() => boardLimit.value !== -1 && boards.value.length >= boardLimit.value)

const filteredBoards = computed(() => {
  if (!searchQuery.value.trim()) return boards.value
  const q = searchQuery.value.toLowerCase()
  return boards.value.filter((b: any) => b.name.toLowerCase().includes(q))
})

onMounted(async () => {
  workspace.value = workspaceStore.currentWorkspace
  if (workspace.value) {
    await Promise.all([loadBoards(), loadCustomTemplates(), loadPlanLimit()])
  }
})

watch(useTemplate, (val) => {
  if (!val) {
    selectedTemplate.value = null
  } else if (!selectedTemplate.value && customTemplates.value.length > 0) {
    selectedTemplate.value = customTemplates.value[0]
  }
})

async function loadBoards() {
  isLoading.value = true
  try {
    const response = await boardService.getByWorkspace(workspace.value.publicId)
    boards.value = response.data
  } finally {
    isLoading.value = false
  }
}

async function loadCustomTemplates() {
  try {
    const response = await boardService.getTemplates(workspace.value.publicId)
    customTemplates.value = (response.data || []).map((t: any) => ({
      id: t.publicId,
      sourceBoardPublicId: t.publicId,
      name: t.name,
      lists: t.lists?.map((l: any) => l.name) || [],
      labels: t.labels?.map((l: any) => l.name) || []
    }))
  } catch {
    customTemplates.value = []
  }
}

async function loadPlanLimit() {
  try {
    const res = await billingService.getByWorkspace(workspace.value.slug)
    const planName = res.data.plan || 'free'
    const plans = await planService.getAll()
    const currentPlan = plans.data.find((p: any) => p.name === planName)
    boardLimit.value = currentPlan?.boardLimit ?? 3
  } catch {
    boardLimit.value = 3
  }
}

async function createBoard() {
  if (!newBoard.value.name.trim()) return

  isCreating.value = true
  try {
    const template = selectedTemplate.value
    const response = await boardService.create(workspace.value.publicId, {
      name: newBoard.value.name.trim(),
      lists: template?.lists ?? [],
      labels: template?.labels ?? [],
      type: 'regular',
      sourceBoardPublicId: template?.sourceBoardPublicId
    })

    showCreateModal.value = false
    newBoard.value = { name: '' }
    useTemplate.value = false
    selectedTemplate.value = null
    toast.success('Board created')

    const board = response.data
    if (board?.slug) {
      router.push(`/${workspace.value.slug}/${board.slug}`)
    } else {
      await loadBoards()
    }
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Failed to create board')
  } finally {
    isCreating.value = false
  }
}
</script>
