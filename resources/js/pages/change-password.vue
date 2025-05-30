<script setup>
import { useAuth } from '@/composables/useAuth.js'
import { useFormAccessibility } from '@/composables/useFormAccessibility.js'
import { useIconSystem } from '@/composables/useIconSystem.js'
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
    requiresAuth: true,
  },
})

// Authentication composable
const { changePassword, user, needsPasswordChange } = useAuth()
const router = useRouter()

// System configuration store
const systemConfigStore = useSystemConfigStore()

// Computed properties for dynamic branding
const appBranding = computed(() => systemConfigStore.appBranding)
const appTitle = computed(() => appBranding.value.name || themeConfig.app.title)
const showLogo = computed(() => systemConfigStore.loginConfig.showLogo !== false)

// Form state
const form = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
})

// UI state
const isCurrentPasswordVisible = ref(false)
const isNewPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

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
  formName: 'change-password',
  validationRules: {
    current_password: {
      required: !needsPasswordChange.value,
      message: 'Current password is required',
    },
    new_password: {
      required: true,
      minLength: 8,
      message: 'New password must meet all requirements',
    },
    new_password_confirmation: {
      required: true,
      message: 'Password confirmation is required',
    },
  },
})

// Icon system
const { getStatusIcon, getActionIcon } = useIconSystem()

// Form validation
const isFormValid = computed(() => {
  if (needsPasswordChange.value) {
    // Initial password change - no current password required
    return form.value.new_password && 
           form.value.new_password_confirmation &&
           form.value.new_password === form.value.new_password_confirmation
  } else {
    // Regular password change - current password required
    return form.value.current_password && 
           form.value.new_password && 
           form.value.new_password_confirmation &&
           form.value.new_password === form.value.new_password_confirmation
  }
})

// Password strength validation
const passwordRequirements = computed(() => {
  const password = form.value.new_password
  
  return {
    minLength: password.length >= 8,
    hasUppercase: /[A-Z]/.test(password),
    hasLowercase: /[a-z]/.test(password),
    hasNumber: /\d/.test(password),
  }
})

const isPasswordStrong = computed(() => {
  return Object.values(passwordRequirements.value).every(req => req)
})

