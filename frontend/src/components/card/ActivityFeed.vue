<template>
  <div class="flex flex-col space-y-6">
    <div v-for="item in sortedActivities" :key="item.publicId || item.id" class="flex gap-3">
      <Avatar :name="item.userName || 'User'" size="sm" class="mt-0.5 shrink-0" />
      <div class="min-w-0 flex-1">
        <div class="flex items-baseline gap-2">
          <span class="text-sm font-medium text-light-1000 dark:text-dark-1000">{{ item.userName || 'User' }}</span>
          <span class="text-xs text-light-700 dark:text-dark-800">{{ formatTimeAgo(item.createdAt) }}</span>
        </div>
        <p v-if="item.type === 'card.updated.comment.added'" class="mt-1 text-sm text-light-900 dark:text-dark-800 whitespace-pre-wrap break-words">{{ item.commentText || item.toComment }}</p>
        <p v-else class="mt-1 text-sm italic text-light-700 dark:text-dark-700">{{ describe(item) }}</p>
      </div>
    </div>

    <div v-if="sortedActivities.length === 0" class="py-6 text-center">
      <p class="text-sm text-light-700 dark:text-dark-700">No activity yet. Be the first to comment.</p>
    </div>

    <div class="mt-4 relative">
      <form @submit.prevent="addComment" class="flex w-full max-w-[800px] flex-col rounded-xl border border-light-600 bg-light-100 p-4 dark:border-dark-400 dark:bg-dark-100">
        <textarea
          ref="commentTextarea"
          v-model="newComment"
          placeholder="Add comment... Type @ to mention someone"
          rows="3"
          @input="handleCommentInput"
          @keydown="handleCommentKeydown"
          class="w-full bg-transparent border-none focus:outline-none text-sm resize-none"
        />
        <div class="flex justify-end">
          <button type="submit" :disabled="!newComment.trim()" class="flex h-8 w-8 items-center justify-center rounded-full border border-light-600 bg-light-300 hover:bg-light-400 disabled:opacity-50 dark:border-dark-400 dark:bg-dark-200 dark:hover:bg-dark-400">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
          </button>
        </div>
      </form>

      <div
        v-if="mentionSearch"
        class="absolute bottom-full left-0 mb-1 z-50 min-w-[200px] rounded-md border border-light-200 dark:border-dark-300 bg-white dark:bg-dark-200 py-1 shadow-lg"
      >
        <div
          v-for="(member, i) in filteredMentions"
          :key="member.publicId"
          @click="selectMention(member)"
          class="flex items-center gap-2 px-3 py-2 text-sm cursor-pointer"
          :class="i === mentionIndex ? 'bg-light-200 dark:bg-dark-400' : 'hover:bg-light-200 dark:hover:bg-dark-400'"
        >
          <Avatar :name="member.user?.name || member.email || 'U'" size="sm" />
          <span class="text-light-1000 dark:text-dark-1000">{{ member.user?.name || member.email }}</span>
        </div>
        <div v-if="filteredMentions.length === 0" class="px-3 py-2 text-sm text-light-700 dark:text-dark-700">
          No members found
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, nextTick, onMounted } from 'vue'
import { useToast } from '@/composables/useToast'
import { commentService } from '@/services/comment.service'
import { activityService } from '@/services/activity.service'
import Avatar from '@/components/ui/Avatar.vue'

const props = defineProps<{
  cardId: number
  workspaceMembers: any[]
}>()

const emit = defineEmits<{
  (e: 'activity-added'): void
}>()

const toast = useToast()

const activities = ref<any[]>([])
const newComment = ref('')
const commentTextarea = ref<HTMLTextAreaElement | null>(null)
const mentionSearch = ref('')
const mentionIndex = ref(0)
const mentionStart = ref(-1)
const mentionedUserIds = ref<string[]>([])

const filteredMentions = computed(() => {
  if (!mentionSearch.value) return props.workspaceMembers
  const q = mentionSearch.value.toLowerCase()
  return props.workspaceMembers.filter((m: any) =>
    (m.user?.name || '').toLowerCase().includes(q) ||
    (m.email || '').toLowerCase().includes(q)
  )
})

const sortedActivities = computed(() => {
  return [...activities.value].sort((a, b) =>
    new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime()
  )
})

async function loadActivities() {
  try {
    const res = await activityService.getByCard(props.cardId)
    activities.value = res.data || []
  } catch {
    toast.error('Failed to load activities')
  }
}

onMounted(loadActivities)

async function addComment() {
  if (!newComment.value.trim()) return
  try {
    await commentService.create({
      cardId: props.cardId,
      comment: newComment.value.trim(),
      mentionedUserIds: mentionedUserIds.value.length > 0 ? mentionedUserIds.value : undefined,
    })
    newComment.value = ''
    mentionedUserIds.value = []
    await loadActivities()
    emit('activity-added')
    toast.success('Comment added')
  } catch (error: any) {
    console.error('Failed to add comment:', error)
    const msg = error?.response?.data?.message || error?.message || 'Failed to add comment'
    toast.error(msg)
  }
}

function describe(activity: any): string {
  switch (activity.type) {
    case 'card.updated.comment.deleted':
      return 'deleted a comment'
    case 'card.created':
      return 'added this card'
    default:
      return 'made a change'
  }
}

import { formatTimeAgo } from '@/utils/date'

function handleCommentInput() {
  const el = commentTextarea.value
  if (!el) return
  const pos = el.selectionStart
  const text = newComment.value.slice(0, pos)
  const atIdx = text.lastIndexOf('@')

  if (atIdx >= 0 && (atIdx === 0 || text[atIdx - 1] === ' ')) {
    const after = text.slice(atIdx + 1)
    if (!after.includes(' ')) {
      mentionStart.value = atIdx
      mentionSearch.value = after
      mentionIndex.value = 0
      return
    }
  }
  mentionSearch.value = ''
  mentionStart.value = -1
}

function handleCommentKeydown(e: KeyboardEvent) {
  if (!mentionSearch.value) return
  if (e.key === 'ArrowDown') {
    e.preventDefault()
    mentionIndex.value = Math.min(mentionIndex.value + 1, filteredMentions.value.length - 1)
  } else if (e.key === 'ArrowUp') {
    e.preventDefault()
    mentionIndex.value = Math.max(mentionIndex.value - 1, 0)
  } else if (e.key === 'Enter' || e.key === 'Tab') {
    if (filteredMentions.value[mentionIndex.value]) {
      e.preventDefault()
      selectMention(filteredMentions.value[mentionIndex.value])
    }
  } else if (e.key === 'Escape') {
    mentionSearch.value = ''
    mentionStart.value = -1
  }
}

function selectMention(member: any) {
  if (mentionStart.value < 0) return
  const name = member.user?.name || member.email?.split('@')[0] || 'user'
  const userId = member.userId
  const before = newComment.value.slice(0, mentionStart.value)
  const after = newComment.value.slice(mentionStart.value + mentionSearch.value.length + 1)
  newComment.value = before + '@' + name + ' ' + after
  if (userId && !mentionedUserIds.value.includes(userId)) {
    mentionedUserIds.value.push(userId)
  }
  mentionSearch.value = ''
  mentionStart.value = -1
  nextTick(() => {
    commentTextarea.value?.focus()
  })
}
</script>
