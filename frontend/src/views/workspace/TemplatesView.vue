<template>
  <div class="max-w-[1100px] mx-auto px-5 py-6 md:px-28 md:py-12">
    <div class="flex w-full justify-between mb-6">
      <h1 class="font-bold tracking-tight text-light-1000 dark:text-dark-1000 text-lg">Templates</h1>
      <Button @click="showCreateModal = true" size="sm">
        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        New
      </Button>
    </div>

    <div v-if="isLoading" class="flex justify-center py-16">
      <LoadingSpinner />
    </div>

    <div v-else-if="templates.length === 0" class="text-center py-16">
      <div class="w-14 h-14 bg-light-200 dark:bg-dark-300 rounded-xl flex items-center justify-center mx-auto mb-4">
        <svg class="w-7 h-7 text-light-700 dark:text-dark-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
        </svg>
      </div>
      <h3 class="text-base font-medium text-light-1000 dark:text-dark-1000 mb-1">No templates yet</h3>
      <p class="text-sm text-light-800 dark:text-dark-700 mb-4">Create templates to reuse board structures</p>
      <Button @click="showCreateModal = true" size="sm">Create Template</Button>
    </div>

    <template v-else>
      <div v-if="templates.length > 3" class="mb-4">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search templates..."
          class="input w-full max-w-xs"
        />
      </div>

      <div v-if="filteredTemplates.length === 0" class="text-center py-12">
        <p class="text-sm text-light-700 dark:text-dark-700">No templates match "{{ searchQuery }}"</p>
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <router-link
          v-for="template in filteredTemplates"
        :key="template.publicId"
        :to="`/${workspace?.slug}/templates/${template.slug}`"
        class="group card p-4 hover:shadow-md hover:border-light-400 dark:hover:border-dark-500 transition-all duration-150"
      >
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-lg bg-purple-50 dark:bg-purple-100 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-purple-600 dark:text-purple-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
            </svg>
          </div>
          <div class="min-w-0">
            <h3 class="font-medium text-sm text-light-1000 dark:text-dark-1000 truncate">{{ template.name }}</h3>
            <p class="text-xs text-light-800 dark:text-dark-700 truncate">{{ template.lists?.length || 0 }} lists</p>
          </div>
        </div>
      </router-link>
    </div>
    </template>

    <Modal :is-open="showCreateModal" @close="showCreateModal = false">
      <template #header>New template</template>
      <form @submit.prevent="createTemplate" class="space-y-4">
        <Input v-model="newTemplate.name" placeholder="Template name" autofocus />
      </form>
      <template #footer>
        <div class="flex justify-end gap-2">
          <Button variant="secondary" @click="showCreateModal = false" size="sm">Cancel</Button>
          <Button @click="createTemplate" :loading="isCreating" size="sm">Create</Button>
        </div>
      </template>
    </Modal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useWorkspaceStore } from '@/stores/workspace'
import { useToast } from '@/composables/useToast'
import { boardService } from '@/services/board.service'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'
import Modal from '@/components/ui/Modal.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const workspaceStore = useWorkspaceStore()
const toast = useToast()

const workspace = ref<any>(null)
const templates = ref<any[]>([])
const isLoading = ref(true)
const showCreateModal = ref(false)
const isCreating = ref(false)
const newTemplate = ref({ name: '' })
const searchQuery = ref('')

const filteredTemplates = computed(() => {
  if (!searchQuery.value.trim()) return templates.value
  const q = searchQuery.value.toLowerCase()
  return templates.value.filter((t: any) => t.name.toLowerCase().includes(q))
})

onMounted(async () => {
  workspace.value = workspaceStore.currentWorkspace
  if (workspace.value) {
    await loadTemplates()
  }
})

async function loadTemplates() {
  isLoading.value = true
  try {
    const response = await boardService.getTemplates(workspace.value.publicId)
    templates.value = response.data || []
  } finally {
    isLoading.value = false
  }
}

async function createTemplate() {
  if (!newTemplate.value.name.trim()) return

  isCreating.value = true
  try {
    await boardService.create(workspace.value.publicId, {
      name: newTemplate.value.name.trim(),
      lists: ['To Do', 'In Progress', 'Done'],
      labels: [],
      type: 'template'
    })
    await loadTemplates()
    showCreateModal.value = false
    newTemplate.value = { name: '' }
    toast.success('Template created')
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Failed to create template')
  } finally {
    isCreating.value = false
  }
}
</script>
