<template>
  <div class="relative flex h-screen flex-col bg-light-50 dark:bg-dark-50 md:bg-light-100 md:p-3 md:dark:bg-dark-100">
    <!-- Mobile Header -->
    <div class="flex h-12 items-center justify-between border-b border-light-300 bg-light-50 px-3 dark:border-dark-300 dark:bg-dark-50 md:hidden">
      <button
        ref="toggleRef"
        @click="toggleSidebar"
        class="rounded p-1.5 transition-all hover:bg-light-200 dark:hover:bg-dark-100"
      >
        <svg v-if="isSidebarOpen" class="w-5 h-5 text-light-900 dark:text-dark-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
        </svg>
        <svg v-else class="w-5 h-5 text-light-900 dark:text-dark-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
        </svg>
      </button>
    </div>

    <div class="flex h-[calc(100dvh-4.5rem)] min-h-0 w-full md:h-[calc(100dvh-1.5rem)]">
      <!-- Mobile Backdrop -->
      <Transition name="fade">
        <div
          v-if="isSidebarOpen"
          class="fixed inset-0 z-30 bg-black/40 md:hidden"
          @click="isSidebarOpen = false"
        />
      </Transition>

      <!-- Sidebar -->
      <div
        ref="sidebarRef"
        class="fixed top-12 z-40 h-[calc(100dvh-3rem)] w-64 max-w-[90vw] transform transition-transform duration-300 ease-in-out md:relative md:top-0 md:h-full md:w-auto md:translate-x-0 md:max-w-none"
        :class="isSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
      >
        <Sidebar @close="isSidebarOpen = false" />
      </div>

      <!-- Main Content -->
      <div class="relative h-full min-h-0 w-full overflow-hidden md:rounded-lg md:border md:border-light-300 md:bg-light-50 md:dark:border-dark-300 md:dark:bg-dark-50">
        <div class="relative flex h-full min-h-0 w-full overflow-hidden">
          <div class="h-full w-full overflow-y-auto">
            <slot />
          </div>
        </div>
      </div>
    </div>

    <Toast />
  </div>
</template>

<style>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useUIStore } from '@/stores/ui'
import Sidebar from './Sidebar.vue'
import Toast from '@/components/ui/Toast.vue'

const uiStore = useUIStore()
const sidebarRef = ref<HTMLElement | null>(null)
const toggleRef = ref<HTMLElement | null>(null)

const isSidebarOpen = computed({
  get: () => uiStore.sidebarOpen,
  set: (val: boolean) => { uiStore.sidebarOpen = val }
})

function toggleSidebar() {
  uiStore.toggleSidebar()
}

function handleClickOutside(e: MouseEvent) {
  const target = e.target as Node
  if (sidebarRef.value && !sidebarRef.value.contains(target) && toggleRef.value && !toggleRef.value.contains(target)) {
    uiStore.sidebarOpen = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
