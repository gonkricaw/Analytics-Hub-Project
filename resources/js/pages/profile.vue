<script setup>
import { useAuth } from '@/composables/useAuth.js'
import { validationRules as rules, useFormAccessibility } from '@/composables/useFormAccessibility.js'
import { computed, ref } from 'vue'

definePage({
  meta: {
    requiresAuth: true,
  },
})

// Authentication composable
const { user, updateProfile, changePassword, logout } = useAuth()

// Form accessibility composables
const profileFormAccessibility = useFormAccessibility()
const passwordFormAccessibility = useFormAccessibility()

// Tabs
const currentTab = ref('profile')

// Profile form state
const profileForm = ref({
  name: user.value?.name || '',
  email: user.value?.email || '',
  profile_photo: null,
})

// Profile form errors
const profileErrors = ref({
  name: '',
  email: '',
})

// Password form state
const passwordForm = ref({
  current_password: '',
  password: '',
  password_confirmation: '',
})

// Password form errors
const passwordErrors = ref({
  current_password: '',
  password: '',
  password_confirmation: '',
})

// UI state
const isUpdatingProfile = ref(false)
const isChangingPassword = ref(false)
const profileMessage = ref('')
const passwordMessage = ref('')
const profileError = ref('')
const passwordError = ref('')
const isCurrentPasswordVisible = ref(false)
const isNewPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)

// Form validation rules
const profileValidationRules = {
  name: [
    rules.required('Name'),
    rules.minLength(2, 'Name')
  ],
  email: [
    rules.required('Email'),
    rules.email
  ],
}

const passwordValidationRules = {
  current_password: [
    rules.required('Current password')
  ],
  password: [
    rules.required('New password'),
    rules.password
  ],
  password_confirmation: [
    rules.required('Password confirmation'),
    (value) => {
      if (value !== passwordForm.value.password) {
        return 'Passwords do not match'
      }
      return true
    }
  ],
}

// Profile photo preview
const profilePhotoPreview = ref(user.value?.profile_photo_url || null)
const fileInput = ref(null)

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
  const password = passwordForm.value.password
  const passedRequirements = passwordRequirements.filter(req => req.test(password))
  
  return passedRequirements.length
})

const passwordsMatch = computed(() => {
  return passwordForm.value.password === passwordForm.value.password_confirmation && passwordForm.value.password_confirmation.length > 0
})

const isProfileFormValid = computed(() => {
  return profileForm.value.name.trim() && 
         profileForm.value.email.trim() && 
         !profileErrors.value.name && 
         !profileErrors.value.email
})

const isPasswordFormValid = computed(() => {
  return passwordForm.value.current_password &&
         passwordForm.value.password &&
         passwordForm.value.password_confirmation &&
         !passwordErrors.value.current_password &&
         !passwordErrors.value.password &&
         !passwordErrors.value.password_confirmation &&
         passwordsMatch.value
})

// Handle profile field validation
const handleProfileFieldValidation = field => {
  const isValid = profileFormAccessibility.validateField(field, profileForm.value[field], profileValidationRules[field])
  
  return isValid
}

// Handle password field validation
const handlePasswordFieldValidation = field => {
  const isValid = passwordFormAccessibility.validateField(field, passwordForm.value[field], passwordValidationRules[field])
  
  return isValid
}

// Watch for user changes
watch(user, newUser => {
  if (newUser) {
    profileForm.value.name = newUser.name || ''
    profileForm.value.email = newUser.email || ''
    profilePhotoPreview.value = newUser.profile_photo_url || null
  }
}, { immediate: true })

