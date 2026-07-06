<template>
  <div class="h-10 border-b border-light-300 dark:border-dark-400 flex items-center justify-between px-4 bg-light-50 dark:bg-dark-100">
    <div class="flex items-center gap-4">
      <h1 class="font-semibold text-light-1000 dark:text-dark-1000">{{ board.name }}</h1>
      <Badge :variant="board.visibility === 'public' ? 'success' : 'default'">
        {{ board.visibility }}
      </Badge>
    </div>
    <div class="flex items-center gap-2">
      <Button variant="ghost" size="sm" @click="$emit('filter')">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
        </svg>
        Filter
      </Button>
    </div>
  </div>
</template>

<script setup lang="ts">
import Badge from '@/components/ui/Badge.vue'
import Button from '@/components/ui/Button.vue'

defineProps<{
  board: any
}>()

defineEmits<{
  filter: []
}>()
</script>
