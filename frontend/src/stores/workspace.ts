import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { Workspace } from '@/types'

export const useWorkspaceStore = defineStore('workspace', () => {
  const workspaces = ref<Workspace[]>([])
  const currentWorkspace = ref<Workspace | null>(null)
  const isLoading = ref(false)

  const workspaceCount = computed(() => workspaces.value.length)

  function setWorkspaces(data: Workspace[]) {
    workspaces.value = data
  }

  function setCurrentWorkspace(workspace: Workspace) {
    currentWorkspace.value = workspace
  }

  function clearWorkspaces() {
    workspaces.value = []
    currentWorkspace.value = null
  }

  async function fetchWorkspaces() {
    isLoading.value = true
    try {
      const { workspaceService } = await import('@/services/workspace.service')
      const response = await workspaceService.getAll()
      setWorkspaces(response.data)
    } catch (error) {
      console.error('Failed to fetch workspaces:', error)
    } finally {
      isLoading.value = false
    }
  }

  async function fetchWorkspace(publicId: string) {
    isLoading.value = true
    try {
      const { workspaceService } = await import('@/services/workspace.service')
      const response = await workspaceService.getById(publicId)
      setCurrentWorkspace(response.data)
      return response.data
    } catch (error) {
      console.error('Failed to fetch workspace:', error)
      throw error
    } finally {
      isLoading.value = false
    }
  }

  return {
    workspaces,
    currentWorkspace,
    isLoading,
    workspaceCount,
    setWorkspaces,
    setCurrentWorkspace,
    clearWorkspaces,
    fetchWorkspaces,
    fetchWorkspace
  }
})
