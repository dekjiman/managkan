<template>
  <div class="min-h-screen flex items-center justify-center bg-light-100 dark:bg-dark-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 text-center">
      <div class="w-16 h-16 bg-light-200 dark:bg-dark-300 rounded-xl flex items-center justify-center mx-auto">
        <span class="text-3xl font-bold text-light-700 dark:text-dark-600">404</span>
      </div>
      <h1 class="text-3xl font-bold text-light-1000 dark:text-dark-1000">
        Page not found
      </h1>
      <p class="text-sm text-light-800 dark:text-dark-700">
        The page you're looking for doesn't exist or has been moved.
      </p>
      <router-link to="/dashboard">
        <Button>Back to Dashboard</Button>
      </router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import Button from '@/components/ui/Button.vue'
</script>
