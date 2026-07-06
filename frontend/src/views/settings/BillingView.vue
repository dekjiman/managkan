<template>
  <SettingsLayout>
    <div>
      <h2 class="text-sm font-bold text-light-1000 dark:text-dark-1000">Billing & plan</h2>
      <p class="text-sm text-light-800 dark:text-dark-700 mt-1 mb-8">Manage your subscription and billing information.</p>

      <div v-if="loading" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-light-1000 dark:border-dark-1000" />
      </div>

      <template v-else>
        <!-- Current Plan -->
        <div class="rounded-lg border border-light-300 dark:border-dark-400 p-6 mb-8">
          <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
              <p class="text-sm font-medium text-light-1000 dark:text-dark-1000">Current plan</p>
              <p class="text-2xl font-bold text-light-1000 dark:text-dark-1000 mt-1">
                {{ currentPlan?.displayName || 'Free' }}
              </p>
              <p v-if="billing?.subscription?.endDate" class="text-xs text-light-800 dark:text-dark-700 mt-1">
                Renews on {{ formatDate(billing.subscription.endDate) }}
              </p>
              <p v-else class="text-xs text-light-800 dark:text-dark-700 mt-1">
                {{ currentPlan?.displayName || 'Free' }} plan
              </p>
            </div>
            <Badge v-if="billing?.plan !== 'free'" variant="success">Active</Badge>
          </div>
        </div>

        <!-- Usage -->
        <h3 class="text-sm font-bold text-light-1000 dark:text-dark-1000 mb-4">Usage</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
          <div class="rounded-lg border border-light-300 dark:border-dark-400 p-4">
            <p class="text-xs text-light-800 dark:text-dark-700">Workspaces</p>
            <p class="text-lg font-semibold text-light-1000 dark:text-dark-1000 mt-1">
              {{ workspaceUsageCount }} / {{ workspaceLimitLabel }}
            </p>
            <div class="mt-2 h-1.5 rounded-full bg-light-300 dark:bg-dark-400 overflow-hidden">
              <div class="h-full rounded-full bg-light-1000 dark:bg-dark-1000 transition-all" :style="{ width: workspacePercent + '%' }" />
            </div>
          </div>
          <div class="rounded-lg border border-light-300 dark:border-dark-400 p-4">
            <p class="text-xs text-light-800 dark:text-dark-700">Members</p>
            <p class="text-lg font-semibold text-light-1000 dark:text-dark-1000 mt-1">
              {{ usage.members }} / {{ memberLimitLabel }}
            </p>
            <div class="mt-2 h-1.5 rounded-full bg-light-300 dark:bg-dark-400 overflow-hidden">
              <div class="h-full rounded-full bg-light-1000 dark:bg-dark-1000 transition-all" :style="{ width: memberPercent + '%' }" />
            </div>
          </div>
        </div>

        <!-- Plans -->
        <h3 class="text-sm font-bold text-light-1000 dark:text-dark-1000 mb-4">Compare plans</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
          <div
            v-for="p in plans"
            :key="p.name"
            class="rounded-lg border p-5 flex flex-col"
            :class="currentPlan?.name === p.name
              ? 'border-2 border-light-1000 dark:border-dark-1000 relative'
              : 'border-light-300 dark:border-dark-400'"
          >
            <div v-if="p.name === 'team'" class="absolute -top-2.5 left-5 px-2 text-[10px] font-semibold bg-light-1000 dark:bg-dark-1000 text-light-50 dark:text-dark-50 rounded">
              POPULAR
            </div>
            <h4 class="text-sm font-semibold text-light-1000 dark:text-dark-1000">{{ p.displayName }}</h4>
            <p class="text-2xl font-bold text-light-1000 dark:text-dark-1000 mt-2">{{ formatPrice(p.price) }}</p>
            <p class="text-xs text-light-800 dark:text-dark-700 mb-4">/ month</p>
            <ul class="space-y-2 text-xs text-light-900 dark:text-dark-800 flex-1">
              <li v-for="(feat, i) in p.features" :key="i" class="flex items-start gap-2">
                <svg class="w-3.5 h-3.5 mt-0.5 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                {{ feat }}
              </li>
            </ul>
            <Badge v-if="currentPlan?.name === p.name" variant="success" class="mt-4 self-start">Current</Badge>
            <Button
              v-else-if="p.price > 0"
              size="sm"
              class="mt-4"
              :loading="checkoutPlan === p.name"
              @click="handleUpgrade(p.name)"
            >
              Upgrade
            </Button>
          </div>
        </div>

        <!-- Payment Info -->
        <h3 class="text-sm font-bold text-light-1000 dark:text-dark-1000 mb-4">Payment</h3>
        <div class="rounded-lg border border-light-300 dark:border-dark-400 p-4 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
              <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M21 5H3a1 1 0 00-1 1v12a1 1 0 001 1h18a1 1 0 001-1V6a1 1 0 00-1-1zm-1 12H4V7h16v10zM6 9h4v2H6V9zm0 4h6v2H6v-2zm10-4h2v6h-2V9z"/></svg>
            </div>
            <div>
              <p class="text-sm text-light-1000 dark:text-dark-1000">
                {{ billing?.plan !== 'free' ? 'Midtrans' : 'No payment method on file' }}
              </p>
              <p class="text-xs text-light-800 dark:text-dark-700">
                {{ billing?.plan !== 'free' ? 'Monthly billing via Midtrans' : 'Your Free plan does not require a payment method.' }}
              </p>
            </div>
          </div>
          <Button v-if="billing?.plan !== 'free'" variant="secondary" size="sm" disabled>Manage</Button>
          <Button v-else variant="secondary" size="sm" disabled>Add</Button>
        </div>
      </template>
    </div>
  </SettingsLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useWorkspaceStore } from '@/stores/workspace'
