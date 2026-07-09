<template>
  <AppLayout>
    <div class="max-w-6xl mx-auto">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-xl font-semibold text-light-1000 dark:text-dark-1000">Dashboard</h1>
          <p class="text-sm text-light-800 dark:text-dark-700 mt-0.5">Welcome back, {{ authStore.user?.name }}</p>
        </div>
        <select
          v-model="selectedWorkspace"
          @change="fetchDashboard"
          class="text-sm border border-light-300 dark:border-dark-400 rounded-lg px-3 py-1.5 bg-light-50 dark:bg-dark-200 text-light-1000 dark:text-dark-1000 focus:outline-none focus:ring-2 focus:ring-primary-500"
        >
          <option value="">All Workspaces</option>
          <option v-for="ws in workspaces" :key="ws.publicId" :value="ws.publicId">{{ ws.name }}</option>
        </select>
      </div>

      <!-- Your Workspaces -->
      <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-sm font-semibold text-light-1000 dark:text-dark-1000">Your Workspaces</h2>
          <Button @click="showCreateModal = true" size="sm">
            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            New Workspace
          </Button>
        </div>
        <div v-if="workspaces.length === 0" class="text-center py-10">
          <p class="text-sm text-light-800 dark:text-dark-700">No workspaces yet</p>
        </div>
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
          <router-link
            v-for="ws in workspaces"
            :key="ws.publicId"
            :to="`/${ws.slug}`"
            class="group card p-4 hover:shadow-md hover:border-light-400 dark:hover:border-dark-500 transition-all duration-150"
          >
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-lg bg-primary-50 dark:bg-primary-100 flex items-center justify-center shrink-0 group-hover:bg-primary-100 dark:group-hover:bg-primary-200 transition-colors">
                <span class="text-primary-600 dark:text-primary-700 font-semibold text-sm">{{ ws.name.charAt(0).toUpperCase() }}</span>
              </div>
              <div class="min-w-0">
                <h3 class="font-medium text-sm text-light-1000 dark:text-dark-1000 truncate">{{ ws.name }}</h3>
                <p class="text-xs text-light-800 dark:text-dark-700 truncate">/{{ ws.slug }}</p>
              </div>
            </div>
            <p v-if="ws.description" class="mt-3 text-xs text-light-800 dark:text-dark-700 line-clamp-2 leading-relaxed">
              {{ ws.description }}
            </p>
          </router-link>
        </div>
      </div>

      <div v-if="isLoading" class="flex justify-center py-16">
        <LoadingSpinner />
      </div>

      <div v-else-if="error" class="text-center py-16">
        <p class="text-sm text-red-600 dark:text-red-400">{{ error }}</p>
        <Button @click="fetchDashboard" variant="secondary" size="sm" class="mt-3">Retry</Button>
      </div>

      <template v-else-if="dashboard">
        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
          <div
            v-for="stat in summaryStats"
            :key="stat.label"
            class="rounded-xl border border-light-300 dark:border-dark-400 p-4 bg-light-50 dark:bg-dark-100"
          >
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="stat.bg">
                <svg class="w-5 h-5" :class="stat.color" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" :d="stat.icon" />
                </svg>
              </div>
              <div>
                <p class="text-2xl font-bold text-light-1000 dark:text-dark-1000">{{ stat.value }}</p>
                <p class="text-xs text-light-800 dark:text-dark-700">{{ stat.label }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Overdue & Due Soon Alert -->
        <div v-if="dashboard.overdue.count > 0 || dashboard.dueSoon.count > 0" class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-6">
          <div v-if="dashboard.overdue.count > 0" class="rounded-xl border border-red-200 dark:border-red-900/40 bg-red-50 dark:bg-red-900/10 p-4">
            <div class="flex items-center gap-2 mb-2">
              <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
              <span class="text-sm font-semibold text-red-700 dark:text-red-400">{{ dashboard.overdue.count }} Overdue</span>
            </div>
            <div class="space-y-1.5">
              <router-link v-for="card in dashboard.overdue.cards.slice(0, 3)" :key="card.publicId" :to="`/${card.workspaceSlug}/${card.boardSlug}/cards/${card.publicId}`" class="block text-xs text-red-600 dark:text-red-400 hover:underline">
                {{ card.title }} <span class="text-red-400 dark:text-red-500">· {{ card.boardName }}</span>
              </router-link>
              <p v-if="dashboard.overdue.count > 3" class="text-xs text-red-400 dark:text-red-500">+{{ dashboard.overdue.count - 3 }} more</p>
            </div>
          </div>

          <div v-if="dashboard.dueSoon.count > 0" class="rounded-xl border border-yellow-200 dark:border-yellow-900/40 bg-yellow-50 dark:bg-yellow-900/10 p-4">
            <div class="flex items-center gap-2 mb-2">
              <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span class="text-sm font-semibold text-yellow-700 dark:text-yellow-400">{{ dashboard.dueSoon.count }} Due Soon</span>
            </div>
            <div class="space-y-1.5">
              <router-link v-for="card in dashboard.dueSoon.cards.slice(0, 3)" :key="card.publicId" :to="`/${card.workspaceSlug}/${card.boardSlug}/cards/${card.publicId}`" class="block text-xs text-yellow-600 dark:text-yellow-400 hover:underline">
                {{ card.title }} <span class="text-yellow-400 dark:text-yellow-500">· {{ card.boardName }}</span>
              </router-link>
              <p v-if="dashboard.dueSoon.count > 3" class="text-xs text-yellow-400 dark:text-yellow-500">+{{ dashboard.dueSoon.count - 3 }} more</p>
            </div>
          </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-6">
          <!-- Cards per List (Donut) -->
          <div class="rounded-xl border border-light-300 dark:border-dark-400 p-4 bg-light-50 dark:bg-dark-100">
            <h3 class="text-sm font-semibold text-light-1000 dark:text-dark-1000 mb-3">Cards per List</h3>
            <div v-if="dashboard.cardsPerList.length === 0" class="flex items-center justify-center h-48 text-xs text-light-700 dark:text-dark-700">
              No cards yet
            </div>
            <div v-else ref="donutChart" class="h-64" />
          </div>

          <!-- Cards Over Time (Line) -->
          <div class="rounded-xl border border-light-300 dark:border-dark-400 p-4 bg-light-50 dark:bg-dark-100">
            <h3 class="text-sm font-semibold text-light-1000 dark:text-dark-1000 mb-3">Cards Created (12 weeks)</h3>
            <div v-if="dashboard.cardsOverTime.length === 0" class="flex items-center justify-center h-48 text-xs text-light-700 dark:text-dark-700">
              No data yet
            </div>
            <div v-else ref="lineChart" class="h-64" />
          </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-6">
          <!-- User Activity (Bar) -->
          <div class="rounded-xl border border-light-300 dark:border-dark-400 p-4 bg-light-50 dark:bg-dark-100">
            <h3 class="text-sm font-semibold text-light-1000 dark:text-dark-1000 mb-3">User Activity (30 days)</h3>
            <div v-if="dashboard.userActivity.length === 0" class="flex items-center justify-center h-48 text-xs text-light-700 dark:text-dark-700">
              No activity yet
            </div>
            <div v-else ref="activityChart" class="h-64" />
          </div>

          <!-- Boards per Workspace (Bar) -->
          <div class="rounded-xl border border-light-300 dark:border-dark-400 p-4 bg-light-50 dark:bg-dark-100">
            <h3 class="text-sm font-semibold text-light-1000 dark:text-dark-1000 mb-3">Boards per Workspace</h3>
            <div v-if="dashboard.boardsPerWorkspace.length === 0" class="flex items-center justify-center h-48 text-xs text-light-700 dark:text-dark-700">
              No workspaces yet
            </div>
            <div v-else ref="boardsChart" class="h-64" />
          </div>
        </div>
      </template>
    </div>

    <Modal :is-open="showCreateModal" @close="showCreateModal = false">
      <template #header>Create Workspace</template>
      <form @submit.prevent="createWorkspace" class="space-y-4">
        <Input v-model="newWorkspace.name" label="Name" placeholder="My Workspace" required />
        <Input v-model="newWorkspace.description" label="Description (optional)" placeholder="Description" />
      </form>
      <template #footer>
        <div class="flex justify-end gap-2">
          <Button variant="secondary" @click="showCreateModal = false" size="sm">Cancel</Button>
          <Button @click="createWorkspace" :loading="isCreating" size="sm">Create</Button>
        </div>
      </template>
    </Modal>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import ApexCharts from 'apexcharts'
import { useAuthStore } from '@/stores/auth'
import { useWorkspaceStore } from '@/stores/workspace'
import { useToast } from '@/composables/useToast'
import { dashboardService, type DashboardData } from '@/services/dashboard.service'
import { workspaceService } from '@/services/workspace.service'
import AppLayout from '@/components/layout/AppLayout.vue'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'
import Modal from '@/components/ui/Modal.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'

const authStore = useAuthStore()
const workspaceStore = useWorkspaceStore()
const toast = useToast()

const dashboard = ref<DashboardData | null>(null)
const workspaces = ref<any[]>([])
const selectedWorkspace = ref('')
const isLoading = ref(true)
const error = ref('')
const showCreateModal = ref(false)
const isCreating = ref(false)
const newWorkspace = ref({ name: '', description: '' })

const donutChart = ref<HTMLElement | null>(null)
const lineChart = ref<HTMLElement | null>(null)
const activityChart = ref<HTMLElement | null>(null)
const boardsChart = ref<HTMLElement | null>(null)

let donutInstance: ApexCharts | null = null
let lineInstance: ApexCharts | null = null
let activityInstance: ApexCharts | null = null
let boardsInstance: ApexCharts | null = null

const summaryStats = ref<{ label: string; value: number; icon: string; bg: string; color: string }[]>([])

function getChartTheme() {
  const isDark = document.documentElement.classList.contains('dark')
  return {
    isDark,
    textColor: isDark ? '#707070' : '#565656',
    borderColor: isDark ? '#2e2e2e' : '#ececec',
    bgColor: isDark ? '#1c1c1c' : '#fafafa',
  }
}

const CHART_COLORS = ['#3b82f6', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899', '#f97316']

function destroyCharts() {
  donutInstance?.destroy()
  lineInstance?.destroy()
  activityInstance?.destroy()
  boardsInstance?.destroy()
  donutInstance = null
  lineInstance = null
  activityInstance = null
  boardsInstance = null
}

async function renderCharts() {
  await nextTick()
  if (!dashboard.value) return

  const theme = getChartTheme()

  // Donut - Cards per List
  if (donutChart.value && dashboard.value.cardsPerList.length > 0) {
    donutInstance = new ApexCharts(donutChart.value, {
      series: dashboard.value.cardsPerList.map(c => c.count),
      chart: { type: 'donut', height: 256, background: 'transparent', fontFamily: 'Inter, system-ui, sans-serif' },
      labels: dashboard.value.cardsPerList.map(c => c.name),
      colors: CHART_COLORS,
      plotOptions: { pie: { donut: { size: '65%' } } },
      stroke: { colors: [theme.bgColor] },
      theme: { mode: theme.isDark ? 'dark' : 'light' },
      dataLabels: { enabled: true, formatter: (val: number) => `${Math.round(val)}%` },
      legend: { position: 'bottom', fontSize: '11px', labels: { colors: theme.textColor } },
      tooltip: { theme: theme.isDark ? 'dark' : 'light' },
    })
    donutInstance.render()
  }

  // Line - Cards Over Time
  if (lineChart.value && dashboard.value.cardsOverTime.length > 0) {
    const dates = dashboard.value.cardsOverTime.map(d => d.date)
    const counts = dashboard.value.cardsOverTime.map(d => d.count)
    lineInstance = new ApexCharts(lineChart.value, {
      series: [{ name: 'Cards Created', data: counts }],
      chart: { type: 'area', height: 256, background: 'transparent', fontFamily: 'Inter, system-ui, sans-serif', toolbar: { show: false } },
      colors: ['#3b82f6'],
      stroke: { curve: 'smooth', width: 2 },
      fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.05 } },
      xaxis: { categories: dates, labels: { style: { fontSize: '10px', colors: theme.textColor }, rotate: -45 } },
      yaxis: { labels: { style: { fontSize: '10px', colors: theme.textColor } }, min: 0 },
      grid: { borderColor: theme.borderColor, strokeDashArray: 3 },
      theme: { mode: theme.isDark ? 'dark' : 'light' },
      tooltip: { theme: theme.isDark ? 'dark' : 'light' },
    })
    lineInstance.render()
  }

  // Bar - User Activity
  if (activityChart.value && dashboard.value.userActivity.length > 0) {
    activityInstance = new ApexCharts(activityChart.value, {
      series: [{ name: 'Actions', data: dashboard.value.userActivity.map(u => u.count) }],
      chart: { type: 'bar', height: 256, background: 'transparent', fontFamily: 'Inter, system-ui, sans-serif', toolbar: { show: false } },
      colors: ['#8b5cf6'],
      plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '60%' } },
      xaxis: { categories: dashboard.value.userActivity.map(u => u.name), labels: { style: { fontSize: '10px', colors: theme.textColor } } },
      yaxis: { labels: { style: { fontSize: '10px', colors: theme.textColor } } },
      grid: { borderColor: theme.borderColor, strokeDashArray: 3 },
      theme: { mode: theme.isDark ? 'dark' : 'light' },
      tooltip: { theme: theme.isDark ? 'dark' : 'light' },
    })
    activityInstance.render()
  }

  // Bar - Boards per Workspace
  if (boardsChart.value && dashboard.value.boardsPerWorkspace.length > 0) {
    boardsInstance = new ApexCharts(boardsChart.value, {
      series: [{ name: 'Boards', data: dashboard.value.boardsPerWorkspace.map(w => w.count) }],
      chart: { type: 'bar', height: 256, background: 'transparent', fontFamily: 'Inter, system-ui, sans-serif', toolbar: { show: false } },
      colors: ['#22c55e'],
      plotOptions: { bar: { borderRadius: 4, barHeight: '60%' } },
      xaxis: { categories: dashboard.value.boardsPerWorkspace.map(w => w.name), labels: { style: { fontSize: '10px', colors: theme.textColor }, rotate: -30 } },
      yaxis: { labels: { style: { fontSize: '10px', colors: theme.textColor } }, min: 0, forceNiceScale: true },
      grid: { borderColor: theme.borderColor, strokeDashArray: 3 },
      theme: { mode: theme.isDark ? 'dark' : 'light' },
      tooltip: { theme: theme.isDark ? 'dark' : 'light' },
    })
    boardsInstance.render()
  }
}

