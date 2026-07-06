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
      <h1 class="text-3xl font-bold text-light-1000 dark:text-dark-1000 mb-2">Terms of Service</h1>
      <p class="text-sm text-light-700 dark:text-dark-700 mb-8">Last updated: {{ lastUpdated }}</p>

      <div class="prose prose-sm dark:prose-invert max-w-none space-y-8">
        <section>
          <h2 class="text-lg font-semibold text-light-1000 dark:text-dark-1000 mb-3">1. Acceptance of Terms</h2>
          <p class="text-sm text-light-900 dark:text-dark-900 leading-relaxed">
            By accessing or using ManagPro (the "Service"), you agree to be bound by these Terms of Service ("Terms"). If you do not agree to these Terms, do not use the Service. These Terms constitute a legally binding agreement between you and ManagPro.
          </p>
        </section>

        <section>
          <h2 class="text-lg font-semibold text-light-1000 dark:text-dark-1000 mb-3">2. Description of Service</h2>
          <p class="text-sm text-light-900 dark:text-dark-900 leading-relaxed">
            ManagPro is a project management platform that provides kanban boards, team collaboration tools, task tracking, and related features. The Service includes web-based interfaces, APIs, and associated services.
          </p>
        </section>

        <section>
          <h2 class="text-lg font-semibold text-light-1000 dark:text-dark-1000 mb-3">3. Account Registration</h2>
          <ul class="list-disc list-inside text-sm text-light-900 dark:text-dark-900 leading-relaxed space-y-1">
            <li>You must be at least 13 years old to create an account</li>
            <li>You must provide accurate and complete registration information</li>
            <li>You are responsible for maintaining the security of your account credentials</li>
            <li>You must notify us immediately of any unauthorized use of your account</li>
            <li>One person or entity may not maintain more than one free account</li>
          </ul>
        </section>

        <section>
          <h2 class="text-lg font-semibold text-light-1000 dark:text-dark-1000 mb-3">4. Subscriptions and Payment</h2>
          <ul class="list-disc list-inside text-sm text-light-900 dark:text-dark-900 leading-relaxed space-y-1">
            <li>The Service offers free and paid subscription plans</li>
            <li>Paid subscriptions are billed monthly in advance via Midtrans</li>
            <li>All fees are non-refundable except as required by applicable law</li>
            <li>We reserve the right to change pricing with 30 days' notice</li>
            <li>If payment fails, your subscription will be downgraded to the free plan</li>
            <li>You may cancel your subscription at any time; cancellation takes effect at the end of the current billing period</li>
          </ul>
        </section>

        <section>
          <h2 class="text-lg font-semibold text-light-1000 dark:text-dark-1000 mb-3">5. Your Content</h2>
          <p class="text-sm text-light-900 dark:text-dark-900 leading-relaxed">
            You retain ownership of all content you create, upload, or share through the Service ("Your Content"). By using the Service, you grant ManagPro a limited license to host, store, and display Your Content solely for the purpose of providing the Service to you.
          </p>
          <p class="text-sm text-light-900 dark:text-dark-900 leading-relaxed mt-2">
            You are solely responsible for Your Content. You represent that you have all necessary rights to share content through the Service.
          </p>
        </section>

        <section>
          <h2 class="text-lg font-semibold text-light-1000 dark:text-dark-1000 mb-3">6. Acceptable Use</h2>
          <p class="text-sm text-light-900 dark:text-dark-900 leading-relaxed">You agree not to:</p>
          <ul class="list-disc list-inside text-sm text-light-900 dark:text-dark-900 leading-relaxed space-y-1 mt-2">
            <li>Use the Service for any unlawful purpose or in violation of any applicable law</li>
            <li>Attempt to gain unauthorized access to the Service or other users' accounts</li>
            <li>Interfere with or disrupt the Service or servers</li>
            <li>Upload malware, viruses, or malicious code</li>
            <li>Scrape, crawl, or use automated tools to access the Service without permission</li>
            <li>Impersonate another person or entity</li>
            <li>Use the Service to send spam or unsolicited communications</li>
            <li>Reverse engineer, decompile, or disassemble any part of the Service</li>
          </ul>
        </section>

        <section>
          <h2 class="text-lg font-semibold text-light-1000 dark:text-dark-1000 mb-3">7. Intellectual Property</h2>
          <p class="text-sm text-light-900 dark:text-dark-900 leading-relaxed">
            The Service, including its original content, features, and design, is owned by ManagPro and protected by copyright, trademark, and other intellectual property laws. You may not copy, modify, distribute, or reverse-engineer any part of the Service.
          </p>
        </section>

        <section>
          <h2 class="text-lg font-semibold text-light-1000 dark:text-dark-1000 mb-3">8. Limitation of Liability</h2>
          <p class="text-sm text-light-900 dark:text-dark-900 leading-relaxed">
            To the maximum extent permitted by law, ManagPro shall not be liable for any indirect, incidental, special, consequential, or punitive damages, including but not limited to loss of profits, data, or business opportunities, arising from your use of the Service.
          </p>
          <p class="text-sm text-light-900 dark:text-dark-900 leading-relaxed mt-2">
            Our total liability for any claim arising from or related to the Service shall not exceed the amount you paid us in the 12 months preceding the claim.
          </p>
        </section>

        <section>
          <h2 class="text-lg font-semibold text-light-1000 dark:text-dark-1000 mb-3">9. Disclaimer of Warranties</h2>
          <p class="text-sm text-light-900 dark:text-dark-900 leading-relaxed">
            The Service is provided "as is" and "as available" without warranties of any kind, whether express or implied, including but not limited to implied warranties of merchantability, fitness for a particular purpose, and non-infringement. We do not warrant that the Service will be uninterrupted, error-free, or secure.
          </p>
        </section>

        <section>
          <h2 class="text-lg font-semibold text-light-1000 dark:text-dark-1000 mb-3">10. Termination</h2>
          <p class="text-sm text-light-900 dark:text-dark-900 leading-relaxed">
            We may suspend or terminate your access to the Service at any time, with or without cause, with or without notice. Upon termination, your right to use the Service ceases immediately. We may delete your data after termination, subject to our data retention policy.
          </p>
        </section>

        <section>
          <h2 class="text-lg font-semibold text-light-1000 dark:text-dark-1000 mb-3">11. Modifications to Terms</h2>
          <p class="text-sm text-light-900 dark:text-dark-900 leading-relaxed">
            We reserve the right to modify these Terms at any time. We will notify you of significant changes by posting the updated Terms on this page. Your continued use of the Service after changes take effect constitutes acceptance of the updated Terms.
          </p>
        </section>

        <section>
          <h2 class="text-lg font-semibold text-light-1000 dark:text-dark-1000 mb-3">12. Governing Law</h2>
          <p class="text-sm text-light-900 dark:text-dark-900 leading-relaxed">
            These Terms shall be governed by and construed in accordance with the laws of the Republic of Indonesia, without regard to its conflict of law provisions. Any disputes shall be resolved in the courts of Indonesia.
          </p>
        </section>

        <section>
          <h2 class="text-lg font-semibold text-light-1000 dark:text-dark-1000 mb-3">13. Contact Us</h2>
          <p class="text-sm text-light-900 dark:text-dark-900 leading-relaxed">
            If you have questions about these Terms, please contact us at <strong>legal@managpro.com</strong>.
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
