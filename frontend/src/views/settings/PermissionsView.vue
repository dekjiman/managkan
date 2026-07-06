<template>
  <SettingsLayout>
    <div>
      <div class="flex items-center justify-between mb-6">
        <div>
          <h2 class="text-sm font-bold text-light-1000 dark:text-dark-1000">Members</h2>
          <p class="text-sm text-light-800 dark:text-dark-700 mt-1">Manage who has access to this workspace.</p>
        </div>
        <Button v-if="isAdmin" size="sm" @click="showAdd = true">
          <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
          Add member
        </Button>
      </div>

      <div v-if="isLoading" class="flex justify-center py-12">
        <LoadingSpinner />
      </div>

      <div v-else-if="error" class="rounded-md bg-red-50 dark:bg-red-900/20 p-4 text-sm text-red-700 dark:text-red-400">
        {{ error }}
      </div>

      <div v-else-if="members.length === 0" class="text-center py-12 text-sm text-light-800 dark:text-dark-700">
        No members in this workspace yet.
      </div>

      <div v-else class="overflow-hidden rounded-lg border border-light-300 dark:border-dark-400">
        <table class="min-w-full divide-y divide-light-300 dark:divide-dark-400">
          <thead class="bg-light-200 dark:bg-dark-200">
            <tr>
              <th class="py-3 pl-4 pr-3 text-left text-xs font-semibold text-light-800 dark:text-dark-800">Member</th>
              <th class="px-3 py-3 text-left text-xs font-semibold text-light-800 dark:text-dark-800">Role</th>
              <th class="px-3 py-3 text-left text-xs font-semibold text-light-800 dark:text-dark-800">Status</th>
              <th class="px-3 py-3 text-right text-xs font-semibold text-light-800 dark:text-dark-800">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-light-300 dark:divide-dark-400 bg-light-50 dark:bg-dark-100">
            <tr v-for="member in members" :key="member.publicId">
              <td class="py-3 pl-4 pr-3">
                <div class="flex items-center gap-3">
                  <Avatar :name="member.userName || member.email || 'U'" size="md" />
                  <div>
                    <p class="text-sm font-medium text-light-1000 dark:text-dark-1000">{{ member.userName || member.email }}</p>
                    <p v-if="member.userName" class="text-xs text-light-700 dark:text-dark-700">{{ member.email }}</p>
                  </div>
                </div>
              </td>
              <td class="px-3 py-3">
                <select
                  v-if="isAdmin && member.publicId !== currentMember?.publicId"
                  :value="member.role"
                  @change="changeRole(member.publicId, ($event.target as HTMLSelectElement).value)"
                  class="input text-xs py-1 px-2 w-24"
                  :disabled="changingRole === member.publicId"
                >
                  <option value="admin">Admin</option>
                  <option value="member">Member</option>
                  <option value="guest">Guest</option>
                </select>
                <Badge v-else :variant="roleBadge(member.role)" class="text-xs">{{ member.role }}</Badge>
              </td>
              <td class="px-3 py-3">
                <Badge :variant="member.status === 'active' ? 'success' : 'warning'">{{ member.status }}</Badge>
              </td>
              <td class="px-3 py-3 text-right">
                <Button
                  v-if="isAdmin && member.publicId !== currentMember?.publicId"
                  variant="ghost"
                  size="sm"
                  @click="confirmRemove(member)"
                >
                  <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </Button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Add Member Modal -->
      <Modal :is-open="showAdd" @close="showAdd = false">
        <template #header>Add member</template>
        <div class="space-y-4">
          <Input v-model="addEmail" label="Email address" placeholder="colleague@company.com" type="email" />
          <div>
            <label class="block text-xs font-medium text-light-900 dark:text-dark-800 mb-1">Role</label>
            <select v-model="addRole" class="input w-full text-sm">
              <option value="member">Member</option>
              <option value="admin">Admin</option>
              <option value="guest">Guest</option>
            </select>
          </div>
          <p v-if="addError" class="text-xs text-red-500">{{ addError }}</p>
        </div>
        <template #footer>
          <div class="flex justify-end gap-2">
            <Button variant="secondary" size="sm" @click="showAdd = false">Cancel</Button>
            <Button size="sm" :loading="addingMember" :disabled="!addEmail.trim()" @click="addMember">Add</Button>
          </div>
        </template>
      </Modal>
    </div>
  </SettingsLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useWorkspaceStore } from '@/stores/workspace'
import { useToast } from '@/composables/useToast'
import { useRequireAdmin } from '@/composables/useRequireAdmin'
import { memberService } from '@/services/member.service'
import SettingsLayout from '@/components/layout/SettingsLayout.vue'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'
import Avatar from '@/components/ui/Avatar.vue'
import Badge from '@/components/ui/Badge.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import Modal from '@/components/ui/Modal.vue'

const workspaceStore = useWorkspaceStore()
const toast = useToast()

useRequireAdmin()

const isAdmin = computed(() => workspaceStore.currentWorkspace?.role === 'admin')
const currentMember = computed(() => members.value.find((m: any) => m.role === 'admin') || null)

const members = ref<any[]>([])
const isLoading = ref(true)
const error = ref('')
const changingRole = ref('')

// Add member
const showAdd = ref(false)
const addEmail = ref('')
const addRole = ref('member')
const addingMember = ref(false)
const addError = ref('')

onMounted(fetchMembers)

async function fetchMembers() {
  isLoading.value = true
  error.value = ''
  try {
    const workspace = workspaceStore.currentWorkspace
    if (!workspace) return
    const response = await memberService.getByWorkspace(String(workspace.id))
    members.value = response.data
  } catch (err: any) {
    error.value = err?.response?.data?.message || 'Failed to load members'
  } finally {
    isLoading.value = false
  }
}

async function changeRole(publicId: string, role: string) {
  changingRole.value = publicId
  try {
    await memberService.updateRole(publicId, role)
    const member = members.value.find((m: any) => m.publicId === publicId)
    if (member) member.role = role
    toast.success('Role updated')
  } catch (err: any) {
    toast.error(err?.response?.data?.message || 'Failed to update role')
  } finally {
    changingRole.value = ''
  }
}

function confirmRemove(member: any) {
  if (!confirm(`Remove ${member.userName || member.email} from this workspace?`)) return
  removeMember(member.publicId)
}

async function removeMember(publicId: string) {
  try {
    await memberService.remove(publicId)
    members.value = members.value.filter((m: any) => m.publicId !== publicId)
    toast.success('Member removed')
  } catch (err: any) {
    toast.error(err?.response?.data?.message || 'Failed to remove member')
  }
}

async function addMember() {
  if (!addEmail.value.trim()) return
  addingMember.value = true
  addError.value = ''
  try {
    const workspace = workspaceStore.currentWorkspace
    if (!workspace) return
    await memberService.add({ workspaceId: String(workspace.id), email: addEmail.value.trim(), role: addRole.value })
    showAdd.value = false
    addEmail.value = ''
    toast.success('Member added')
    await fetchMembers()
  } catch (err: any) {
    addError.value = err?.response?.data?.message || 'Failed to add member'
  } finally {
    addingMember.value = false
  }
}

function roleBadge(role: string) {
  if (role === 'admin') return 'info'
  if (role === 'guest') return 'warning'
  return 'default'
}
</script>
