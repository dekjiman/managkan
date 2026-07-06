<template>
  <AppLayout>
    <div v-if="isLoading" class="flex justify-center py-12">
      <LoadingSpinner />
    </div>
    <div v-else-if="workspace">
      <router-view />
    </div>
    <div v-else class="text-center py-12">
      <p class="text-light-900 dark:text-dark-800">Workspace not found</p>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useWorkspaceStore } from '@/stores/workspace'
import { workspaceService } from '@/services/workspace.service'
import AppLayout from '@/components/layout/AppLayout.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const route = useRoute()
const workspaceStore = useWorkspaceStore()

const workspace = ref<any>(null)
const isLoading = ref(true)

async function loadWorkspace() {
  isLoading.value = true
  workspace.value = null
  try {
    const slug = route.params.workspaceSlug as string

    await workspaceStore.fetchWorkspaces()
    let ws = workspaceStore.workspaces.find(w => w.slug === slug)

    if (!ws) {
      const fallback = await workspaceService.getById(slug)
      ws = fallback.data || fallback
    }

    if (ws) {
      workspace.value = ws
      workspaceStore.setCurrentWorkspace(ws)
    }
  } catch (e) {
    console.error('Failed to load workspace:', e)
  } finally {
    isLoading.value = false
  }
}

onMounted(loadWorkspace)
watch(() => route.params.workspaceSlug, loadWorkspace)
</script>
