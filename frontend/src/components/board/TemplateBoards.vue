<template>
  <div v-if="show" class="mt-3">
    <div class="relative">
      <div
        ref="scrollRef"
        class="flex flex-col gap-2 max-h-[200px] overflow-y-auto pr-1"
        @scroll="handleScroll"
      >
        <button
          v-for="template in allTemplates"
          :key="template.id"
          type="button"
          @click="selectTemplate(template)"
          :data-template-id="template.id"
          class="relative text-left flex cursor-pointer rounded-lg border p-3 transition-all hover:bg-light-100 dark:hover:bg-dark-200"
          :class="selectedTemplate?.id === template.id
            ? 'border-light-700 bg-light-100 ring-1 ring-inset ring-light-700 dark:border-dark-700 dark:bg-dark-200 dark:ring-dark-700'
            : 'border-light-500 dark:border-dark-500'"
        >
          <div class="flex-1 min-w-0">
            <h4 class="text-sm font-medium text-light-1000 dark:text-dark-1000">{{ template.name }}</h4>
            <p class="text-xs text-light-800 dark:text-dark-700 truncate">{{ template.lists.join(', ') }}</p>
          </div>
          <svg v-if="selectedTemplate?.id === template.id" class="absolute right-3 top-3 w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </button>
      </div>
      <div v-if="showTopFade" class="pointer-events-none absolute left-0 right-0 top-0 h-6 bg-gradient-to-b from-light-50 dark:from-dark-200 to-transparent" />
      <div v-if="showFade" class="pointer-events-none absolute bottom-0 left-0 right-0 h-6 bg-gradient-to-t from-light-50 dark:from-dark-200 to-transparent" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, nextTick, onMounted } from 'vue'

export interface Template {
  id: string
  sourceBoardPublicId?: string
  name: string
  lists: string[]
  labels: string[]
}

const props = defineProps<{
  show: boolean
  selectedTemplate: Template | null
  customTemplates?: Template[]
}>()

const emit = defineEmits<{
  select: [template: Template | null]
}>()

const scrollRef = ref<HTMLDivElement | null>(null)
const showFade = ref(false)
const showTopFade = ref(false)

const defaultTemplates: Template[] = [
  {
    id: 'basic',
    name: 'Basic Kanban',
    lists: ['To Do', 'In Progress', 'Done'],
    labels: ['High Priority', 'Medium Priority', 'Low Priority']
  },
  {
    id: 'software-dev',
    name: 'Software Development',
    lists: ['Backlog', 'To Do', 'In Progress', 'Code Review', 'Done'],
    labels: ['Bug', 'Feature', 'Enhancement', 'Critical', 'Documentation']
  },
  {
    id: 'roadmap-basic',
    name: 'Basic Roadmap',
    lists: ['Requested', 'Planned', 'In Progress', 'Done'],
    labels: ['Feature', 'Enhancement', 'Critical', 'Documentation']
  },
  {
    id: 'roadmap-extended',
    name: 'Extended Roadmap',
    lists: ['Requested', 'Under Review', 'Planned', 'In Progress', 'Done', 'Rejected'],
    labels: ['Feature', 'Enhancement', 'Critical', 'Documentation']
  },
  {
    id: 'content-creation',
    name: 'Content Creation',
    lists: ['Brainstorming', 'Writing', 'Editing', 'Design', 'Approval', 'Publishing', 'Done'],
    labels: ['Blog Post', 'Social Media', 'Video', 'Newsletter', 'Urgent']
  },
  {
    id: 'customer-support',
    name: 'Customer Support',
    lists: ['New Ticket', 'Triaging', 'In Progress', 'Awaiting Customer', 'Resolution', 'Done'],
    labels: ['Bug Report', 'Feature Request', 'Question', 'Urgent', 'Billing']
  },
  {
    id: 'recruitment',
    name: 'Recruitment',
    lists: ['Applicants', 'Screening', 'Interviewing', 'Offer', 'Onboarding', 'Hired'],
    labels: ['Remote', 'Full-time', 'Part-time', 'Senior', 'Junior']
  },
  {
    id: 'personal-project',
    name: 'Personal Project',
    lists: ['Ideas', 'Research', 'Planning', 'Execution', 'Review', 'Next Steps', 'Complete'],
    labels: ['Important', 'Quick Win', 'Long-term', 'Learning', 'Fun']
  }
]

const allTemplates = computed(() => {
  return [...(props.customTemplates ?? []), ...defaultTemplates]
})

function handleScroll() {
  if (!scrollRef.value) return
  const { scrollTop, scrollHeight, clientHeight } = scrollRef.value
  showFade.value = scrollTop + clientHeight < scrollHeight - 5
  showTopFade.value = scrollTop > 5
}

function selectTemplate(template: Template) {
  if (props.selectedTemplate?.id === template.id) {
    emit('select', null)
  } else {
    emit('select', template)
  }
}

watch(() => props.show, async (val) => {
  if (val) {
    await nextTick()
    handleScroll()
  }
})
</script>
