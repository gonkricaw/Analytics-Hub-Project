<script setup>
import { useAuth } from '@/composables/useAuth.js'
import { useFormAccessibility } from '@/composables/useFormAccessibility.js'
import { useSystemConfigStore } from '@/stores/systemConfig.js'
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import authV2LoginIllustrationBorderedDark from '@images/pages/auth-v2-login-illustration-bordered-dark.png'
import authV2LoginIllustrationBorderedLight from '@images/pages/auth-v2-login-illustration-bordered-light.png'
import authV2LoginIllustrationDark from '@images/pages/auth-v2-login-illustration-dark.png'
import authV2LoginIllustrationLight from '@images/pages/auth-v2-login-illustration-light.png'
import authV2MaskDark from '@images/pages/misc-mask-dark.png'
import authV2MaskLight from '@images/pages/misc-mask-light.png'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'

definePage({
  meta: {
    layout: 'guest',
    public: true,
  },
})

// Authentication composable
const { forgotPassword } = useAuth()

// System configuration store
const systemConfigStore = useSystemConfigStore()

// Computed properties for dynamic branding
const appBranding = computed(() => systemConfigStore.appBranding)
const appTitle = computed(() => appBranding.value.name || themeConfig.app.title)
const showLogo = computed(() => systemConfigStore.loginConfig.showLogo !== false)

// Form state
const form = ref({
  email: '',
})

// UI state
const isLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const emailSent = ref(false)

// Form accessibility
const {
  formId,
  getFieldAttributes,
  getFieldValidation,
  announceToScreenReader,
  handleKeyboardNavigation,
  handleFormSubmission,
  isFormSubmissionAccessible,
} = useFormAccessibility({
  formName: 'forgot-password',
  validationRules: {
    email: {
      required: true,
      email: true,
      message: 'Please enter a valid email address',
    },
  },
})

// Form validation
const isFormValid = computed(() => {
  const emailRegex = /^[^\s@]+@[^\s@][^\s.@]*\.[^\s@]+$/
  
  return form.value.email && emailRegex.test(form.value.email)
})

// Handle forgot password
const handleForgotPassword = async () => {
  if (!isFormValid.value || !isFormSubmissionAccessible.value) return
  
  return await handleFormSubmission(async () => {
    isLoading.value = true
    errorMessage.value = ''
    successMessage.value = ''
    
    try {
      const result = await forgotPassword(form.value.email)
      
      if (result.success) {
        emailSent.value = true
        successMessage.value = 'Password reset instructions have been sent to your email address.'
        announceToScreenReader('Password reset email sent successfully. Check your email for instructions.')
        
        return { success: true }
      } else {
        errorMessage.value = result.error || 'Failed to send password reset email'
        announceToScreenReader(`Error: ${errorMessage.value}`)
        
        return { success: false, error: errorMessage.value }
      }
    } catch (error) {
      errorMessage.value = 'An unexpected error occurred'
      announceToScreenReader(`Error: ${errorMessage.value}`)
      
      return { success: false, error: errorMessage.value }
    } finally {
      isLoading.value = false
    }
  })
}

// Resend email
const handleResendEmail = async () => {
  emailSent.value = false
  announceToScreenReader('Resending password reset email...')
  await handleForgotPassword()
}

// Theme images
const authThemeImg = useGenerateImageVariant(authV2LoginIllustrationLight, authV2LoginIllustrationDark, authV2LoginIllustrationBorderedLight, authV2LoginIllustrationBorderedDark, true)
const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark)
</script>

<template>
  <a href="javascript:void(0)">
    <div 
      v-if="showLogo"
      class="auth-logo d-flex align-center gap-x-3"
    >
      <VNodeRenderer :nodes="themeConfig.app.logo" />
      <h1 class="auth-title">
        {{ appTitle }}
      </h1>
    </div>
  </a>

  <VRow
    no-gutters
    class="auth-wrapper bg-surface"
  >
    <VCol
      md="8"
      class="d-none d-md-flex"
    >
      <div class="position-relative bg-background w-100 me-0">
        <div
          class="d-flex align-center justify-center w-100 h-100"
          style="padding-inline: 6.25rem;"
        >
          <VImg
            max-width="613"
            :src="authThemeImg"
            class="auth-illustration mt-16 mb-2"
          />
        </div>
      </div>
    </VCol>

    <VCol
      cols="12"
      md="4"
      class="auth-card-v2 d-flex align-center justify-center"
    >
      <VCard
        flat
        :max-width="500"
        class="mt-12 mt-sm-0 pa-6"
      >
        <VCardText>
          <h4 class="text-h4 mb-1">
            Forgot Password? 🔒
          </h4>
          <p class="mb-0">
            Enter your email and we'll send you instructions to reset your password
          </p>
        </VCardText>

        <!-- Screen reader announcements -->
        <div 
          aria-live="polite" 
          aria-atomic="true" 
          class="sr-only"
        >
          {{ successMessage || errorMessage }}
        </div>

        <VCardText>
          <!-- Success state -->
          <div v-if="emailSent">
            <VAlert
              type="success"
              variant="tonal"
              class="mb-4"
            >
              <div class="d-flex align-center">
                <VIcon
                  icon="tabler-circle-check"
                  class="me-2"
                />
                <div>
                  <div class="text-body-1 font-weight-medium mb-1">
                    Email Sent Successfully!
                  </div>
                  <div class="text-body-2">
                    {{ successMessage }}
                  </div>
                </div>
              </div>
            </VAlert>

            <div class="text-center">
              <p class="text-body-2 mb-4">
                Didn't receive the email? Check your spam folder or
              </p>
              <VBtn
                variant="outlined"
                color="primary"
                :loading="isLoading"
                @click="handleResendEmail"
              >
                Resend Email
              </VBtn>
            </div>
          </div>

          <!-- Form state -->
          <VForm
            v-else
            :id="formId"
            @submit.prevent="handleForgotPassword"
            @keydown="handleKeyboardNavigation"
          >
            >
            <!-- Error message display -->
            <VAlert
              v-if="errorMessage"
              type="error"
              class="mb-4"
              variant="tonal"
            >
              {{ errorMessage }}
            </VAlert>

            <VRow>
              <!-- email -->
              <VCol cols="12">
                <AppTextField
                  v-model="form.email"
                  autofocus
                  label="Email"
                  type="email"
                  placeholder="johndoe@email.com"
                  v-bind="getFieldAttributes('email')"
                  :error-messages="getFieldValidation('email', form.email).errorMessage"
                  :rules="[
                    (v) => !!v || 'Email is required',
                    (v) => /.+@.+\..+/.test(v) || 'Email must be valid'
                  ]"
                />
              </VCol>

              <!-- submit button -->
              <VCol cols="12">
                <VBtn
                  block
                  type="submit"
                  :loading="isLoading"
                  :disabled="!isFormValid"
                  class="mb-4"
                >
                  Send Reset Instructions
                </VBtn>

                <!-- Back to login -->
                <div class="text-center">
                  <RouterLink
                    to="/login"
                    class="text-primary d-inline-flex align-center"
                  >
                    <VIcon
                      icon="tabler-chevron-left"
                      size="20"
                      class="me-1 flip-in-rtl"
                    />
                    <span>Back to login</span>
                  </RouterLink>
                </div>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";
</style>
