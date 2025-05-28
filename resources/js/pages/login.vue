<script setup>
import { useAuth } from '@/composables/useAuth.js'
import { validationRules as rules, useFormAccessibility } from '@/composables/useFormAccessibility.js'
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import authV2LoginIllustrationBorderedDark from '@images/pages/auth-v2-login-illustration-bordered-dark.png'
import authV2LoginIllustrationBorderedLight from '@images/pages/auth-v2-login-illustration-bordered-light.png'

import { useSystemConfigStore } from '@/stores/systemConfig.js'
import authV2LoginIllustrationDark from '@images/pages/auth-v2-login-illustration-dark.png'
import authV2LoginIllustrationLight from '@images/pages/auth-v2-login-illustration-light.png'
// import authV2LoginIllustrationDark from '@images/pages/log-in-girl.svg'
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
const { login } = useAuth()

// Form accessibility composable
const {
  formId,
  getFieldAttributes,
  getErrorAttributes,
  handleKeyboardNavigation,
  announceToScreenReader,
  handleFormSubmission,
  validateField,
  focusFirstError,
} = useFormAccessibility()

// System configuration store
const systemConfigStore = useSystemConfigStore()

// Form state
const form = ref({
  email: '',
  password: '',
  remember: false,
})

// Validation errors
const errors = ref({
  email: '',
  password: '',
})

// UI state
const isPasswordVisible = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')

// Form validation rules
const validationRules = {
  email: [
    rules.required('Email'),
    rules.email
  ],
  password: [
    rules.required('Password'),
    rules.minLength(6, 'Password')
  ],
}

// Form validation
const isFormValid = computed(() => {
  return form.value.email && 
         form.value.password && 
         !errors.value.email && 
         !errors.value.password
})

// Login configuration computed properties
const loginConfig = computed(() => systemConfigStore.loginConfig)
const appBranding = computed(() => systemConfigStore.appBranding)

// Dynamic welcome message and subtitle
const welcomeMessage = computed(() => {
  return loginConfig.value.welcome_message || 'Welcome to'
})

const subtitle = computed(() => {
  return loginConfig.value.subtitle || 'Please sign-in to your account and start the adventure'
})

const showLogo = computed(() => {
  return loginConfig.value.show_logo !== false // Default to true
})

const appTitle = computed(() => {
  return appBranding.value.name || themeConfig.app.title
})

// Handle field validation
const handleFieldValidation = field => {
  const isValid = validateField(field, form.value[field], validationRules[field])
  
  return isValid
}

// Handle login
const handleLogin = async () => {
  // Validate each field manually
  let formIsValid = true
  const formErrors = {}
  
  for (const [field, rules] of Object.entries(validationRules)) {
    const isValid = validateField(field, form.value[field], rules)
    if (!isValid) {
      formIsValid = false
      formErrors[field] = errors.value[field]
    }
  }
  
  if (!formIsValid) {
    announceToScreenReader('Please fix the errors in the form', 'assertive')
    focusFirstError(formErrors)
    
    return
  }
  
  // Handle form submission with accessibility feedback
  await handleFormSubmission(async () => {
    isLoading.value = true
    errorMessage.value = ''
    
    try {
      const result = await login(form.value)
      
      if (!result.success) {
        errorMessage.value = result.error || 'Login failed'
        announceToScreenReader(`Login failed: ${errorMessage.value}`, 'assertive')
        throw new Error(errorMessage.value)
      } else {
        announceToScreenReader('Login successful, redirecting...', 'polite')
      }
    } catch (error) {
      errorMessage.value = 'An unexpected error occurred'
      announceToScreenReader(`Error: ${errorMessage.value}`, 'assertive')
      throw error
    } finally {
      isLoading.value = false
    }
  })
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

  <!-- Custom CSS injection -->
  <component 
    is="style" 
    v-if="loginConfig.custom_css"
    v-html="loginConfig.custom_css"
  />

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

        <!--
          <img
          class="auth-footer-mask flip-in-rtl"
          :src="authThemeMask"
          alt="auth-footer-mask"
          height="280"
          width="100"
          > 
        -->
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
            {{ welcomeMessage }}
          </h4>
          <h1><span class="text-capitalize">{{ appTitle }}</span>!</h1>
          <br>
          <p class="mb-0">
            {{ subtitle }}
          </p>
        </VCardText>
        <VCardText>
          <VForm 
            :id="formId"
            @submit.prevent="handleLogin"
            @keydown="handleKeyboardNavigation"
          >
            <!-- Screen reader announcements -->
            <div
              id="login-announcements"
              aria-live="polite"
              aria-atomic="true"
              class="sr-only"
            />
            
            <!-- Error message display -->
            <VAlert
              v-if="errorMessage"
              type="error"
              class="mb-4"
              variant="tonal"
              role="alert"
              aria-live="assertive"
            >
              {{ errorMessage }}
            </VAlert>

            <VRow>
              <!-- email -->
              <VCol cols="12">
                <AppTextField
                  v-model="form.email"
                  v-bind="getFieldAttributes('email', 'Email is required for login')"
                  autofocus
                  label="Email Account"
                  type="email"
                  placeholder="your.email@indonet.id"
                  :error="!!errors.email"
                  :error-messages="errors.email"
                  @blur="handleFieldValidation('email')"
                />
              </VCol>

              <!-- password -->
              <VCol cols="12">
                <AppTextField
                  v-model="form.password"
                  v-bind="getFieldAttributes('password', 'Password is required for login')"
                  label="Password"
                  placeholder="············"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  autocomplete="current-password"
                  :error="!!errors.password"
                  :error-messages="errors.password"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                  @blur="handleFieldValidation('password')"
                />

                <div class="d-flex align-center flex-wrap justify-space-between my-6">
                  <VCheckbox
                    v-model="form.remember"
                    label="Remember me"
                    :aria-describedby="`${formId}-remember-help`"
                  />
                  <div 
                    :id="`${formId}-remember-help`"
                    class="sr-only"
                  >
                    Keep me logged in on this device
                  </div>
                  <RouterLink
                    to="/forgot-password"
                    class="text-primary"
                    aria-label="Go to forgot password page"
                  >
                    Forgot Password?
                  </RouterLink>
                </div>

                <VBtn
                  block
                  type="submit"
                  :loading="isLoading"
                  :disabled="!isFormValid"
                  :aria-describedby="!isFormValid ? `${formId}-submit-help` : undefined"
                >
                  Login
                </VBtn>
                
                <div 
                  v-if="!isFormValid"
                  :id="`${formId}-submit-help`"
                  class="sr-only"
                >
                  Please fill in all required fields to enable login
                </div>
              </VCol>

              <!-- create account -->
              <!--
                <VCol
                cols="12"
                class="text-body-1 text-center"
                >
                <span class="d-inline-block">
                New on our platform?
                </span>
                <a
                class="text-primary ms-1 d-inline-block text-body-1"
                href="javascript:void(0)"
                >
                Create an account
                </a>
                </VCol> 
              -->

              <VCol
                cols="12"
                class="d-flex align-center"
              >
                <VDivider />
                <!-- <span class="mx-4">or</span> -->
                <VDivider />
              </VCol>

              <!-- auth providers -->
              <!--
                <VCol
                cols="12"
                class="text-center"
                >
                <AuthProvider />
                </VCol> 
              -->
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
