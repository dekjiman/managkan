<template>
  <div class="relative w-full">
    <button
      @click.stop="open = !open"
      class="w-full flex items-center gap-2 rounded-md border border-light-500 bg-light-100 px-3 py-2 text-xs text-left hover:bg-light-200 dark:border-dark-500 dark:bg-dark-400 dark:text-dark-900 dark:hover:bg-dark-500 transition-colors"
    >
      <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
      </svg>
      <span v-if="dueDate" class="text-light-1000 dark:text-dark-1000">{{ formattedDate }}</span>
      <span v-else class="text-light-800 dark:text-dark-700">Set due date</span>
    </button>
    <div v-if="open" class="absolute left-0 top-full mt-1 bg-light-50 dark:bg-dark-200 rounded-lg shadow-lg border border-light-300 dark:border-dark-400 p-2 z-10" @click.stop>
      <input
        type="date"
        :value="dueDateValue"
        @change="updateDate(($event.target as HTMLInputElement).value)"
        class="input text-xs w-full"
      />
      <button v-if="dueDate" @click="clearDate" class="mt-2 text-xs text-red-500 hover:text-red-600 w-full text-left">Clear date</button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useToast } from '@/composables/useToast'
import { cardService } from '@/services/card.service'

const props = defineProps<{
  cardPublicId: string
  dueDate: string | null
}>()

const emit = defineEmits<{
  update: []
}>()

const toast = useToast()
const open = ref(false)

const dueDateValue = computed(() => {
  if (!props.dueDate) return ''
  return props.dueDate.split('T')[0]
})

const formattedDate = computed(() => {
  if (!props.dueDate) return ''
  return new Date(props.dueDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
})

async function updateDate(date: string) {
  try {
    await cardService.update(props.cardPublicId, { dueDate: date ? new Date(date).toISOString() : null })
    open.value = false
    emit('update')
    toast.success('Due date updated')
  } catch (error: any) {
    toast.error('Failed to update due date')
  }
}

async function clearDate() {
  try {
    await cardService.update(props.cardPublicId, { dueDate: null })
    open.value = false
    emit('update')
    toast.success('Due date removed')
  } catch (error: any) {
    toast.error('Failed to remove due date')
  }
}
</script>
