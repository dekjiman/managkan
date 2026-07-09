<template>
  <div class="max-w-[1100px] mx-auto px-5 py-6 md:px-28 md:py-12">
    <div v-if="isLoading" class="flex justify-center py-16">
      <LoadingSpinner />
    </div>

    <div v-else-if="error" class="rounded-md bg-red-50 dark:bg-red-900/20 p-4 text-sm text-red-700 dark:text-red-400">
      {{ error }}
    </div>

    <div v-else-if="template">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <div>
          <div class="flex items-center gap-2 text-sm text-light-800 dark:text-dark-700 mb-1">
            <router-link :to="`/${workspace?.slug}/templates`" class="hover:text-light-1000 dark:hover:text-dark-1000">Templates</router-link>
            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
            <span class="text-light-1000 dark:text-dark-1000">{{ template.name }}</span>
          </div>
          <h1 class="text-lg font-bold text-light-1000 dark:text-dark-1000">{{ template.name }}</h1>
          <p v-if="template.description" class="text-sm text-light-800 dark:text-dark-700 mt-1">{{ template.description }}</p>
        </div>
        <div class="flex items-center gap-2">
          <Button size="sm" variant="secondary" @click="useTemplate">
            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            Use template
          </Button>
          <Button size="sm" variant="danger" @click="deleteTemplate">
            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
            Delete
          </Button>
        </div>
      </div>

      <!-- Lists preview -->
      <div class="flex gap-4 overflow-x-auto pb-4">
        <div v-for="list in lists" :key="list.publicId" class="min-w-[200px] max-w-[280px] md:min-w-[280px] shrink-0">
          <div class="rounded-lg border border-light-300 dark:border-dark-400 bg-light-100 dark:bg-dark-100">
            <div class="px-3 py-2.5 border-b border-light-300 dark:border-dark-400">
              <h3 class="text-sm font-medium text-light-1000 dark:text-dark-1000">{{ list.name }}</h3>
              <span class="text-xs text-light-700 dark:text-dark-700">{{ list.cards?.length || 0 }} cards</span>
            </div>
            <div class="p-2 space-y-2 min-h-[60px]">
              <div v-for="card in list.cards || []" :key="card.publicId" class="rounded-md bg-white dark:bg-dark-300 px-3 py-2 text-xs text-light-1000 dark:text-dark-1000 shadow-sm">
                {{ card.title }}
              </div>
              <div v-if="!list.cards?.length" class="text-center py-4 text-xs text-light-700 dark:text-dark-700">No cards</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToast } from '@/composables/useToast'
import { boardService } from '@/services/board.service'
import { useWorkspaceStore } from '@/stores/workspace'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import Button from '@/components/ui/Button.vue'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const workspaceStore = useWorkspaceStore()

const workspace = computed(() => workspaceStore.currentWorkspace)
const template = ref<any>(null)
const lists = ref<any[]>([])
const isLoading = ref(true)
const error = ref('')

onMounted(async () => {
  if (!workspaceStore.currentWorkspace) {
    const slug = route.params.workspaceSlug as string
    const ws = workspaceStore.workspaces?.find((w: any) => w.slug === slug)
    if (ws) workspaceStore.setCurrentWorkspace(ws)
  }
  await loadTemplate()
})

async function loadTemplate() {
  isLoading.value = true
  error.value = ''
  try {
    const res = await boardService.getById(route.params.slug as string)
    template.value = res.data
    lists.value = template.value.lists || []
  } catch (err: any) {
    error.value = err?.response?.data?.message || 'Failed to load template'
  } finally {
    isLoading.value = false
  }
}

async function useTemplate() {
  if (!workspace.value) {
    toast.error('No workspace selected')
    return
  }
  const name = prompt('Enter a name for the new board:', template.value?.name || '')
  if (!name) return

  try {
    const res = await boardService.create(workspace.value.publicId, {
      name,
      sourceBoardPublicId: template.value!.publicId
    })
    toast.success('Board created from template')
    router.push(`/${workspace.value.slug}/${res.data.slug}`)
  } catch (err: any) {
    toast.error(err?.response?.data?.message || 'Failed to create board from template')
  }
}

async function deleteTemplate() {
  if (!template.value || !confirm(`Delete template "${template.value.name}"? This cannot be undone.`)) return
  try {
    await boardService.delete(template.value.publicId)
    toast.success('Template deleted')
    router.push(`/${workspace.value?.slug}/templates`)
  } catch (err: any) {
    toast.error(err?.response?.data?.message || 'Failed to delete template')
  }
}
</script>
