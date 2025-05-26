<script setup>
import { useAuth } from '@/composables/useAuth.js'

// Props
const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
})

// Emits
const emit = defineEmits(['update:modelValue'])

// Authentication composable
const { acceptTerms } = useAuth()

// State
const isLoading = ref(false)
const errorMessage = ref('')
const hasScrolledToBottom = ref(false)
const termsContainer = ref(null)

// Computed
const dialogModel = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value),
})

// Check if user has scrolled to bottom
const handleScroll = () => {
  const element = termsContainer.value
  if (element) {
    const threshold = 50 // Allow 50px threshold
    const isAtBottom = element.scrollTop + element.clientHeight >= element.scrollHeight - threshold

    hasScrolledToBottom.value = isAtBottom
  }
}

// Handle accepting terms
const handleAcceptTerms = async () => {
  isLoading.value = true
  errorMessage.value = ''
  
  try {
    const result = await acceptTerms()
    
    if (result.success) {
      dialogModel.value = false
    } else {
      errorMessage.value = result.error || 'Failed to accept terms and conditions'
    }
  } catch (error) {
    errorMessage.value = 'An unexpected error occurred'
  } finally {
    isLoading.value = false
  }
}

// Reset state when dialog opens
watch(dialogModel, newValue => {
  if (newValue) {
    hasScrolledToBottom.value = false
    errorMessage.value = ''
  }
})

// Terms content (in a real app, this would likely come from an API or CMS)
const termsContent = `
<h3>Terms and Conditions</h3>
<p><strong>Last updated:</strong> ${new Date().toLocaleDateString()}</p>

<h4>1. Introduction</h4>
<p>Welcome to Indonet Analytics Hub ("we," "our," or "us"). These Terms and Conditions ("Terms") govern your use of our analytics platform and services.</p>

<h4>2. Acceptance of Terms</h4>
<p>By accessing and using this platform, you accept and agree to be bound by the terms and provision of this agreement.</p>

<h4>3. Use License</h4>
<p>Permission is granted to temporarily access the materials on Indonet Analytics Hub for personal, non-commercial transitory viewing only. This is the grant of a license, not a transfer of title, and under this license you may not:</p>
<ul>
  <li>modify or copy the materials;</li>
  <li>use the materials for any commercial purpose or for any public display (commercial or non-commercial);</li>
  <li>attempt to decompile or reverse engineer any software contained on the platform;</li>
  <li>remove any copyright or other proprietary notations from the materials.</li>
</ul>

<h4>4. Data Privacy and Security</h4>
<p>We are committed to protecting your privacy and maintaining the security of your data. By using our platform, you agree to our data collection and processing practices as outlined in our Privacy Policy.</p>

<h4>5. User Responsibilities</h4>
<p>You are responsible for:</p>
<ul>
  <li>Maintaining the confidentiality of your account credentials</li>
  <li>All activities that occur under your account</li>
  <li>Ensuring that your use of the platform complies with applicable laws</li>
  <li>Providing accurate and up-to-date information</li>
</ul>

<h4>6. Prohibited Activities</h4>
<p>You agree not to:</p>
<ul>
  <li>Use the platform for any unlawful purpose or to solicit others to take or refrain from taking any unlawful action</li>
  <li>Violate any international, federal, provincial, or local regulations, rules, laws, or ordinances</li>
  <li>Infringe upon or violate our intellectual property rights or the intellectual property rights of others</li>
  <li>Harass, abuse, insult, harm, defame, slander, disparage, intimidate, or discriminate</li>
  <li>Submit false or misleading information</li>
</ul>

<h4>7. Analytics and Reporting</h4>
<p>Our platform provides analytics and reporting services. You acknowledge that:</p>
<ul>
  <li>Data accuracy depends on proper integration and setup</li>
  <li>We are not responsible for decisions made based on analytics data</li>
  <li>Historical data may be subject to revision as our algorithms improve</li>
</ul>

<h4>8. Service Availability</h4>
<p>While we strive to maintain continuous service availability, we do not guarantee uninterrupted access to the platform. We may perform maintenance, updates, or modifications that may temporarily affect service availability.</p>

<h4>9. Intellectual Property</h4>
<p>The platform and its original content, features, and functionality are and will remain the exclusive property of Indonet Analytics Hub and its licensors. The platform is protected by copyright, trademark, and other laws.</p>

<h4>10. Limitation of Liability</h4>
<p>In no event shall Indonet Analytics Hub, nor its directors, employees, partners, agents, suppliers, or affiliates, be liable for any indirect, incidental, special, consequential, or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses, resulting from your access to or use of or inability to access or use the platform.</p>

<h4>11. Termination</h4>
<p>We may terminate or suspend your account and bar access to the platform immediately, without prior notice or liability, under our sole discretion, for any reason whatsoever and without limitation, including but not limited to a breach of the Terms.</p>

<h4>12. Changes to Terms</h4>
<p>We reserve the right, at our sole discretion, to modify or replace these Terms at any time. If a revision is material, we will try to provide at least 30 days notice prior to any new terms taking effect.</p>

<h4>13. Contact Information</h4>
<p>If you have any questions about these Terms and Conditions, please contact us at:</p>
<ul>
  <li>Email: legal@indonet-analytics.com</li>
  <li>Phone: +62 21 1234 5678</li>
  <li>Address: Jakarta, Indonesia</li>
</ul>

<h4>14. Governing Law</h4>
<p>These Terms shall be interpreted and governed in accordance with the laws of Indonesia, and you submit to the exclusive jurisdiction of the state and federal courts located in Jakarta, Indonesia for the resolution of any disputes.</p>
`
</script>

