<template>
  <div class="min-h-screen bg-light-50 dark:bg-dark-50">
    <nav class="sticky top-0 z-50 bg-light-50/80 dark:bg-dark-50/80 backdrop-blur-lg border-b border-light-300 dark:border-dark-400">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-14">
          <router-link to="/" class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-light-1000 dark:bg-dark-1000 flex items-center justify-center">
              <span class="text-xs font-bold text-light-50 dark:text-dark-50">M</span>
            </div>
            <span class="text-sm font-bold text-light-1000 dark:text-dark-1000">ManagPro</span>
          </router-link>
          <router-link to="/" class="text-xs text-light-800 dark:text-dark-800 hover:text-light-1000 dark:hover:text-dark-1000 transition-colors">
            ← Back to Home
          </router-link>
        </div>
      </div>
    </nav>
    <div class="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
      <h1 class="text-3xl font-bold text-light-1000 dark:text-dark-1000 mb-2">Privacy Policy</h1>
      <p class="text-sm text-light-700 dark:text-dark-700 mb-8">Last updated: {{ lastUpdated }}</p>

      <div class="prose prose-sm dark:prose-invert max-w-none space-y-8">
        <section>
          <h2 class="text-lg font-semibold text-light-1000 dark:text-dark-1000 mb-3">1. Introduction</h2>
          <p class="text-sm text-light-900 dark:text-dark-900 leading-relaxed">
            Welcome to ManagPro ("we", "our", "us"). We are committed to protecting your personal information and your right to privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our project management platform and related services (collectively, the "Service").
          </p>
          <p class="text-sm text-light-900 dark:text-dark-900 leading-relaxed mt-2">
            By using the Service, you agree to the collection and use of information in accordance with this policy. If you do not agree, please discontinue use of the Service.
          </p>
        </section>

        <section>
          <h2 class="text-lg font-semibold text-light-1000 dark:text-dark-1000 mb-3">2. Information We Collect</h2>
          <h3 class="text-base font-medium text-light-1000 dark:text-dark-1000 mb-2">2.1 Information You Provide</h3>
          <ul class="list-disc list-inside text-sm text-light-900 dark:text-dark-900 leading-relaxed space-y-1">
            <li>Account information: name, email address, and password</li>
            <li>Profile information: avatar/image</li>
            <li>Workspace data: workspace names, board content, cards, comments, and attachments</li>
            <li>Payment information: processed securely through Midtrans; we do not store card numbers</li>
            <li>Communication: messages sent through the Service or correspondence with us</li>
          </ul>

          <h3 class="text-base font-medium text-light-1000 dark:text-dark-1000 mb-2 mt-4">2.2 Information Collected Automatically</h3>
          <ul class="list-disc list-inside text-sm text-light-900 dark:text-dark-900 leading-relaxed space-y-1">
            <li>Usage data: pages viewed, features used, actions taken, and timestamps</li>
            <li>Device data: browser type, operating system, device type, and screen resolution</li>
            <li>Log data: IP address, access times, and error logs</li>
          </ul>

          <h3 class="text-base font-medium text-light-1000 dark:text-dark-1000 mb-2 mt-4">2.3 Information from Third Parties</h3>
          <ul class="list-disc list-inside text-sm text-light-900 dark:text-dark-900 leading-relaxed space-y-1">
            <li>OAuth providers (Google, GitHub): name, email, and profile image when you sign in with these services</li>
          </ul>
        </section>

        <section>
          <h2 class="text-lg font-semibold text-light-1000 dark:text-dark-1000 mb-3">3. How We Use Your Information</h2>
          <ul class="list-disc list-inside text-sm text-light-900 dark:text-dark-900 leading-relaxed space-y-1">
            <li>To provide, maintain, and improve the Service</li>
            <li>To process transactions and manage subscriptions</li>
            <li>To send transactional emails (verification, password reset, notifications)</li>
            <li>To detect and prevent fraud, abuse, and security incidents</li>
            <li>To comply with legal obligations</li>
            <li>To communicate with you about updates, features, and support</li>
          </ul>
        </section>

        <section>
          <h2 class="text-lg font-semibold text-light-1000 dark:text-dark-1000 mb-3">4. How We Share Your Information</h2>
          <p class="text-sm text-light-900 dark:text-dark-900 leading-relaxed">
            We do not sell your personal information. We may share your information with:
          </p>
          <ul class="list-disc list-inside text-sm text-light-900 dark:text-dark-900 leading-relaxed space-y-1 mt-2">
            <li><strong>Workspace members:</strong> Your name and email are visible to other members of workspaces you belong to</li>
            <li><strong>Service providers:</strong> Third parties that help us operate the Service (hosting, email delivery, payment processing)</li>
            <li><strong>Legal compliance:</strong> When required by law, regulation, or legal process</li>
            <li><strong>Business transfers:</strong> In connection with a merger, acquisition, or sale of assets</li>
          </ul>
        </section>

        <section>
          <h2 class="text-lg font-semibold text-light-1000 dark:text-dark-1000 mb-3">5. Data Security</h2>
          <p class="text-sm text-light-900 dark:text-dark-900 leading-relaxed">
            We implement industry-standard security measures including encryption in transit (TLS/HTTPS), secure password hashing, and access controls. However, no method of transmission or storage is 100% secure, and we cannot guarantee absolute security.
          </p>
        </section>

        <section>
          <h2 class="text-lg font-semibold text-light-1000 dark:text-dark-1000 mb-3">6. Data Retention</h2>
          <p class="text-sm text-light-900 dark:text-dark-900 leading-relaxed">
            We retain your information for as long as your account is active or as needed to provide the Service. When you delete your account, we will remove your personal data within 30 days, except where required by law or for legitimate business purposes.
          </p>
        </section>

        <section>
          <h2 class="text-lg font-semibold text-light-1000 dark:text-dark-1000 mb-3">7. Your Rights</h2>
          <p class="text-sm text-light-900 dark:text-dark-900 leading-relaxed">You have the right to:</p>
          <ul class="list-disc list-inside text-sm text-light-900 dark:text-dark-900 leading-relaxed space-y-1 mt-2">
            <li>Access, correct, or delete your personal information</li>
            <li>Export your data in a portable format</li>
            <li>Opt out of non-essential communications</li>
            <li>Revoke OAuth account connections</li>
          </ul>
          <p class="text-sm text-light-900 dark:text-dark-900 leading-relaxed mt-2">
            To exercise these rights, contact us at the email address below.
          </p>
        </section>

        <section>
          <h2 class="text-lg font-semibold text-light-1000 dark:text-dark-1000 mb-3">8. Cookies</h2>
          <p class="text-sm text-light-900 dark:text-dark-900 leading-relaxed">
            We use essential cookies to maintain your session and authentication. These cookies are necessary for the Service to function and cannot be disabled. We do not use tracking or advertising cookies.
          </p>
        </section>

        <section>
          <h2 class="text-lg font-semibold text-light-1000 dark:text-dark-1000 mb-3">9. Children's Privacy</h2>
          <p class="text-sm text-light-900 dark:text-dark-900 leading-relaxed">
            The Service is not intended for children under 13. We do not knowingly collect personal information from children. If you become aware that a child has provided us with personal data, please contact us.
          </p>
        </section>

        <section>
          <h2 class="text-lg font-semibold text-light-1000 dark:text-dark-1000 mb-3">10. Changes to This Policy</h2>
          <p class="text-sm text-light-900 dark:text-dark-900 leading-relaxed">
            We may update this Privacy Policy from time to time. We will notify you of significant changes by posting the new policy on this page and updating the "Last updated" date.
          </p>
        </section>

        <section>
          <h2 class="text-lg font-semibold text-light-1000 dark:text-dark-1000 mb-3">11. Contact Us</h2>
          <p class="text-sm text-light-900 dark:text-dark-900 leading-relaxed">
            If you have questions about this Privacy Policy, please contact us at <strong>privacy@managpro.com</strong>.
          </p>
        </section>
      </div>
    </div>
    <footer class="border-t border-light-300 dark:border-dark-400 py-8 px-4 sm:px-6 lg:px-8">
      <div class="max-w-3xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2">
          <div class="w-6 h-6 rounded-md bg-light-1000 dark:bg-dark-1000 flex items-center justify-center">
            <span class="text-[10px] font-bold text-light-50 dark:text-dark-50">M</span>
          </div>
          <span class="text-xs font-semibold text-light-1000 dark:text-dark-1000">ManagPro</span>
        </div>
        <p class="text-xs text-light-700 dark:text-dark-700">&copy; {{ currentYear }} ManagPro. All rights reserved.</p>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const lastUpdated = computed(() => {
  return new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
})
const currentYear = computed(() => new Date().getFullYear())
</script>
