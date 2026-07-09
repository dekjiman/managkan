import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/',
      name: 'landing',
      component: () => import('@/views/landing/LandingView.vue'),
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/auth/LoginView.vue'),
      meta: { guest: true }
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/views/auth/RegisterView.vue'),
      meta: { guest: true }
    },
    {
      path: '/verify-email-notice',
      name: 'verify-email-notice',
      component: () => import('@/views/auth/VerifyEmailNoticeView.vue'),
    },
    {
      path: '/verify-email',
      name: 'verify-email',
      component: () => import('@/views/auth/VerifyEmailView.vue'),
    },
    {
      path: '/invite/:code',
      name: 'invite-signup',
      component: () => import('@/views/auth/InviteSignupView.vue'),
      meta: { guest: true }
    },
    {
      path: '/forgot-password',
      name: 'forgot-password',
      component: () => import('@/views/auth/ForgotPasswordView.vue'),
      meta: { guest: true }
    },
    {
      path: '/reset-password',
      name: 'reset-password',
      component: () => import('@/views/auth/ResetPasswordView.vue'),
      meta: { guest: true }
    },
    {
      path: '/privacy-policy',
      name: 'privacy-policy',
      component: () => import('@/views/legal/PrivacyPolicyView.vue'),
    },
    {
      path: '/terms-of-service',
      name: 'terms-of-service',
      component: () => import('@/views/legal/TermsOfServiceView.vue'),
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('@/views/dashboard/DashboardView.vue'),
      meta: { auth: true }
    },
    {
      path: '/settings/account',
      name: 'account-settings',
      component: () => import('@/views/settings/AccountSettingsView.vue'),
      meta: { auth: true }
    },
    {
      path: '/settings',
      name: 'settings',
      component: () => import('@/views/settings/SettingsIndex.vue'),
      meta: { auth: true }
    },
    {
      path: '/:workspaceSlug',
      component: () => import('@/views/workspace/WorkspaceView.vue'),
      meta: { auth: true },
      children: [
        {
          path: '',
          name: 'workspace',
          component: () => import('@/views/workspace/BoardListView.vue')
        },
        {
          path: 'settings',
          name: 'workspace-settings',
          component: () => import('@/views/workspace/WorkspaceSettingsView.vue')
        },
        {
          path: 'settings/permissions',
          name: 'workspace-permissions',
          component: () => import('@/views/settings/PermissionsView.vue')
        },
        {
          path: 'settings/billing',
          name: 'workspace-billing',
          component: () => import('@/views/settings/BillingView.vue')
        },
        {
          path: 'settings/api',
          name: 'workspace-api',
          component: () => import('@/views/settings/ApiSettingsView.vue')
        },
        {
          path: 'settings/webhooks',
          name: 'workspace-webhooks',
          component: () => import('@/views/settings/WebhooksView.vue')
        },
        {
          path: 'settings/integrations',
          name: 'workspace-integrations',
          component: () => import('@/views/settings/IntegrationsView.vue')
        },
        {
          path: 'members',
          name: 'workspace-members',
          component: () => import('@/views/workspace/MembersView.vue')
        },
        {
          path: 'templates',
          name: 'workspace-templates',
          component: () => import('@/views/workspace/TemplatesView.vue')
        },
        {
          path: 'templates/:slug',
          name: 'workspace-template-detail',
          component: () => import('@/views/workspace/TemplateDetailView.vue')
        },
        {
          path: 'notifications',
          name: 'workspace-notifications',
          component: () => import('@/views/notifications/NotificationsView.vue')
        },
        {
          path: ':boardSlug',
          name: 'board',
          component: () => import('@/views/board/BoardDetailView.vue')
        },
        {
          path: ':boardSlug/cards/:cardPublicId',
          name: 'card-detail',
          component: () => import('@/views/board/CardDetailView.vue')
        }
      ]
    },
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: () => import('@/views/NotFoundView.vue'),
    }
  ]
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  // Handle OAuth redirect: save token from URL params
  const accessToken = to.query.accessToken as string | undefined
  if (accessToken) {
    localStorage.setItem('accessToken', accessToken)
    const refreshToken = to.query.refreshToken as string
    if (refreshToken) localStorage.setItem('refreshToken', refreshToken)
    const userId = to.query.userId as string
    if (userId) localStorage.setItem('userId', userId)
    return next({ path: to.path, replace: true })
  }

  if (to.path === '/' && to.query.redirect) {
    const redirect = to.query.redirect as string
    if (redirect.startsWith('/') && !redirect.startsWith('//')) {
      return next({ path: redirect, replace: true })
    }
    return next('/dashboard')
  }

  if (to.meta.auth && !authStore.isAuthenticated) {
    await authStore.fetchSession()
    if (!authStore.isAuthenticated) {
      return next({ path: '/login', query: { redirect: to.fullPath } })
    }
  }

  if (to.meta.guest && authStore.isAuthenticated) {
    return next('/dashboard')
  }

  if (to.name === 'landing' && authStore.isAuthenticated && to.query.from !== 'sidebar') {
    return next('/dashboard')
  }

  next()
})

export default router
