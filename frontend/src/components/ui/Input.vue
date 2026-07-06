<template>
  <div class="w-full">
    <label v-if="label" :for="id" class="block text-xs font-medium text-light-900 dark:text-dark-800 mb-1">
      {{ label }}
    </label>
    <input
      :id="id"
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      :disabled="disabled"
      class="input"
      :class="{ 'border-red-500 focus:ring-red-500 dark:border-red-400 dark:focus:ring-red-400': error }"
      @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
    />
    <p v-if="error" class="mt-1 text-xs text-red-500">{{ error }}</p>
  </div>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
  modelValue: string
  label?: string
  type?: string
  placeholder?: string
  disabled?: boolean
  error?: string
  id?: string
}>(), {
  type: 'text',
  placeholder: '',
  disabled: false
})

defineEmits<{
  'update:modelValue': [value: string]
}>()
</script>
