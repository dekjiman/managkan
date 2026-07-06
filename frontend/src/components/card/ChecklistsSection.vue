<template>
  <div>
    <div v-for="checklist in checklists" :key="checklist.publicId" class="mb-4">
      <!-- Checklist Header -->
      <div class="mb-2 flex items-center font-medium text-light-1000 dark:text-dark-1000">
        <div class="min-w-0 flex-1">
          <input
            :value="checklist.name"
            @blur="updateChecklistName(checklist, ($event.target as HTMLInputElement).value)"
            class="w-full border-0 bg-transparent text-sm font-medium text-light-1000 dark:text-dark-1000 focus:ring-0 focus-visible:outline-none"
          />
        </div>
        <div class="ml-2 flex flex-shrink-0 items-center gap-2">
          <div class="flex items-center gap-1 rounded-full border border-light-300 dark:border-dark-300 px-2 py-1">
            <svg class="w-3 h-3" :class="getProgressColor(checklist)" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-[11px] text-light-900 dark:text-dark-700">
              {{ getCompletedCount(checklist) }}/{{ checklist.items?.length || 0 }}
            </span>
          </div>
          <button
            @click="showNewItemForm = checklist.publicId"
            class="rounded-md p-1 text-light-900 hover:bg-light-100 dark:text-dark-700 dark:hover:bg-dark-100"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Checklist Items -->
      <div class="ml-1 space-y-0.5">
        <div
          v-for="item in checklist.items"
          :key="item.publicId"
          class="group relative flex items-start gap-3 rounded-md py-2 pl-4 hover:bg-light-100 dark:hover:bg-dark-100"
        >
          <label class="relative mt-0.5 inline-flex h-4 w-4 flex-shrink-0 items-center justify-center">
            <input
              type="checkbox"
              :checked="item.completed"
              @change="toggleItem(item)"
              class="h-4 w-4 appearance-none rounded-md border border-light-500 dark:border-dark-500 bg-transparent checked:bg-blue-600 cursor-pointer"
            />
          </label>
          <div class="flex-1 pr-7">
            <input
              :value="item.title"
              @blur="updateItemTitle(item, ($event.target as HTMLInputElement).value)"
              :class="['w-full border-0 bg-transparent text-sm text-light-950 dark:text-dark-950 focus:ring-0 focus-visible:outline-none', item.completed ? 'line-through text-light-800 dark:text-dark-700' : '']"
            />
          </div>
          <button
            @click="deleteItem(item)"
            class="absolute right-1 top-1/2 hidden -translate-y-1/2 rounded-md p-1 text-light-900 group-hover:block hover:bg-light-200 dark:text-dark-700 dark:hover:bg-dark-200"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- New Item Form -->
      <div v-if="showNewItemForm === checklist.publicId" class="ml-1 mt-1">
        <input
          v-model="newItemTitle"
          @keydown.enter="addItem(checklist)"
          @keydown.escape="showNewItemForm = ''"
          placeholder="Add item..."
          class="w-full px-3 py-1.5 text-sm bg-transparent border-none focus:outline-none text-light-1000 dark:text-dark-1000 placeholder-light-700 dark:placeholder-dark-600"
          autofocus
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useToast } from '@/composables/useToast'
import { checklistService } from '@/services/checklist.service'

defineProps<{
  checklists: any[]
}>()

const emit = defineEmits<{
  update: []
}>()

const toast = useToast()
const showNewItemForm = ref('')
const newItemTitle = ref('')

function getCompletedCount(checklist: any) {
  return checklist.items?.filter((i: any) => i.completed).length || 0
}

function getProgressColor(checklist: any) {
  const total = checklist.items?.length || 0
  const completed = getCompletedCount(checklist)
  if (total === 0) return 'text-light-800 dark:text-dark-700'
  if (completed === total) return 'text-green-500'
  return 'text-light-800 dark:text-dark-700'
}

async function updateChecklistName(checklist: any, newName: string) {
  if (!newName.trim() || newName === checklist.name) return
  try {
    await checklistService.update(checklist.publicId, { name: newName.trim() })
    emit('update')
  } catch {
    toast.error('Failed to update checklist name')
  }
}

async function toggleItem(item: any) {
  try {
    await checklistService.updateItem(item.publicId, { completed: !item.completed })
    emit('update')
  } catch {
    toast.error('Failed to update item')
  }
}

async function updateItemTitle(item: any, newTitle: string) {
  if (!newTitle.trim() || newTitle === item.title) return
  try {
    await checklistService.updateItem(item.publicId, { title: newTitle.trim() })
    emit('update')
  } catch {
    toast.error('Failed to update item')
  }
}

async function deleteItem(item: any) {
  try {
    await checklistService.deleteItem(item.publicId)
    emit('update')
  } catch {
    toast.error('Failed to delete item')
  }
}

async function addItem(checklist: any) {
  if (!newItemTitle.value.trim()) return
  try {
    await checklistService.createItem(checklist.publicId, { title: newItemTitle.value.trim() })
    newItemTitle.value = ''
    showNewItemForm.value = ''
    emit('update')
  } catch {
    toast.error('Failed to add item')
  }
}
</script>
