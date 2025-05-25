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

definePage({
  meta: {
    layout: 'guest',
    requiresAuth: true,
  },
})

// Authentication composable
const { changePassword, user, needsPasswordChange } = useAuth()
const router = useRouter()

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
  if (!isFormValid.value || !isPasswordStrong.value) return
  
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
      
      // Redirect to dashboard after successful change
      setTimeout(() => {
        router.push('/')
      }, 2000)
    } else {
      errorMessage.value = result.error || 'Password change failed'
    }
  } catch (error) {
    errorMessage.value = 'An unexpected error occurred'
  } finally {
    isLoading.value = false
  }
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

        <VCardText>
          <VForm @submit.prevent="handlePasswordChange">
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
                            :icon="passwordRequirements.minLength ? 'fa-check-circle' : 'fa-times-circle'"
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
                            :icon="passwordRequirements.hasUppercase ? 'fa-check-circle' : 'fa-times-circle'"
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
                            :icon="passwordRequirements.hasLowercase ? 'fa-check-circle' : 'fa-times-circle'"
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
                            :icon="passwordRequirements.hasNumber ? 'fa-check-circle' : 'fa-times-circle'"
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
                      icon="fa-check"
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
                      icon="fa-times"
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
