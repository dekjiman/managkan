<template>
  <AppLayout>
    <SettingsLayout>
      <div class="text-center py-12">
        <p class="text-light-800 dark:text-dark-700">Redirecting to account settings...</p>
      </div>
    </SettingsLayout>
  </AppLayout>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import AppLayout from '@/components/layout/AppLayout.vue'
import SettingsLayout from '@/components/layout/SettingsLayout.vue'

const router = useRouter()

onMounted(() => {
  router.replace('/settings/account')
})
</script>