import { useRequireAdmin } from '@/composables/useRequireAdmin'
import { billingService, type BillingData } from '@/services/billing.service'
import { planService, type Plan } from '@/services/plan.service'
import SettingsLayout from '@/components/layout/SettingsLayout.vue'
import Button from '@/components/ui/Button.vue'
import Badge from '@/components/ui/Badge.vue'

const route = useRoute()
const workspaceStore = useWorkspaceStore()

useRequireAdmin()

const loading = ref(true)
const checkoutPlan = ref<string | null>(null)
const billing = ref<BillingData | null>(null)
const plans = ref<Plan[]>([])

const workspace = computed(() => workspaceStore.currentWorkspace)
const slug = computed(() => workspace.value?.slug || (route.params.workspaceSlug as string) || '')

const currentPlan = computed(() => {
  return plans.value.find(p => p.name === billing.value?.plan) || null
})

const usage = computed(() => billing.value?.usage || { boards: 0, members: 0, storageBytes: 0 })

const workspaceUsageCount = computed(() => billing.value?.workspaceUsage?.count || 0)
const workspaceLimitLabel = computed(() => {
  const limit = billing.value?.workspaceUsage?.limit ?? 3
  return limit >= 999 ? 'Unlimited' : String(limit)
})
const workspacePercent = computed(() => {
  const limit = billing.value?.workspaceUsage?.limit ?? 3
  if (limit >= 999) return 10
  return Math.min((workspaceUsageCount.value / limit) * 100, 100)
})

function formatPrice(price: number) {
  if (price === 0) return 'Rp 0'
  return 'Rp ' + price.toLocaleString('id-ID')
}

function formatDate(dateStr: string) {
  return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
}

function formatLimit(limit: number) {
  return limit >= 999 ? 'Unlimited' : String(limit)
}

const memberLimitLabel = computed(() => formatLimit(currentPlan.value?.memberLimit ?? 3))

const memberPercent = computed(() => {
  const limit = currentPlan.value?.memberLimit ?? 3
  if (limit >= 999) return 10
  return Math.min((usage.value.members / limit) * 100, 100)
})

async function loadData() {
  if (!slug.value) return
  loading.value = true
  try {
    const [billingRes, plansRes] = await Promise.all([
      billingService.getByWorkspace(slug.value),
      planService.getAll(),
    ])
    billing.value = billingRes.data
    plans.value = plansRes.data
  } catch (error) {
    console.error('Failed to load billing:', error)
  } finally {
    loading.value = false
  }
}

declare global {
  interface Window {
    snap: {
      pay: (token: string, options?: {
        onSuccess?: (result: any) => void
        onPending?: (result: any) => void
        onError?: (error: any) => void
        onClose?: () => void
      }) => void
    }
  }
}

async function handleUpgrade(planName: string) {
  if (!slug.value) return
  checkoutPlan.value = planName
  try {
    const res = await billingService.createCheckout(slug.value, planName)
    const { token } = res.data

    window.snap.pay(token, {
      onSuccess: () => loadData(),
      onPending: () => loadData(),
      onError: () => loadData(),
      onClose: () => loadData(),
    })
  } catch (error) {
    console.error('Failed to create checkout:', error)
  } finally {
    checkoutPlan.value = null
  }
}

onMounted(() => {
  loadData()
})
</script>