// Handle password change
const handlePasswordChange = async () => {
  if (!isFormValid.value || !isPasswordStrong.value || !isFormSubmissionAccessible.value) return
  
  return await handleFormSubmission(async () => {
    isLoading.value = true
    errorMessage.value = ''
    successMessage.value = ''
    
    try {
      const passwordData = {
        new_password: form.value.new_password,
        new_password_confirmation: form.value.new_password_confirmation,
      }

      // Add current password if not initial change
      if (!needsPasswordChange.value) {
        passwordData.current_password = form.value.current_password
      } else {
        passwordData.is_initial_change = true
      }

      const result = await changePassword(passwordData)
      
      if (result.success) {
        successMessage.value = result.message || 'Password changed successfully!'
        announceToScreenReader('Password changed successfully. Redirecting to dashboard in 2 seconds.')
        
        // Redirect to dashboard after successful change
        setTimeout(() => {
          router.push('/')
        }, 2000)
        
        return { success: true }
      } else {
        errorMessage.value = result.error || 'Password change failed'
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

// Redirect if user doesn't need password change and it's not initial login
onMounted(() => {
  if (!needsPasswordChange.value && !user.value?.temporary_password_used) {
    router.push('/')
  }
})

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

        <img
          class="auth-footer-mask flip-in-rtl"
          :src="authThemeMask"
          alt="auth-footer-mask"
          height="280"
          width="100"
        >
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
            {{ needsPasswordChange ? 'Set Your New Password' : 'Change Password' }} 🔒
          </h4>
          <p class="mb-0">
            {{ needsPasswordChange ? 'Please set a new password to secure your account' : 'Update your password to keep your account secure' }}
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
          <VForm 
            :id="formId"
            @submit.prevent="handlePasswordChange"
            @keydown="handleKeyboardNavigation"
          >
            <!-- Success message display -->
            <VAlert
              v-if="successMessage"
              type="success"
              class="mb-4"
              variant="tonal"
            >
              {{ successMessage }}
            </VAlert>

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
              <!-- Current password (only if not initial change) -->
              <VCol 
                v-if="!needsPasswordChange"
                cols="12"
              >
                <AppTextField
                  v-model="form.current_password"
                  label="Current Password"
                  placeholder="············"
                  :type="isCurrentPasswordVisible ? 'text' : 'password'"
                  autocomplete="current-password"
                  :append-inner-icon="isCurrentPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  v-bind="getFieldAttributes('current_password')"
                  :error-messages="getFieldValidation('current_password', form.current_password).errorMessage"
                  @click:append-inner="isCurrentPasswordVisible = !isCurrentPasswordVisible"
                />
              </VCol>

              <!-- New password -->
              <VCol cols="12">
                <AppTextField
                  v-model="form.new_password"
                  label="New Password"
                  placeholder="············"
                  :type="isNewPasswordVisible ? 'text' : 'password'"
                  autocomplete="new-password"
                  :append-inner-icon="isNewPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  v-bind="getFieldAttributes('new_password')"
                  :error-messages="getFieldValidation('new_password', form.new_password).errorMessage"
                  @click:append-inner="isNewPasswordVisible = !isNewPasswordVisible"
                />

                <!-- Password requirements -->
                <VCard
                  v-if="form.new_password"
                  variant="outlined"
                  class="mt-3 pa-3"
                >
                  <VCardText class="pa-0">
                    <h6 class="text-h6 mb-2">
                      Password Requirements:
                    </h6>
                    <VList class="pa-0">
                      <VListItem class="pa-1">
                        <template #prepend>
                          <VIcon
                            :color="passwordRequirements.minLength ? 'success' : 'error'"
                            :icon="passwordRequirements.minLength ? getStatusIcon('success') : getStatusIcon('error')"
                            size="16"
                          />
                        </template>
                        <VListItemTitle class="text-body-2">
                          At least 8 characters
                        </VListItemTitle>
                      </VListItem>
                      <VListItem class="pa-1">
                        <template #prepend>
                          <VIcon
                            :color="passwordRequirements.hasUppercase ? 'success' : 'error'"
                            :icon="passwordRequirements.hasUppercase ? getStatusIcon('success') : getStatusIcon('error')"
                            size="16"
                          />
                        </template>
                        <VListItemTitle class="text-body-2">
                          One uppercase letter
                        </VListItemTitle>
                      </VListItem>
                      <VListItem class="pa-1">
                        <template #prepend>
                          <VIcon
                            :color="passwordRequirements.hasLowercase ? 'success' : 'error'"
                            :icon="passwordRequirements.hasLowercase ? getStatusIcon('success') : getStatusIcon('error')"
                            size="16"
                          />
                        </template>
                        <VListItemTitle class="text-body-2">
                          One lowercase letter
                        </VListItemTitle>
                      </VListItem>
                      <VListItem class="pa-1">
                        <template #prepend>
                          <VIcon
                            :color="passwordRequirements.hasNumber ? 'success' : 'error'"
                            :icon="passwordRequirements.hasNumber ? getStatusIcon('success') : getStatusIcon('error')"
                            size="16"
                          />
                        </template>
                        <VListItemTitle class="text-body-2">
                          One number
                        </VListItemTitle>
                      </VListItem>
                    </VList>
                  </VCardText>
                </VCard>
              </VCol>

              <!-- Confirm password -->
              <VCol cols="12">
                <AppTextField
                  v-model="form.new_password_confirmation"
                  label="Confirm New Password"
                  placeholder="············"
                  :type="isConfirmPasswordVisible ? 'text' : 'password'"
                  autocomplete="new-password"
                  :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  v-bind="getFieldAttributes('new_password_confirmation')"
                  :error-messages="getFieldValidation('new_password_confirmation', form.new_password_confirmation).errorMessage"
                  @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                />

                <!-- Password match indicator -->
                <div
                  v-if="form.new_password_confirmation"
                  class="mt-2"
                >
                  <VChip
                    v-if="form.new_password === form.new_password_confirmation"
                    color="success"
                    size="small"
                    variant="tonal"
                  >
                    <VIcon
                      start
                      :icon="getStatusIcon('success')"
                      size="14"
                    />
                    Passwords match
                  </VChip>
                  <VChip
                    v-else
                    color="error"
                    size="small"
                    variant="tonal"
                  >
                    <VIcon
                      start
                      :icon="getStatusIcon('error')"
                      size="14"
                    />
                    Passwords don't match
                  </VChip>
                </div>

                <VBtn
                  block
                  type="submit"
                  :loading="isLoading"
                  :disabled="!isFormValid || !isPasswordStrong"
                  class="mt-6"
                >
                  {{ needsPasswordChange ? 'Set Password' : 'Change Password' }}
                </VBtn>
              </VCol>

              <!-- Back to login link (if not initial change) -->
              <VCol
                v-if="!needsPasswordChange"
                cols="12"
                class="text-center"
              >
                <a
                  class="text-primary"
                  href="/login"
                >
                  Back to Login
                </a>
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
