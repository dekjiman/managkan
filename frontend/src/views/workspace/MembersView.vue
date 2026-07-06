<template>
  <div class="max-w-[1100px] mx-auto px-5 py-6 md:px-28 md:py-12">
    <div class="flex w-full justify-between mb-6">
      <div>
        <h1 class="font-bold tracking-tight text-light-1000 dark:text-dark-1000 text-lg">Members</h1>
        <p class="text-sm text-light-800 dark:text-dark-700 mt-1">Manage workspace members and their roles.</p>
      </div>
      <Button @click="showInviteModal = true" :disabled="workspace?.role !== 'admin' || atMemberLimit" size="sm">
        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Invite
      </Button>
    </div>

    <div v-if="atMemberLimit" class="mb-4 rounded-md bg-yellow-50 dark:bg-yellow-900/20 p-3 text-sm text-yellow-700 dark:text-yellow-400">
      You've reached the member limit for your current plan.
      <router-link :to="`/${workspace?.slug}/settings/billing`" class="underline font-medium">Upgrade</router-link> for more members.
    </div>

    <div v-if="isLoading" class="space-y-4">
      <div v-for="i in 3" :key="i" class="h-16 bg-light-200 dark:bg-dark-300 rounded-lg animate-pulse" />
    </div>

    <div v-else-if="error" class="rounded-md bg-red-50 dark:bg-red-900/20 p-4 text-sm text-red-700 dark:text-red-400">
      {{ error }}
    </div>

    <div v-else-if="members.length === 0" class="text-center py-12 text-sm text-light-800 dark:text-dark-700">
      No members in this workspace yet.
    </div>

    <template v-else>
      <div v-if="members.length > 5" class="mb-4">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search members..."
          class="input w-full max-w-xs"
        />
      </div>

      <div :class="['rounded-lg border border-light-300 dark:border-dark-400', openDropdown ? 'overflow-visible' : 'overflow-hidden']">
        <table class="min-w-full divide-y divide-light-300 dark:divide-dark-400">
          <thead class="bg-light-200 dark:bg-dark-200">
            <tr>
              <th class="py-3 pl-4 pr-3 text-left text-xs font-semibold text-light-800 dark:text-dark-800">Member</th>
              <th class="px-3 py-3 text-left text-xs font-semibold text-light-800 dark:text-dark-800">Role</th>
              <th v-if="workspace?.role === 'admin'" class="px-3 py-3 text-right text-xs font-semibold text-light-800 dark:text-dark-800">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-light-300 dark:divide-dark-400 bg-light-50 dark:bg-dark-100">
            <tr v-for="member in filteredMembers" :key="member.publicId">
            <td class="py-3 pl-4 pr-3">
              <div class="flex items-center gap-3">
                <Avatar :name="displayName(member)" :src="member.userImage" size="md" />
                <div class="min-w-0 flex-1">
                  <p class="text-sm font-medium text-light-1000 dark:text-dark-1000 truncate">{{ displayName(member) }}</p>
                  <p class="text-xs text-light-700 dark:text-dark-700 truncate">{{ member.email }}</p>
                </div>
              </div>
            </td>
            <td class="px-3 py-3">
              <div class="flex items-center gap-2">
                <Badge :variant="roleVariant(member.role)">{{ capitalize(member.role) }}</Badge>
                <Badge v-if="member.status === 'invited'" variant="warning">Pending</Badge>
              </div>
            </td>
            <td v-if="workspace?.role === 'admin'" class="px-3 py-3 text-right">
              <div class="relative inline-flex">
                <button
                  @click.stop="toggleDropdown(member.publicId)"
                  class="rounded p-1 text-light-800 hover:bg-light-200 dark:text-dark-700 dark:hover:bg-dark-300"
                  title="Actions"
                >
                  <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                  </svg>
                </button>
                <div @click.stop v-if="openDropdown === member.publicId" class="absolute right-0 top-full mt-1 w-40 bg-light-50 dark:bg-dark-200 rounded-lg shadow-lg border border-light-300 dark:border-dark-400 py-1 z-10">
                  <button @click="changeRole(member.publicId, 'admin')" class="w-full text-left px-3 py-1.5 text-xs hover:bg-light-200 dark:hover:bg-dark-300">Set as Admin</button>
                  <button @click="changeRole(member.publicId, 'member')" class="w-full text-left px-3 py-1.5 text-xs hover:bg-light-200 dark:hover:bg-dark-300">Set as Member</button>
                  <button @click="changeRole(member.publicId, 'guest')" class="w-full text-left px-3 py-1.5 text-xs hover:bg-light-200 dark:hover:bg-dark-300">Set as Guest</button>
                  <template v-if="member.status === 'invited'">
                    <div class="border-t border-light-300 dark:border-dark-400 my-1" />
                    <button @click="resendInvite(member.publicId)" class="w-full text-left px-3 py-1.5 text-xs hover:bg-light-200 dark:hover:bg-dark-300">Resend invite</button>
                  </template>
                  <div class="border-t border-light-300 dark:border-dark-400 my-1" />
                  <button @click="removeMember(member.publicId)" class="w-full text-left px-3 py-1.5 text-xs text-red-600 hover:bg-light-200 dark:hover:bg-dark-300">Remove</button>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    </template>

    <Modal :is-open="showInviteModal" @close="showInviteModal = false">
      <template #header>Invite member</template>
      <form @submit.prevent="inviteMember" class="space-y-4">
        <Input v-model="inviteEmail" label="Email" type="email" placeholder="member@example.com" required />
        <div>
          <label class="block text-xs font-medium text-light-900 dark:text-dark-800 mb-1">Role</label>
          <select v-model="inviteRole" class="input w-full text-sm">
            <option value="member">Member</option>
            <option value="admin">Admin</option>
            <option value="guest">Guest</option>
          </select>
        </div>
        <p v-if="inviteError" class="text-xs text-red-500">{{ inviteError }}</p>
      </form>
      <template #footer>
        <div class="flex justify-end gap-2">
          <Button variant="secondary" @click="showInviteModal = false" size="sm">Cancel</Button>
          <Button @click="inviteMember" :loading="isInviting" :disabled="!inviteEmail.trim()" size="sm">Invite</Button>
        </div>
      </template>
    </Modal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import { useToast } from '@/composables/useToast'
