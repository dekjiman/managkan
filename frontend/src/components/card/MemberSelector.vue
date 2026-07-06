<template>
  <div class="relative w-full">
    <button
      @click.stop="open = !open"
      class="w-full flex items-center gap-2 rounded-md border border-light-500 bg-light-100 px-3 py-2 text-xs text-left hover:bg-light-200 dark:border-dark-500 dark:bg-dark-400 dark:text-dark-900 dark:hover:bg-dark-500 transition-colors"
    >
      <div v-if="selectedMembers.length" class="flex -space-x-1">
        <Avatar
          v-for="member in selectedMembers"
          :key="member.publicId"
          :name="member.userName || 'User'"
          size="sm"
        />
      </div>
      <span v-else class="text-light-800 dark:text-dark-700">+ Add member</span>
    </button>
    <div v-if="open" class="absolute left-0 top-full mt-1 w-56 bg-light-50 dark:bg-dark-200 rounded-lg shadow-lg border border-light-300 dark:border-dark-400 py-1 z-10 max-h-48 overflow-y-auto" @click.stop>
      <button
        v-for="member in workspaceMembers"
        :key="member.publicId"
        @click="toggleMember(member.publicId)"
        class="w-full text-left px-3 py-1.5 text-xs hover:bg-light-200 dark:hover:bg-dark-300 transition-colors flex items-center gap-2"
        :class="{ 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400': isSelected(member.publicId) }"
      >
        <Avatar :name="member.user?.name || member.email" size="sm" />
        <span class="truncate">{{ member.user?.name || member.email }}</span>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useToast } from '@/composables/useToast'
import { cardService } from '@/services/card.service'
import Avatar from '@/components/ui/Avatar.vue'

const props = defineProps<{
  cardPublicId: string
  cardMembers: any[]
  workspaceMembers: any[]
}>()

const emit = defineEmits<{
  update: []
}>()

const toast = useToast()
const open = ref(false)

const selectedMembers = computed(() => props.cardMembers)

function isSelected(memberPublicId: string) {
  return props.cardMembers.some(m => m.publicId === memberPublicId)
}

async function toggleMember(memberPublicId: string) {
  try {
    await cardService.toggleMember(props.cardPublicId, memberPublicId)
    emit('update')
  } catch (error: any) {
    toast.error('Failed to update member')
  }
}
</script>
