<template>
  <SettingsLayout>
    <div class="space-y-8">
      <div>
        <h2 class="text-sm font-bold text-light-1000 dark:text-dark-1000 mb-4">Workspace name</h2>
        <div class="flex items-center gap-2">
          <Input v-model="form.name" class="flex-1" />
          <Button @click="updateName" :loading="isSaving" :disabled="form.name === workspace?.name" size="sm">Save</Button>
        </div>
      </div>

      <div>
        <h2 class="text-sm font-bold text-light-1000 dark:text-dark-1000 mb-4">Workspace URL</h2>
        <div class="flex items-center gap-2">
          <span class="text-sm text-light-800 dark:text-dark-700">/{{ workspace?.slug }}</span>
        </div>
      </div>

      <div>
        <h2 class="text-sm font-bold text-light-1000 dark:text-dark-1000 mb-4">Workspace description</h2>
        <div class="flex items-start gap-2">
          <textarea
            v-model="form.description"
            rows="3"
            class="input flex-1 resize-none"
            placeholder="Add a description..."
          />
          <Button @click="updateDescription" :loading="isSavingDesc" size="sm">Save</Button>
        </div>
      </div>

      <div class="border-t border-light-300 dark:border-dark-300 pt-8">
        <h2 class="text-sm font-bold text-red-600 mb-4">Delete workspace</h2>
        <p class="mb-4 text-sm text-light-800 dark:text-dark-700">
          Once you delete your workspace, there is no going back. This action cannot be undone.
        </p>
        <Button variant="danger" @click="confirmDelete" :disabled="workspace?.role !== 'admin'">Delete workspace</Button>
      </div>
    </div>
  </SettingsLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useWorkspaceStore } from '@/stores/workspace'
import { useToast } from '@/composables/useToast'
import { useRequireAdmin } from '@/composables/useRequireAdmin'
import { workspaceService } from '@/services/workspace.service'
import SettingsLayout from '@/components/layout/SettingsLayout.vue'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'

const route = useRoute()
const router = useRouter()
const toast = useToast()
const workspaceStore = useWorkspaceStore()

useRequireAdmin()

const workspace = ref<any>(null)
const form = ref({ name: '', description: '' })
const isSaving = ref(false)
const isSavingDesc = ref(false)

onMounted(async () => {
  workspace.value = workspaceStore.currentWorkspace
  if (!workspace.value) {
    const slug = route.params.workspaceSlug as string
    if (slug) {
      try {
        const res = await workspaceService.getById(slug)
        workspace.value = res.data || res
        if (workspace.value) {
          workspaceStore.setCurrentWorkspace(workspace.value)
        }
      } catch { }
    }
  }
  if (workspace.value) {
    form.value = {
      name: workspace.value.name,
      description: workspace.value.description || ''
    }
  }
})

async function updateName() {
  if (!workspace.value || !form.value.name.trim()) return

  isSaving.value = true
  try {
    await workspaceService.update(workspace.value.publicId, { name: form.value.name.trim() })
    toast.success('Workspace name updated')
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Failed to update workspace')
  } finally {
    isSaving.value = false
  }
}

async function updateDescription() {
  if (!workspace.value) return

  isSavingDesc.value = true
  try {
    await workspaceService.update(workspace.value.publicId, { description: form.value.description })
    toast.success('Workspace description updated')
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Failed to update workspace')
  } finally {
    isSavingDesc.value = false
  }
}

async function confirmDelete() {
  if (!workspace.value) return
  if (!confirm('Are you sure you want to delete this workspace? This cannot be undone.')) return

  try {
    await workspaceService.delete(workspace.value.publicId)
    toast.success('Workspace deleted')
    router.push('/dashboard')
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Failed to delete workspace')
  }
}
</script>
