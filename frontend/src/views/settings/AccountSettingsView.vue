<template>
  <AppLayout>
    <SettingsLayout>
      <div class="mb-8 border-t border-light-300 dark:border-dark-300">
        <!-- Profile Picture -->
        <h2 class="mb-4 mt-8 text-sm font-bold text-light-1000 dark:text-dark-1000">Profile picture</h2>
        <div class="flex items-center gap-4">
          <Avatar :name="user?.name || 'User'" :src="user?.image" size="lg" />
          <div>
            <p class="text-sm text-light-900 dark:text-dark-800">{{ user?.name }}</p>
            <p class="text-xs text-light-800 dark:text-dark-700">{{ user?.email }}</p>
          </div>
        </div>

        <!-- Display Name -->
        <h2 class="mb-4 mt-8 text-sm font-bold text-light-1000 dark:text-dark-1000">Display name</h2>
        <div class="flex items-center gap-2">
          <Input v-model="form.name" class="flex-1" />
          <Button @click="updateName" :loading="isSavingName" :disabled="form.name === user?.name" size="sm">Save</Button>
        </div>

        <!-- Email -->
        <h2 class="mb-4 mt-8 text-sm font-bold text-light-1000 dark:text-dark-1000">Email</h2>
        <p class="text-sm text-light-900 dark:text-dark-800">{{ user?.email }}</p>

        <!-- Delete Account -->
        <div class="mb-8 border-t border-light-300 dark:border-dark-300">
          <h2 class="mb-4 mt-8 text-sm font-bold text-light-1000 dark:text-dark-1000">Delete account</h2>
          <p class="mb-4 text-sm text-light-800 dark:text-dark-700">
            Once you delete your account, there is no going back. This action cannot be undone.
          </p>
          <Button variant="danger" @click="confirmDelete" :loading="isDeleting" :disabled="isDeleting">Delete account</Button>
        </div>
      </div>
    </SettingsLayout>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'
import { userService } from '@/services/user.service'
import AppLayout from '@/components/layout/AppLayout.vue'
import SettingsLayout from '@/components/layout/SettingsLayout.vue'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'
import Avatar from '@/components/ui/Avatar.vue'

const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

const user = ref<any>(null)
const form = ref({ name: '' })
const isSavingName = ref(false)
const isDeleting = ref(false)

onMounted(() => {
  user.value = authStore.user
  if (user.value) {
    form.value.name = user.value.name
  }
})

async function updateName() {
  if (!form.value.name.trim()) return

  isSavingName.value = true
  try {
    await userService.updateMe({ name: form.value.name.trim() })
    if (authStore.user) {
      authStore.user.name = form.value.name.trim()
    }
    toast.success('Profile updated')
  } catch (error: any) {
    toast.error('Failed to update profile')
  } finally {
    isSavingName.value = false
  }
}

async function confirmDelete() {
  if (!confirm('Are you sure you want to delete your account? This cannot be undone.')) return

  isDeleting.value = true
  try {
    await userService.deleteMe()
    authStore.clearUser()
    localStorage.removeItem('accessToken')
    localStorage.removeItem('refreshToken')
    toast.success('Account deleted')
    router.push('/')
  } catch (error: any) {
    toast.error(error?.response?.data?.message || 'Failed to delete account')
  } finally {
    isDeleting.value = false
  }
}
</script>
