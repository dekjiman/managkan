<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="isOpen"
        ref="modalRef"
        class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="titleId"
        @keydown.esc="close"
      >
        <div class="flex min-h-full items-center justify-center p-4">
          <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" @click="close" />
          <div class="relative w-full max-w-md transform rounded-xl bg-light-50 dark:bg-dark-200 text-left shadow-2xl transition-all">
            <div class="px-5 py-4 border-b border-light-300 dark:border-dark-400">
              <div class="flex items-center justify-between">
                <h3 :id="titleId" class="text-sm font-semibold text-light-1000 dark:text-dark-1000">
                  <slot name="header" />
                </h3>
                <button
                  ref="closeBtnRef"
                  @click="close"
                  aria-label="Close dialog"
                  class="p-1 text-light-700 hover:text-light-1000 dark:text-dark-600 dark:hover:text-dark-1000 rounded-md hover:bg-light-200 dark:hover:bg-dark-300 transition-colors"
                >
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>
            <div class="px-5 py-4">
              <slot />
            </div>
            <div v-if="$slots.footer" class="px-5 py-3 border-t border-light-300 dark:border-dark-400 bg-light-100 dark:bg-dark-100">
              <slot name="footer" />
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, watch, nextTick, onUnmounted } from 'vue'

const props = defineProps<{
  isOpen: boolean
}>()

const emit = defineEmits<{
  close: []
}>()

const modalRef = ref<HTMLElement | null>(null)
const closeBtnRef = ref<HTMLElement | null>(null)
const titleId = `modal-title-${Math.random().toString(36).slice(2, 9)}`

let previousActiveElement: HTMLElement | null = null

function close() {
  emit('close')
}

function getFocusableElements(): HTMLElement[] {
  if (!modalRef.value) return []
  return Array.from(
    modalRef.value.querySelectorAll<HTMLElement>(
      'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'
    )
  )
}

function handleKeydown(e: KeyboardEvent) {
  if (e.key !== 'Tab' || !modalRef.value) return

  const focusable = getFocusableElements()
  if (focusable.length === 0) return

  const first = focusable[0]
  const last = focusable[focusable.length - 1]

  if (e.shiftKey) {
    if (document.activeElement === first) {
      e.preventDefault()
      last.focus()
    }
  } else {
    if (document.activeElement === last) {
      e.preventDefault()
      first.focus()
    }
  }
}

watch(() => props.isOpen, async (open) => {
  if (open) {
    previousActiveElement = document.activeElement as HTMLElement
    document.body.style.overflow = 'hidden'
    await nextTick()
    closeBtnRef.value?.focus()
    document.addEventListener('keydown', handleKeydown)
  } else {
    document.body.style.overflow = ''
    document.removeEventListener('keydown', handleKeydown)
    previousActiveElement?.focus()
  }
})

onUnmounted(() => {
  document.body.style.overflow = ''
  document.removeEventListener('keydown', handleKeydown)
})
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.15s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
  transition: transform 0.15s ease, opacity 0.15s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
  transform: scale(0.95);
  opacity: 0;
}
</style>
