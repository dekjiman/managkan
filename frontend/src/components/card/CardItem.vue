<template>
  <div
    class="flex flex-col overflow-hidden rounded-md border border-light-200 dark:border-dark-200 bg-light-50 dark:bg-dark-200 px-3 py-2 text-sm text-light-1000 dark:text-dark-1000 hover:bg-light-100 dark:hover:bg-dark-300 cursor-pointer transition-colors"
    @click="$emit('click')"
    @contextmenu.prevent="$emit('contextmenu', $event)"
  >
    <!-- Labels -->
    <div v-if="card.labels?.length" class="mb-1 flex flex-wrap gap-0.5">
      <span
        v-for="label in card.labels"
        :key="label.publicId"
        class="inline-flex items-center gap-1 rounded-full px-1.5 py-0.5 text-[10px] font-medium text-light-1000 dark:text-dark-1000"
        :style="{ backgroundColor: label.colourCode + '20' }"
      >
        <span class="w-1.5 h-1.5 rounded-full" :style="{ backgroundColor: label.colourCode }" />
        {{ label.name }}
      </span>
    </div>

    <!-- Title -->
    <span class="break-words">{{ card.title }}</span>

    <!-- Footer -->
    <div v-if="hasFooter" class="mt-2 flex items-center justify-between gap-1">
      <div class="flex items-center gap-2">
        <!-- Description indicator -->
        <div v-if="card.description" class="text-light-700 dark:text-dark-800">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 16h7" />
          </svg>
        </div>

        <!-- Due date -->
        <div
          v-if="card.dueDate"
          :class="[
            'flex items-center gap-1',
            isOverdue ? 'text-red-600 dark:text-red-400' : 'text-light-800 dark:text-dark-800'
          ]"
        >
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="text-[11px]">{{ formattedDate }}</span>
        </div>

        <!-- Comments indicator -->
        <div v-if="card.comments?.length" class="text-light-700 dark:text-dark-800">
          <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
          </svg>
        </div>
      </div>

      <div class="flex items-center justify-end gap-1">
        <!-- Checklist progress -->
        <div v-if="checklistTotal > 0" class="flex items-center gap-1 rounded-full border border-light-300 dark:border-dark-600 px-2 py-0.5">
          <svg class="h-3 w-3" :class="checklistComplete === checklistTotal ? 'text-green-500' : 'text-light-800 dark:text-dark-800'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="text-[10px] text-light-900 dark:text-dark-950">{{ checklistComplete }}/{{ checklistTotal }}</span>
        </div>

        <!-- Members -->
        <div v-if="card.members?.length" class="flex -space-x-1">
          <Avatar
            v-for="member in card.members.slice(0, 3)"
            :key="member.publicId"
            :name="member.userName || 'User'"
            size="sm"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import Avatar from '@/components/ui/Avatar.vue'

const props = defineProps<{
  card: any
}>()

defineEmits<{
  click: []
  contextmenu: [e: MouseEvent]
}>()

const hasFooter = computed(() => {
  return props.card.description ||
    props.card.dueDate ||
    props.card.comments?.length ||
    props.card.labels?.length ||
    props.card.members?.length ||
    checklistTotal.value > 0
})

const checklistComplete = computed(() => {
  return props.card.checklists?.reduce((acc: number, cl: any) => {
    return acc + cl.items?.filter((i: any) => i.completed).length || 0
  }, 0) || 0
})

const checklistTotal = computed(() => {
  return props.card.checklists?.reduce((acc: number, cl: any) => {
    return acc + (cl.items?.length || 0)
  }, 0) || 0
})

const isOverdue = computed(() => {
  if (!props.card.dueDate) return false
  return new Date(props.card.dueDate) < new Date()
})

const formattedDate = computed(() => {
  if (!props.card.dueDate) return ''
  const date = new Date(props.card.dueDate)
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
})
</script>