import { memberService } from '@/services/member.service'
import { billingService } from '@/services/billing.service'
import { planService } from '@/services/plan.service'
import { useWorkspaceStore } from '@/stores/workspace'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'
import Modal from '@/components/ui/Modal.vue'
import Avatar from '@/components/ui/Avatar.vue'
import Badge from '@/components/ui/Badge.vue'

const route = useRoute()
const toast = useToast()
const workspaceStore = useWorkspaceStore()

const workspace = ref<any>(null)
const members = ref<any[]>([])
const isLoading = ref(true)
const error = ref('')
const showInviteModal = ref(false)
const isInviting = ref(false)
const inviteEmail = ref('')
const inviteRole = ref('member')
const inviteError = ref('')
const openDropdown = ref('')
const searchQuery = ref('')
const memberLimit = ref(3)

const atMemberLimit = computed(() => memberLimit.value !== -1 && members.value.length >= memberLimit.value)

const filteredMembers = computed(() => {
  if (!searchQuery.value.trim()) return members.value
  const q = searchQuery.value.toLowerCase()
  return members.value.filter((m: any) =>
    (m.user?.name || '').toLowerCase().includes(q) ||
    (m.email || '').toLowerCase().includes(q)
  )
})

onMounted(async () => {
  if (workspaceStore.currentWorkspace) {
    workspace.value = workspaceStore.currentWorkspace
  } else {
    await workspaceStore.fetchWorkspaces()
    const slug = route.params.workspaceSlug as string
    const ws = workspaceStore.workspaces?.find((w: any) => w.slug === slug)
    if (ws) {
      workspace.value = ws
      workspaceStore.setCurrentWorkspace(ws)
    }
  }

  if (workspace.value) {
    await loadMembers()
    await loadPlanLimit()
  }
})

async function loadMembers() {
  isLoading.value = true
  error.value = ''
  try {
    const response = await memberService.getByWorkspace(String(workspace.value.id))
    members.value = response.data || []
  } catch (err: any) {
    error.value = err?.response?.data?.message || 'Failed to load members'
  } finally {
    isLoading.value = false
  }
}

async function loadPlanLimit() {
  try {
    const slug = workspace.value.slug
    const res = await billingService.getByWorkspace(slug)
    const planName = res.data.plan || 'free'
    const plans = await planService.getAll()
    const currentPlan = plans.data.find((p: any) => p.name === planName)
    memberLimit.value = currentPlan?.memberLimit ?? 3
  } catch {
    memberLimit.value = 3
  }
}

async function inviteMember() {
  if (!inviteEmail.value.trim()) return

  isInviting.value = true
  inviteError.value = ''
  try {
    await memberService.add({
      workspaceId: workspace.value.id,
      email: inviteEmail.value.trim(),
      role: inviteRole.value
    })
    showInviteModal.value = false
    inviteEmail.value = ''
    inviteRole.value = 'member'
    await loadMembers()
    toast.success('Member invited')
  } catch (err: any) {
    inviteError.value = err?.response?.data?.message || 'Failed to invite member'
  } finally {
    isInviting.value = false
  }
}

async function changeRole(memberPublicId: string, role: string) {
  try {
    await memberService.updateRole(memberPublicId, role)
    openDropdown.value = ''
    await loadMembers()
    toast.success('Role updated')
  } catch (err: any) {
    toast.error(err?.response?.data?.message || 'Failed to update role')
  }
}

async function removeMember(memberPublicId: string) {
  if (!confirm('Remove this member from the workspace?')) return

  try {
    await memberService.remove(memberPublicId)
    openDropdown.value = ''
    await loadMembers()
    toast.success('Member removed')
  } catch (err: any) {
    toast.error(err?.response?.data?.message || 'Failed to remove member')
  }
}

async function resendInvite(memberPublicId: string) {
  try {
    await memberService.resendInvite(memberPublicId)
    openDropdown.value = ''
    toast.success('Invite resent')
  } catch (err: any) {
    toast.error(err?.response?.data?.message || 'Failed to resend invite')
  }
}

function toggleDropdown(memberPublicId: string) {
  openDropdown.value = openDropdown.value === memberPublicId ? '' : memberPublicId
}

function displayName(member: any): string {
  return member.userName || member.email?.split('@')[0] || 'Invited user'
}

function capitalize(s: string): string {
  if (!s) return ''
  return s.charAt(0).toUpperCase() + s.slice(1)
}

function roleVariant(role: string) {
  if (role === 'admin') return 'info'
  if (role === 'guest') return 'warning'
  return 'default'
}

function handleClickOutside() {
  openDropdown.value = ''
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
