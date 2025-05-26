<script setup>
import { useAuth } from '@/composables/useAuth.js'
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import authV2LoginIllustrationBorderedDark from '@images/pages/auth-v2-login-illustration-bordered-dark.png'
import authV2LoginIllustrationBorderedLight from '@images/pages/auth-v2-login-illustration-bordered-light.png'
import authV2LoginIllustrationDark from '@images/pages/auth-v2-login-illustration-dark.png'
import authV2LoginIllustrationLight from '@images/pages/auth-v2-login-illustration-light.png'
import authV2MaskDark from '@images/pages/misc-mask-dark.png'
import authV2MaskLight from '@images/pages/misc-mask-light.png'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { useRoute, useRouter } from 'vue-router'

definePage({
  meta: {
    layout: 'guest',
    public: true,
  },
})

// Authentication composable
const { resetPassword } = useAuth()
const route = useRoute()
const router = useRouter()

// Form state
const form = ref({
  email: route.query.email || '',
  password: '',
  password_confirmation: '',
  token: route.query.token || '',
})

// UI state
const isPasswordVisible = ref(false)
const isPasswordConfirmationVisible = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const resetComplete = ref(false)

// Password requirements
const passwordRequirements = [
  { text: 'At least 8 characters', test: pwd => pwd.length >= 8 },
  { text: 'At least one uppercase letter', test: pwd => /[A-Z]/.test(pwd) },
  { text: 'At least one lowercase letter', test: pwd => /[a-z]/.test(pwd) },
  { text: 'At least one number', test: pwd => /\d/.test(pwd) },
  { text: 'At least one special character', test: pwd => /[!@#$%^&*(),.?":{}|<>]/.test(pwd) },
]

// Computed properties
const passwordStrength = computed(() => {
  const password = form.value.password
  const passedRequirements = passwordRequirements.filter(req => req.test(password))
  
  return passedRequirements.length
})

const passwordsMatch = computed(() => {
  return form.value.password === form.value.password_confirmation && form.value.password_confirmation.length > 0
})

const isFormValid = computed(() => {
  return (
    form.value.email &&
    form.value.token &&
    passwordStrength.value === passwordRequirements.length &&
    passwordsMatch.value
  )
})

// Check for invalid token on mount
onMounted(() => {
  if (!form.value.token || !form.value.email) {
    errorMessage.value = 'Invalid or expired reset link. Please request a new password reset.'
  }
})

// Handle password reset
const handlePasswordReset = async () => {
  if (!isFormValid.value) return
  
  isLoading.value = true
  errorMessage.value = ''
  successMessage.value = ''
  
  try {
    const result = await resetPassword({
      email: form.value.email,
      password: form.value.password,
      password_confirmation: form.value.password_confirmation,
      token: form.value.token,
    })
    
    if (result.success) {
      resetComplete.value = true
      successMessage.value = 'Your password has been reset successfully!'
      
      // Redirect to login after 3 seconds
      setTimeout(() => {
        router.push('/login')
      }, 3000)
    } else {
      errorMessage.value = result.error || 'Failed to reset password'
    }
  } catch (error) {
    errorMessage.value = 'An unexpected error occurred'
  } finally {
    isLoading.value = false
  }
}

// Theme images
const authThemeImg = useGenerateImageVariant(authV2LoginIllustrationLight, authV2LoginIllustrationDark, authV2LoginIllustrationBorderedLight, authV2LoginIllustrationBorderedDark, true)
const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark)
</script>

<template>
  <a href="javascript:void(0)">
    <div class="auth-logo d-flex align-center gap-x-3">
      <VNodeRenderer :nodes="themeConfig.app.logo" />
      <h1 class="auth-title">
        {{ themeConfig.app.title }}
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
            Reset Password 🔐
          </h4>
          <p class="mb-0">
            Enter your new password below
          </p>
        </VCardText>

        <VCardText>
          <!-- Success state -->
          <div v-if="resetComplete">
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
                    Password Reset Successfully!
                  </div>
                  <div class="text-body-2">
                    {{ successMessage }}
                  </div>
                </div>
              </div>
            </VAlert>

            <div class="text-center">
              <p class="text-body-2 mb-4">
                Redirecting to login page...
              </p>
              <VBtn
                color="primary"
                @click="router.push('/login')"
              >
                Go to Login
              </VBtn>
            </div>
          </div>

          <!-- Form state -->
          <VForm
            v-else
            @submit.prevent="handlePasswordReset"
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
              <!-- email (read-only) -->
              <VCol cols="12">
                <AppTextField
                  v-model="form.email"
                  label="Email"
                  type="email"
                  readonly
                  variant="outlined"
                />
              </VCol>

              <!-- new password -->
              <VCol cols="12">
                <AppTextField
                  v-model="form.password"
                  label="New Password"
                  placeholder="············"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  autocomplete="new-password"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />

                <!-- Password requirements -->
                <div class="mt-3">
                  <p class="text-body-2 mb-2 text-medium-emphasis">
                    Password requirements:
                  </p>
                  <div class="d-flex flex-column gap-2">
                    <div
                      v-for="(requirement, index) in passwordRequirements"
                      :key="index"
                      class="d-flex align-center"
                    >
                      <VIcon
                        :icon="requirement.test(form.password) ? 'tabler-circle-check' : 'tabler-circle'"
                        :color="requirement.test(form.password) ? 'success' : 'disabled'"
                        size="16"
                        class="me-2"
                      />
                      <span
                        class="text-body-2"
                        :class="requirement.test(form.password) ? 'text-success' : 'text-disabled'"
                      >
                        {{ requirement.text }}
                      </span>
                    </div>
                  </div>
                </div>
              </VCol>

              <!-- confirm password -->
              <VCol cols="12">
                <AppTextField
                  v-model="form.password_confirmation"
                  label="Confirm Password"
                  placeholder="············"
                  :type="isPasswordConfirmationVisible ? 'text' : 'password'"
                  autocomplete="new-password"
                  :append-inner-icon="isPasswordConfirmationVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordConfirmationVisible = !isPasswordConfirmationVisible"
                />

                <!-- Password match indicator -->
                <div
                  v-if="form.password_confirmation.length > 0"
                  class="mt-2 d-flex align-center"
                >
                  <VIcon
                    :icon="passwordsMatch ? 'tabler-circle-check' : 'tabler-circle-x'"
                    :color="passwordsMatch ? 'success' : 'error'"
                    size="16"
                    class="me-2"
                  />
                  <span
                    class="text-body-2"
                    :class="passwordsMatch ? 'text-success' : 'text-error'"
                  >
                    {{ passwordsMatch ? 'Passwords match' : 'Passwords do not match' }}
                  </span>
                </div>
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
                  Reset Password
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
