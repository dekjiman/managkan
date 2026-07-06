<template>
  <div class="relative w-full">
    <button
      @click.stop="open = !open"
      class="w-full flex items-center gap-2 rounded-md border border-light-500 bg-light-100 px-3 py-2 text-xs text-left hover:bg-light-200 dark:border-dark-500 dark:bg-dark-400 dark:text-dark-900 dark:hover:bg-dark-500 transition-colors"
    >
      <div v-if="selectedLabels.length" class="flex flex-wrap gap-1">
        <span
          v-for="label in selectedLabels"
          :key="label.publicId"
          class="inline-flex items-center gap-1 rounded-full bg-light-200 dark:bg-dark-300 px-2 py-0.5 text-[10px] font-medium text-light-1000 dark:text-dark-1000"
        >
          <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: label.colourCode }" />
          {{ label.name }}
        </span>
      </div>
      <span v-else class="text-light-800 dark:text-dark-700">+ Add label</span>
    </button>
    <div v-if="open" class="absolute left-0 top-full mt-1 w-48 bg-light-50 dark:bg-dark-200 rounded-lg shadow-lg border border-light-300 dark:border-dark-400 py-1 z-10 max-h-48 overflow-y-auto" @click.stop>
      <button
        v-for="label in boardLabels"
        :key="label.publicId"
        @click="toggleLabel(label.publicId)"
        class="w-full text-left px-3 py-1.5 text-xs hover:bg-light-200 dark:hover:bg-dark-300 transition-colors flex items-center gap-2"
        :class="{ 'bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400': isSelected(label.publicId) }"
      >
        <span class="w-2.5 h-2.5 rounded-full shrink-0" :style="{ backgroundColor: label.colourCode }" />
        <span class="truncate">{{ label.name }}</span>
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
  cardLabels: any[]
  boardLabels: any[]
}>()

const emit = defineEmits<{
  update: []
}>()

const toast = useToast()
const open = ref(false)

const selectedLabels = computed(() => props.cardLabels)

function isSelected(labelPublicId: string) {
  return props.cardLabels.some(l => l.publicId === labelPublicId)
}

async function toggleLabel(labelPublicId: string) {
  try {
    await cardService.toggleLabel(props.cardPublicId, labelPublicId)
    emit('update')
  } catch (error: any) {
    toast.error('Failed to update label')
  }
}
</script>