// Handle profile photo selection
const handlePhotoSelection = event => {
  const file = event.target.files[0]
  if (file) {
    // Validate file type
    if (!file.type.startsWith('image/')) {
      profileError.value = 'Please select a valid image file'
      profileFormAccessibility.announceToScreenReader('Invalid file type. Please select an image file.', 'assertive')
      
      return
    }
    
    // Validate file size (max 2MB)
    if (file.size > 2 * 1024 * 1024) {
      profileError.value = 'Image size must be less than 2MB'
      profileFormAccessibility.announceToScreenReader('File too large. Image size must be less than 2MB.', 'assertive')
      
      return
    }
    
    profileForm.value.profile_photo = file
    
    // Create preview
    const reader = new FileReader()

    reader.onload = e => {
      profilePhotoPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
    
    profileError.value = ''
    profileFormAccessibility.announceToScreenReader('Profile photo selected successfully', 'polite')
  }
}

// Remove profile photo
const removePhoto = () => {
  profileForm.value.profile_photo = null
  profilePhotoPreview.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
  profileFormAccessibility.announceToScreenReader('Profile photo removed', 'polite')
}

// Handle profile update
const handleUpdateProfile = async () => {
  // Validate each field manually
  let formIsValid = true
  const formErrors = {}
  
  for (const [field, rules] of Object.entries(profileValidationRules)) {
    const isValid = profileFormAccessibility.validateField(field, profileForm.value[field], rules)
    if (!isValid) {
      formIsValid = false
      formErrors[field] = profileFormAccessibility.errors.value[field]
    }
  }
  
  if (!formIsValid) {
    profileFormAccessibility.announceToScreenReader('Please fix the errors in the profile form', 'assertive')
    profileFormAccessibility.focusFirstError(formErrors)
    
    return
  }
  
  // Handle form submission with accessibility feedback
  await profileFormAccessibility.handleFormSubmission(async () => {
    isUpdatingProfile.value = true
    profileMessage.value = ''
    profileError.value = ''
    
    try {
      const formData = new FormData()

      formData.append('name', profileForm.value.name)
      formData.append('email', profileForm.value.email)
      
      if (profileForm.value.profile_photo) {
        formData.append('profile_photo', profileForm.value.profile_photo)
      }
      
      const result = await updateProfile(formData)
      
      if (result.success) {
        profileMessage.value = 'Profile updated successfully!'
        profileFormAccessibility.announceToScreenReader('Profile updated successfully!', 'polite')

        // Reset photo file input
        profileForm.value.profile_photo = null
        if (fileInput.value) {
          fileInput.value.value = ''
        }
      } else {
        profileError.value = result.error || 'Failed to update profile'
        profileFormAccessibility.announceToScreenReader(`Profile update failed: ${profileError.value}`, 'assertive')
        throw new Error(profileError.value)
      }
    } catch (error) {
      profileError.value = 'An unexpected error occurred'
      profileFormAccessibility.announceToScreenReader(`Error: ${profileError.value}`, 'assertive')
      throw error
    } finally {
      isUpdatingProfile.value = false
    }
  })
}

// Handle password change
const handleChangePassword = async () => {
  // Validate each field manually
  let formIsValid = true
  const formErrors = {}
  
  for (const [field, rules] of Object.entries(passwordValidationRules)) {
    const isValid = passwordFormAccessibility.validateField(field, passwordForm.value[field], rules)
    if (!isValid) {
      formIsValid = false
      formErrors[field] = passwordFormAccessibility.errors.value[field]
    }
  }
  
  if (!formIsValid) {
    passwordFormAccessibility.announceToScreenReader('Please fix the errors in the password form', 'assertive')
    passwordFormAccessibility.focusFirstError(formErrors)
    
    return
  }
  
  // Handle form submission with accessibility feedback
  await passwordFormAccessibility.handleFormSubmission(async () => {
    isChangingPassword.value = true
    passwordMessage.value = ''
    passwordError.value = ''
    
    try {
      const result = await changePassword({
        current_password: passwordForm.value.current_password,
        password: passwordForm.value.password,
        password_confirmation: passwordForm.value.password_confirmation,
      })
      
      if (result.success) {
        passwordMessage.value = 'Password changed successfully!'
        passwordFormAccessibility.announceToScreenReader('Password changed successfully!', 'polite')

        // Reset form
        passwordForm.value = {
          current_password: '',
          password: '',
          password_confirmation: '',
        }

        // Clear errors
        passwordErrors.value = {
          current_password: '',
          password: '',
          password_confirmation: '',
        }
      } else {
        passwordError.value = result.error || 'Failed to change password'
        passwordFormAccessibility.announceToScreenReader(`Password change failed: ${passwordError.value}`, 'assertive')
        throw new Error(passwordError.value)
      }
    } catch (error) {
      passwordError.value = 'An unexpected error occurred'
      passwordFormAccessibility.announceToScreenReader(`Error: ${passwordError.value}`, 'assertive')
      throw error
    } finally {
      isChangingPassword.value = false
    }
  })
}

// Clear messages when switching tabs
watch(currentTab, () => {
  profileMessage.value = ''
  passwordMessage.value = ''
  profileError.value = ''
  passwordError.value = ''
})
</script>

<template>
  <div>
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardText class="d-flex align-center pb-4">
            <VAvatar
              size="100"
              class="me-6"
              :image="profilePhotoPreview"
            >
              <VIcon
                v-if="!profilePhotoPreview"
                icon="tabler-user"
                size="50"
              />
            </VAvatar>
            
            <div>
              <h4 class="text-h4 mb-2">
                {{ user?.name || 'User' }}
              </h4>
              <p class="text-body-1 mb-0">
                {{ user?.email }}
              </p>
              <VChip
                color="success"
                size="small"
                class="mt-2"
              >
                {{ user?.role || 'User' }}
              </VChip>
            </div>
          </VCardText>

          <VDivider />

          <!-- Tabs -->
          <VTabs
            v-model="currentTab"
            color="primary"
          >
            <VTab value="profile">
              <VIcon
                icon="tabler-user"
                start
              />
              Profile
            </VTab>
            <VTab value="security">
              <VIcon
                icon="tabler-lock"
                start
              />
              Security
            </VTab>
          </VTabs>

          <VCardText>
            <VWindow v-model="currentTab">
              <!-- Profile Tab -->
              <VWindowItem value="profile">
                <VForm 
                  :id="profileFormAccessibility.formId"
                  @submit.prevent="handleUpdateProfile"
                  @keydown="profileFormAccessibility.handleKeyboardNavigation"
                >
                  <!-- Screen reader announcements for profile form -->
                  <div
                    id="profile-announcements"
                    aria-live="polite"
                    aria-atomic="true"
                    class="sr-only"
                  />
                  
                  <VRow>
                    <VCol cols="12">
                      <h5 class="text-h5 mb-4">
                        Profile Information
                      </h5>
                    </VCol>

                    <!-- Success/Error messages -->
                    <VCol
                      v-if="profileMessage"
                      cols="12"
                    >
                      <VAlert
                        type="success"
                        variant="tonal"
                        class="mb-4"
                        role="alert"
                        aria-live="polite"
                      >
                        {{ profileMessage }}
                      </VAlert>
                    </VCol>

                    <VCol
                      v-if="profileError"
                      cols="12"
                    >
                      <VAlert
                        type="error"
                        variant="tonal"
                        class="mb-4"
                        role="alert"
                        aria-live="assertive"
                      >
                        {{ profileError }}
                      </VAlert>
                    </VCol>

                    <!-- Profile Photo -->
                    <VCol cols="12">
                      <h6 class="text-h6 mb-3">
                        Profile Photo
                      </h6>
                      <div class="d-flex align-center gap-4">
                        <VAvatar
                          size="80"
                          :image="profilePhotoPreview"
                        >
                          <VIcon
                            v-if="!profilePhotoPreview"
                            icon="tabler-user"
                            size="40"
                          />
                        </VAvatar>
                        
                        <div>
                          <VBtn
                            variant="outlined"
                            size="small"
                            class="me-2"
                            :aria-describedby="`${profileFormAccessibility.formId}-photo-help`"
                            @click="$refs.fileInput.click()"
                          >
                            <VIcon
                              icon="tabler-upload"
                              start
                            />
                            Upload Photo
                          </VBtn>
                          
                          <VBtn
                            v-if="profilePhotoPreview"
                            variant="outlined"
                            color="error"
                            size="small"
                            aria-label="Remove profile photo"
                            @click="removePhoto"
                          >
                            <VIcon
                              icon="tabler-trash"
                              start
                            />
                            Remove
                          </VBtn>
                          
                          <input
                            ref="fileInput"
                            type="file"
                            accept="image/*"
                            style="display: none;"
                            aria-label="Select profile photo"
                            @change="handlePhotoSelection"
                          >
                          
                          <p 
                            :id="`${profileFormAccessibility.formId}-photo-help`"
                            class="text-body-2 text-disabled mt-2 mb-0"
                          >
                            Allowed JPG, GIF or PNG. Max size of 2MB
                          </p>
                        </div>
                      </div>
                    </VCol>

                    <!-- Name -->
                    <VCol
                      cols="12"
                      md="6"
                    >
                      <AppTextField
                        v-model="profileForm.name"
                        v-bind="profileFormAccessibility.getFieldAttributes('name', 'Your full name for profile display')"
                        label="Full Name"
                        placeholder="John Doe"
                        :error="!!profileErrors.name"
                        :error-messages="profileErrors.name"
                        @blur="handleProfileFieldValidation('name')"
                      />
                    </VCol>

                    <!-- Email -->
                    <VCol
                      cols="12"
                      md="6"
                    >
                      <AppTextField
                        v-model="profileForm.email"
                        v-bind="profileFormAccessibility.getFieldAttributes('email', 'Your email address for login and notifications')"
                        label="Email"
                        type="email"
                        placeholder="johndoe@email.com"
                        :error="!!profileErrors.email"
                        :error-messages="profileErrors.email"
                        @blur="handleProfileFieldValidation('email')"
                      />
                    </VCol>

                    <!-- Submit button -->
                    <VCol cols="12">
                      <VBtn
                        type="submit"
                        :loading="isUpdatingProfile"
                        :disabled="!isProfileFormValid"
                        :aria-describedby="!isProfileFormValid ? `${profileFormAccessibility.formId}-submit-help` : undefined"
                      >
                        Save Changes
                      </VBtn>
                      
                      <div 
                        v-if="!isProfileFormValid"
                        :id="`${profileFormAccessibility.formId}-submit-help`"
                        class="sr-only"
                      >
                        Please fill in all required fields to save changes
                      </div>
                    </VCol>
                  </VRow>
                </VForm>
              </VWindowItem>

              <!-- Security Tab -->
              <VWindowItem value="security">
                <VForm 
                  :id="passwordFormAccessibility.formId"
                  @submit.prevent="handleChangePassword"
                  @keydown="passwordFormAccessibility.handleKeyboardNavigation"
                >
                  <!-- Screen reader announcements for password form -->
                  <div
                    id="password-announcements"
                    aria-live="polite"
                    aria-atomic="true"
                    class="sr-only"
                  />
                  
                  <VRow>
                    <VCol cols="12">
                      <h5 class="text-h5 mb-4">
                        Change Password
                      </h5>
                    </VCol>

                    <!-- Success/Error messages -->
                    <VCol
                      v-if="passwordMessage"
                      cols="12"
                    >
                      <VAlert
                        type="success"
                        variant="tonal"
                        class="mb-4"
                        role="alert"
                        aria-live="polite"
                      >
                        {{ passwordMessage }}
                      </VAlert>
                    </VCol>

                    <VCol
                      v-if="passwordError"
                      cols="12"
                    >
                      <VAlert
                        type="error"
                        variant="tonal"
                        class="mb-4"
                        role="alert"
                        aria-live="assertive"
                      >
                        {{ passwordError }}
                      </VAlert>
                    </VCol>

                    <!-- Current Password -->
                    <VCol cols="12">
                      <AppTextField
                        v-model="passwordForm.current_password"
                        v-bind="passwordFormAccessibility.getFieldAttributes('current_password', 'Your current password for verification')"
                        label="Current Password"
                        placeholder="············"
                        :type="isCurrentPasswordVisible ? 'text' : 'password'"
                        autocomplete="current-password"
                        :error="!!passwordErrors.current_password"
                        :error-messages="passwordErrors.current_password"
                        :append-inner-icon="isCurrentPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                        @click:append-inner="isCurrentPasswordVisible = !isCurrentPasswordVisible"
                        @blur="handlePasswordFieldValidation('current_password')"
                      />
                    </VCol>

                    <!-- New Password -->
                    <VCol cols="12">
                      <AppTextField
                        v-model="passwordForm.password"
                        v-bind="passwordFormAccessibility.getFieldAttributes('password', 'Your new password with security requirements')"
                        label="New Password"
                        placeholder="············"
                        :type="isNewPasswordVisible ? 'text' : 'password'"
                        autocomplete="new-password"
                        :error="!!passwordErrors.password"
                        :error-messages="passwordErrors.password"
                        :append-inner-icon="isNewPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                        @click:append-inner="isNewPasswordVisible = !isNewPasswordVisible"
                        @blur="handlePasswordFieldValidation('password')"
                      />

                      <!-- Password requirements -->
                      <div 
                        :id="`${passwordFormAccessibility.formId}-password-requirements`"
                        class="mt-3"
                        aria-live="polite"
                      >
                        <p class="text-body-2 mb-2 text-medium-emphasis">
                          Password requirements:
                        </p>
                        <div 
                          class="d-flex flex-column gap-2"
                          role="list"
                          aria-label="Password requirements"
                        >
                          <div
                            v-for="(requirement, index) in passwordRequirements"
                            :key="index"
                            class="d-flex align-center"
                            role="listitem"
                          >
                            <VIcon
                              :icon="requirement.test(passwordForm.password) ? 'tabler-circle-check' : 'tabler-circle'"
                              :color="requirement.test(passwordForm.password) ? 'success' : 'disabled'"
                              size="16"
                              class="me-2"
                              :aria-label="requirement.test(passwordForm.password) ? 'Requirement met' : 'Requirement not met'"
                            />
                            <span
                              class="text-body-2"
                              :class="requirement.test(passwordForm.password) ? 'text-success' : 'text-disabled'"
                            >
                              {{ requirement.text }}
                            </span>
                          </div>
                        </div>
                      </div>
                    </VCol>

                    <!-- Confirm New Password -->
                    <VCol cols="12">
                      <AppTextField
                        v-model="passwordForm.password_confirmation"
                        v-bind="passwordFormAccessibility.getFieldAttributes('password_confirmation', 'Confirm your new password')"
                        label="Confirm New Password"
                        placeholder="············"
                        :type="isConfirmPasswordVisible ? 'text' : 'password'"
                        autocomplete="new-password"
                        :error="!!passwordErrors.password_confirmation"
                        :error-messages="passwordErrors.password_confirmation"
                        :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                        @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                        @blur="handlePasswordFieldValidation('password_confirmation')"
                      />

                      <!-- Password match indicator -->
                      <div
                        v-if="passwordForm.password_confirmation.length > 0"
                        class="mt-2 d-flex align-center"
                        role="status"
                        aria-live="polite"
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

                    <!-- Submit button -->
                    <VCol cols="12">
                      <VBtn
                        type="submit"
                        :loading="isChangingPassword"
                        :disabled="!isPasswordFormValid"
                        :aria-describedby="!isPasswordFormValid ? `${passwordFormAccessibility.formId}-submit-help` : undefined"
                      >
                        Change Password
                      </VBtn>
                      
                      <div 
                        v-if="!isPasswordFormValid"
                        :id="`${passwordFormAccessibility.formId}-submit-help`"
                        class="sr-only"
                      >
                        Please fill in all password fields correctly to change password
                      </div>
                    </VCol>
                  </VRow>
                </VForm>
              </VWindowItem>
            </VWindow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>

<style scoped>
.v-avatar img {
  object-fit: cover;
}
</style>
