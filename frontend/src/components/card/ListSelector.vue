<template>
  <div class="relative w-full">
    <button
      @click.stop="open = !open"
      class="w-full flex items-center gap-2 rounded-md border border-light-500 bg-light-100 px-3 py-2 text-xs text-left hover:bg-light-200 dark:border-dark-500 dark:bg-dark-400 dark:text-dark-900 dark:hover:bg-dark-500 transition-colors"
    >
      <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
      </svg>
      <span class="truncate">{{ currentListName }}</span>
    </button>
    <div v-if="open" class="absolute left-0 top-full mt-1 w-48 bg-light-50 dark:bg-dark-200 rounded-lg shadow-lg border border-light-300 dark:border-dark-400 py-1 z-10" @click.stop>
      <button
        v-for="list in lists"
        :key="list.publicId"
        @click="selectList(list.publicId)"
        class="w-full text-left px-3 py-1.5 text-xs hover:bg-light-200 dark:hover:bg-dark-300 transition-colors"
        :class="{ 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400': currentListId === list.publicId }"
      >
        {{ list.name }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useToast } from '@/composables/useToast'
import { cardService } from '@/services/card.service'

const props = defineProps<{
  cardPublicId: string
  lists: any[]
  currentListId: string
}>()

const emit = defineEmits<{
  update: []
}>()

const toast = useToast()
const open = ref(false)

const currentListName = computed(() => {
  const list = props.lists.find(l => l.publicId === props.currentListId)
  return list?.name || 'Select list'
})

async function selectList(listPublicId: string) {
  if (listPublicId === props.currentListId) {
    open.value = false
    return
  }
  try {
    await cardService.move(props.cardPublicId, { listPublicId, index: 0 })
    open.value = false
    emit('update')
    toast.success('Card moved')
  } catch (error: any) {
    toast.error('Failed to move card')
  }
}
</script>
