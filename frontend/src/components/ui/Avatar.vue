<template>
  <div :class="['rounded-full flex items-center justify-center font-medium shrink-0', sizeClasses, colorClasses]">
    <img v-if="src && !imgError" :src="src" :alt="name" class="w-full h-full rounded-full object-cover" @error="imgError = true" />
    <span v-else>{{ initials }}</span>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

const props = withDefaults(defineProps<{
  name: string
  src?: string | null
  size?: 'sm' | 'md' | 'lg'
}>(), {
  size: 'md'
})

const imgError = ref(false)

const initials = computed(() => {
  return props.name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

const sizeClasses = computed(() => {
  const sizes = {
    sm: 'w-5 h-5 text-[9px]',
    md: 'w-7 h-7 text-[11px]',
    lg: 'w-9 h-9 text-xs'
  }
  return sizes[props.size]
})

const colorClasses = computed(() => {
  const colors = [
    'bg-primary-100 text-primary-700',
    'bg-green-100 text-green-700',
    'bg-yellow-100 text-yellow-700',
    'bg-red-100 text-red-700',
    'bg-purple-100 text-purple-700',
    'bg-pink-100 text-pink-700'
  ]
  const index = props.name.charCodeAt(0) % colors.length
  return colors[index]
})
</script>