<template>
  <VDialog
    v-model="dialogModel"
    max-width="800"
    persistent
    scrollable
  >
    <VCard>
      <VCardTitle class="pa-6">
        <div class="d-flex align-center">
          <VIcon
            icon="tabler-file-text"
            class="me-3"
            color="primary"
          />
          <span>Terms and Conditions</span>
        </div>
      </VCardTitle>

      <VDivider />

      <!-- Error message -->
      <VCardText
        v-if="errorMessage"
        class="pa-4"
      >
        <VAlert
          type="error"
          variant="tonal"
        >
          {{ errorMessage }}
        </VAlert>
      </VCardText>

      <!-- Terms content -->
      <VCardText
        ref="termsContainer"
        class="pa-6"
        style="block-size: 400px; overflow-y: auto;"
        @scroll="handleScroll"
      >
        <div
          class="terms-content"
          v-html="termsContent"
        />
      </VCardText>

      <VDivider />

      <!-- Actions -->
      <VCardActions class="pa-6">
        <div class="d-flex flex-column w-100">
          <!-- Scroll notice -->
          <div
            v-if="!hasScrolledToBottom"
            class="d-flex align-center mb-4 text-warning"
          >
            <VIcon
              icon="tabler-arrow-down"
              class="me-2"
            />
            <span class="text-body-2">
              Please scroll to the bottom to read all terms and conditions
            </span>
          </div>

          <!-- Buttons -->
          <div class="d-flex gap-3 justify-end">
            <VBtn
              variant="outlined"
              color="error"
              :disabled="isLoading"
              @click="$router.push('/login').then(() => logout())"
            >
              Decline & Logout
            </VBtn>
            <VBtn
              color="primary"
              :loading="isLoading"
              :disabled="!hasScrolledToBottom"
              @click="handleAcceptTerms"
            >
              Accept & Continue
            </VBtn>
          </div>

          <p class="text-body-2 text-center text-disabled mt-3 mb-0">
            By clicking "Accept & Continue", you agree to comply with these terms and conditions.
          </p>
        </div>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<style scoped>
.terms-content {
  line-height: 1.6;
}

.terms-content h3 {
  color: rgb(var(--v-theme-primary));
  margin-block-end: 1rem;
}

.terms-content h4 {
  color: rgb(var(--v-theme-on-surface));
  margin-block: 1.5rem 0.75rem;
}

.terms-content p {
  margin-block-end: 1rem;
}

.terms-content ul {
  margin-block-end: 1rem;
  padding-inline-start: 1.5rem;
}

.terms-content li {
  margin-block-end: 0.5rem;
}
</style>