async function fetchDashboard() {
  isLoading.value = true
  error.value = ''
  destroyCharts()
  try {
    const res = await dashboardService.getData(selectedWorkspace.value || undefined)
    dashboard.value = res.data

    summaryStats.value = [
      { label: 'Workspaces', value: res.data.summary.workspaces, icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', bg: 'bg-blue-100 dark:bg-blue-900/30', color: 'text-blue-600 dark:text-blue-400' },
      { label: 'Boards', value: res.data.summary.boards, icon: 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2', bg: 'bg-green-100 dark:bg-green-900/30', color: 'text-green-600 dark:text-green-400' },
      { label: 'Cards', value: res.data.summary.cards, icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', bg: 'bg-purple-100 dark:bg-purple-900/30', color: 'text-purple-600 dark:text-purple-400' },
      { label: 'Members', value: res.data.summary.members, icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', bg: 'bg-amber-100 dark:bg-amber-900/30', color: 'text-amber-600 dark:text-amber-400' },
    ]

    await renderCharts()
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Failed to load dashboard'
  } finally {
    isLoading.value = false
  }
}

async function createWorkspace() {
  if (!newWorkspace.value.name) return
  isCreating.value = true
  try {
    await workspaceService.create(newWorkspace.value)
    await workspaceStore.fetchWorkspaces()
    workspaces.value = workspaceStore.workspaces
    showCreateModal.value = false
    newWorkspace.value = { name: '', description: '' }
    toast.success('Workspace created')
    fetchDashboard()
  } catch (e: any) {
    toast.error(e.response?.data?.message || 'Failed to create workspace')
  } finally {
    isCreating.value = false
  }
}

function handleThemeChange() {
  fetchDashboard()
}

onMounted(async () => {
  await workspaceStore.fetchWorkspaces()
  workspaces.value = workspaceStore.workspaces
  await fetchDashboard()

  const observer = new MutationObserver(handleThemeChange)
  observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] })
  onUnmounted(() => observer.disconnect())
})
</script>
