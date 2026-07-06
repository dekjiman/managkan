<template>
  <div class="min-h-screen bg-light-50 dark:bg-dark-50">
    <!-- Navbar -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-light-50/80 dark:bg-dark-50/80 backdrop-blur-lg border-b border-light-300 dark:border-dark-400">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-light-1000 dark:bg-dark-1000 flex items-center justify-center">
              <span class="text-sm font-bold text-light-50 dark:text-dark-50">M</span>
            </div>
            <span class="text-lg font-bold text-light-1000 dark:text-dark-1000">ManagPro</span>
          </div>
          <div class="hidden md:flex items-center gap-8">
            <a href="#features" class="text-sm text-light-800 dark:text-dark-800 hover:text-light-1000 dark:hover:text-dark-1000 transition-colors">Features</a>
            <a href="#pricing" class="text-sm text-light-800 dark:text-dark-800 hover:text-light-1000 dark:hover:text-dark-1000 transition-colors">Pricing</a>
          </div>
          <div class="flex items-center gap-3">
            <router-link to="/login" class="text-sm text-light-900 dark:text-dark-900 hover:text-light-1000 dark:hover:text-dark-1000 transition-colors">
              Sign in
            </router-link>
            <router-link to="/register" class="inline-flex items-center justify-center h-9 px-4 rounded-lg bg-light-1000 dark:bg-dark-1000 text-sm font-medium text-light-50 dark:text-dark-50 hover:opacity-90 transition-opacity">
              Get started
            </router-link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Hero -->
    <section class="pt-32 pb-20 px-4 sm:px-6 lg:px-8">
      <div class="max-w-4xl mx-auto text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 text-xs font-medium mb-6 animate-fade-down">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
          Trusted by teams worldwide
        </div>
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-light-1000 dark:text-dark-1000 tracking-tight animate-fade-down">
          Manage projects<br class="hidden sm:block" />
          <span class="text-primary-600 dark:text-primary-400">with your team</span>
        </h1>
        <p class="mt-6 text-lg text-light-800 dark:text-dark-800 max-w-2xl mx-auto animate-fade-in">
          Kanban boards, team collaboration, billing, and notifications — everything you need to ship faster, in one place.
        </p>
        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4 animate-fade-in">
          <router-link to="/register" class="w-full sm:w-auto inline-flex items-center justify-center h-11 px-6 rounded-lg bg-light-1000 dark:bg-dark-1000 text-sm font-medium text-light-50 dark:text-dark-50 hover:opacity-90 transition-opacity">
            Get started free
            <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
            </svg>
          </router-link>
          <router-link to="/login" class="w-full sm:w-auto inline-flex items-center justify-center h-11 px-6 rounded-lg border border-light-300 dark:border-dark-400 text-sm font-medium text-light-900 dark:text-dark-900 hover:bg-light-200 dark:hover:bg-dark-200 transition-colors">
            Sign in
          </router-link>
        </div>

        <!-- Hero Visual -->
        <div class="mt-16 mx-auto max-w-4xl animate-fade-in">
          <div class="rounded-xl border border-light-300 dark:border-dark-400 bg-light-100 dark:bg-dark-100 p-2 shadow-3xl-light dark:shadow-3xl-dark">
            <div class="rounded-lg bg-light-50 dark:bg-dark-200 p-6">
              <div class="flex gap-4">
                <div v-for="col in 3" :key="col" class="flex-1 space-y-3">
                  <div class="h-4 w-24 rounded bg-light-300 dark:bg-dark-400" />
                  <div v-for="card in (col === 1 ? 3 : col === 2 ? 2 : 1)" :key="card" class="rounded-lg border border-light-300 dark:border-dark-400 bg-light-50 dark:bg-dark-200 p-3 space-y-2">
                    <div class="h-3 w-3/4 rounded bg-light-300 dark:bg-dark-400" />
                    <div class="h-2 w-1/2 rounded bg-light-200 dark:bg-dark-300" />
                    <div class="flex gap-1 mt-2">
                      <div class="h-4 w-4 rounded-full bg-primary-200 dark:bg-primary-800" />
                      <div class="h-4 w-4 rounded-full bg-green-200 dark:bg-green-800" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Logos -->
    <section class="py-12 border-y border-light-300 dark:border-dark-400">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-center text-xs text-light-700 dark:text-dark-700 mb-8">Trusted by innovative companies</p>
        <div class="flex items-center justify-center gap-12 opacity-40">
          <div v-for="i in 5" :key="i" class="h-6 w-20 rounded bg-light-400 dark:bg-dark-400" />
        </div>
      </div>
    </section>

    <!-- Features -->
    <section id="features" class="py-20 px-4 sm:px-6 lg:px-8">
      <div class="max-w-6xl mx-auto">
        <div class="text-center mb-16">
          <h2 class="text-3xl font-bold text-light-1000 dark:text-dark-1000">Everything you need</h2>
          <p class="mt-3 text-light-800 dark:text-dark-800 max-w-lg mx-auto">
            Powerful features to help your team collaborate and ship faster.
          </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div v-for="feature in features" :key="feature.title" class="rounded-xl border border-light-300 dark:border-dark-400 p-6 hover:shadow-lg transition-shadow">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center mb-4" :class="feature.iconBg">
              <svg class="w-5 h-5" :class="feature.iconColor" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" :d="feature.icon" />
              </svg>
            </div>
            <h3 class="text-sm font-semibold text-light-1000 dark:text-dark-1000 mb-2">{{ feature.title }}</h3>
            <p class="text-xs text-light-800 dark:text-dark-800 leading-relaxed">{{ feature.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Pricing -->
    <section id="pricing" class="py-20 px-4 sm:px-6 lg:px-8 bg-light-100 dark:bg-dark-100">
      <div class="max-w-6xl mx-auto">
        <div class="text-center mb-16">
          <h2 class="text-3xl font-bold text-light-1000 dark:text-dark-1000">Simple, transparent pricing</h2>
          <p class="mt-3 text-light-800 dark:text-dark-800 max-w-lg mx-auto">
            Start free, upgrade when you need more. No hidden fees.
          </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
          <div
            v-for="p in plans"
            :key="p.name"
            class="rounded-xl border p-6 flex flex-col relative"
            :class="p.name === 'team'
              ? 'border-2 border-light-1000 dark:border-dark-1000 shadow-lg'
              : 'border-light-300 dark:border-dark-400'"
          >
            <div v-if="p.name === 'team'" class="absolute -top-3 left-6 px-3 py-0.5 rounded-full bg-light-1000 dark:bg-dark-1000 text-[10px] font-semibold text-light-50 dark:text-dark-50">
              POPULAR
            </div>
            <h3 class="text-sm font-semibold text-light-1000 dark:text-dark-1000">{{ p.displayName }}</h3>
            <p class="mt-4 text-3xl font-bold text-light-1000 dark:text-dark-1000">
              {{ p.price === 0 ? 'Free' : `Rp ${p.price.toLocaleString('id-ID')}` }}
            </p>
            <p class="text-xs text-light-800 dark:text-dark-800 mt-1">{{ p.price > 0 ? '/month' : 'forever' }}</p>
            <ul class="mt-6 space-y-3 flex-1">
              <li v-for="(feat, i) in p.features" :key="i" class="flex items-start gap-2 text-xs text-light-900 dark:text-dark-900">
                <svg class="w-4 h-4 text-green-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                {{ feat }}
              </li>
            </ul>
            <router-link
              to="/register"
              class="mt-6 w-full inline-flex items-center justify-center h-10 rounded-lg text-sm font-medium transition-colors"
              :class="p.name === 'team'
                ? 'bg-light-1000 dark:bg-dark-1000 text-light-50 dark:text-dark-50 hover:opacity-90'
                : 'border border-light-300 dark:border-dark-400 text-light-900 dark:text-dark-900 hover:bg-light-200 dark:hover:bg-dark-300'"
            >
              Get started
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <section class="py-20 px-4 sm:px-6 lg:px-8">
      <div class="max-w-3xl mx-auto text-center">
        <h2 class="text-3xl font-bold text-light-1000 dark:text-dark-1000">Ready to get started?</h2>
        <p class="mt-4 text-light-800 dark:text-dark-800">
          Join teams already using ManagPro to manage their projects.
        </p>
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
          <router-link to="/register" class="w-full sm:w-auto inline-flex items-center justify-center h-11 px-6 rounded-lg bg-light-1000 dark:bg-dark-1000 text-sm font-medium text-light-50 dark:text-dark-50 hover:opacity-90 transition-opacity">
            Start for free
            <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
            </svg>
          </router-link>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-light-300 dark:border-dark-400 py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-6xl mx-auto">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-8">
          <div class="col-span-2 md:col-span-1">
            <div class="flex items-center gap-2 mb-4">
              <div class="w-7 h-7 rounded-lg bg-light-1000 dark:bg-dark-1000 flex items-center justify-center">
                <span class="text-xs font-bold text-light-50 dark:text-dark-50">M</span>
              </div>
              <span class="text-sm font-bold text-light-1000 dark:text-dark-1000">ManagPro</span>
            </div>
            <p class="text-xs text-light-800 dark:text-dark-800 leading-relaxed">
              Project management for modern teams.
            </p>
          </div>
          <div>
            <h4 class="text-xs font-semibold text-light-1000 dark:text-dark-1000 mb-3">Product</h4>
            <ul class="space-y-2">
              <li><a href="#features" class="text-xs text-light-800 dark:text-dark-800 hover:text-light-1000 dark:hover:text-dark-1000 transition-colors">Features</a></li>
              <li><a href="#pricing" class="text-xs text-light-800 dark:text-dark-800 hover:text-light-1000 dark:hover:text-dark-1000 transition-colors">Pricing</a></li>
              <li><router-link to="/register" class="text-xs text-light-800 dark:text-dark-800 hover:text-light-1000 dark:hover:text-dark-1000 transition-colors">Get started</router-link></li>
            </ul>
          </div>
          <div>
            <h4 class="text-xs font-semibold text-light-1000 dark:text-dark-1000 mb-3">Account</h4>
            <ul class="space-y-2">
              <li><router-link to="/login" class="text-xs text-light-800 dark:text-dark-800 hover:text-light-1000 dark:hover:text-dark-1000 transition-colors">Sign in</router-link></li>
              <li><router-link to="/register" class="text-xs text-light-800 dark:text-dark-800 hover:text-light-1000 dark:hover:text-dark-1000 transition-colors">Sign up</router-link></li>
              <li><router-link to="/forgot-password" class="text-xs text-light-800 dark:text-dark-800 hover:text-light-1000 dark:hover:text-dark-1000 transition-colors">Reset password</router-link></li>
            </ul>
          </div>
          <div>
            <h4 class="text-xs font-semibold text-light-1000 dark:text-dark-1000 mb-3">Legal</h4>
            <ul class="space-y-2">
              <li><router-link to="/privacy-policy" class="text-xs text-light-800 dark:text-dark-800 hover:text-light-1000 dark:hover:text-dark-1000 transition-colors">Privacy Policy</router-link></li>
              <li><router-link to="/terms-of-service" class="text-xs text-light-800 dark:text-dark-800 hover:text-light-1000 dark:hover:text-dark-1000 transition-colors">Terms of Service</router-link></li>
            </ul>
          </div>
        </div>
        <div class="pt-8 border-t border-light-300 dark:border-dark-400 flex flex-col sm:flex-row items-center justify-between gap-4">
          <p class="text-xs text-light-700 dark:text-dark-700">&copy; {{ currentYear }} ManagPro. All rights reserved.</p>
          <div class="flex items-center gap-4">
            <a href="#" class="text-light-700 dark:text-dark-700 hover:text-light-1000 dark:hover:text-dark-1000 transition-colors">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
            </a>
            <a href="#" class="text-light-700 dark:text-dark-700 hover:text-light-1000 dark:hover:text-dark-1000 transition-colors">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
            </a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { planService, type Plan } from '@/services/plan.service'

const currentYear = computed(() => new Date().getFullYear())
const plans = ref<Plan[]>([])

const features = [
  {
    title: 'Kanban Boards',
    desc: 'Visual task management with drag-and-drop boards, lists, and cards.',
    icon: 'M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2',
    iconBg: 'bg-primary-100 dark:bg-primary-900/30',
    iconColor: 'text-primary-600 dark:text-primary-400',
  },
  {
    title: 'Team Collaboration',
    desc: 'Invite members, assign roles, and collaborate in real-time.',
    icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
    iconBg: 'bg-green-100 dark:bg-green-900/30',
    iconColor: 'text-green-600 dark:text-green-400',
  },
  {
    title: 'Billing & Plans',
    desc: 'Flexible plans with Midtrans payment integration.',
    icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
    iconBg: 'bg-blue-100 dark:bg-blue-900/30',
    iconColor: 'text-blue-600 dark:text-blue-400',
  },
  {
    title: 'Notifications',
    desc: 'Stay informed with real-time notifications for comments and updates.',
    icon: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
    iconBg: 'bg-yellow-100 dark:bg-yellow-900/30',
    iconColor: 'text-yellow-600 dark:text-yellow-400',
  },
]

onMounted(async () => {
  try {
    const res = await planService.getAll()
    plans.value = res.data || []
  } catch {
    // fallback
  }
})
</script>
