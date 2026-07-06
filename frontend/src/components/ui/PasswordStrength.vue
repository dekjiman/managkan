<template>
  <div v-if="password" class="space-y-2">
    <div class="flex gap-1">
      <div
        v-for="i in 4"
        :key="i"
        class="h-1 flex-1 rounded-full transition-colors"
        :class="i <= strength.score ? strength.color : 'bg-light-300 dark:bg-dark-500'"
      />
    </div>
    <p class="text-xs" :class="strength.textColor">
      {{ strength.label }}
    </p>
    <ul class="text-xs space-y-0.5">
      <li v-for="req in requirements" :key="req.label" :class="req.met ? 'text-green-600 dark:text-green-400' : 'text-light-700 dark:text-dark-600'">
        <span class="mr-1">{{ req.met ? '\u2713' : '\u25CB' }}</span>
        {{ req.label }}
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  password: string
}>()

const requirements = computed(() => [
  { label: 'At least 6 characters', met: props.password.length >= 6 },
  { label: 'Contains uppercase letter', met: /[A-Z]/.test(props.password) },
  { label: 'Contains lowercase letter', met: /[a-z]/.test(props.password) },
  { label: 'Contains number', met: /[0-9]/.test(props.password) },
])

const strength = computed(() => {
  const score = requirements.value.filter(r => r.met).length
  if (score <= 1) return { score: 1, color: 'bg-red-500', label: 'Weak', textColor: 'text-red-600 dark:text-red-400' }
  if (score === 2) return { score: 2, color: 'bg-orange-500', label: 'Fair', textColor: 'text-orange-600 dark:text-orange-400' }
  if (score === 3) return { score: 3, color: 'bg-yellow-500', label: 'Good', textColor: 'text-yellow-600 dark:text-yellow-400' }
  return { score: 4, color: 'bg-green-500', label: 'Strong', textColor: 'text-green-600 dark:text-green-400' }
})
</script>
