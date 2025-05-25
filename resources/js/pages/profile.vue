<script setup>
import { useAuth } from '@/composables/useAuth.js'
import { computed, ref } from 'vue'

definePage({
  meta: {
    requiresAuth: true,
  },
})

// Authentication composable
const { user, updateProfile, changePassword, logout } = useAuth()

// Tabs
const currentTab = ref('profile')

// Profile form state
const profileForm = ref({
  name: user.value?.name || '',
  email: user.value?.email || '',
  profile_photo: null,
})

// Password form state
const passwordForm = ref({
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

// Profile photo preview
const profilePhotoPreview = ref(user.value?.profile_photo_url || null)
const fileInput = ref(null)

// Password requirements
const passwordRequirements = [
  { text: 'At least 8 characters', test: (pwd) => pwd.length >= 8 },
  { text: 'At least one uppercase letter', test: (pwd) => /[A-Z]/.test(pwd) },
  { text: 'At least one lowercase letter', test: (pwd) => /[a-z]/.test(pwd) },
  { text: 'At least one number', test: (pwd) => /\d/.test(pwd) },
  { text: 'At least one special character', test: (pwd) => /[!@#$%^&*(),.?":{}|<>]/.test(pwd) },
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
  return profileForm.value.name.trim() && profileForm.value.email.trim()
})

const isPasswordFormValid = computed(() => {
  return (
    passwordForm.value.current_password &&
    passwordStrength.value === passwordRequirements.length &&
    passwordsMatch.value
  )
})

// Watch for user changes
watch(user, (newUser) => {
  if (newUser) {
    profileForm.value.name = newUser.name || ''
    profileForm.value.email = newUser.email || ''
    profilePhotoPreview.value = newUser.profile_photo_url || null
  }
}, { immediate: true })

// Handle profile photo selection
const handlePhotoSelection = (event) => {
  const file = event.target.files[0]
  if (file) {
    // Validate file type
    if (!file.type.startsWith('image/')) {
      profileError.value = 'Please select a valid image file'
      return
    }
    
    // Validate file size (max 2MB)
    if (file.size > 2 * 1024 * 1024) {
      profileError.value = 'Image size must be less than 2MB'
      return
    }
    
    profileForm.value.profile_photo = file
    
    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      profilePhotoPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
    
    profileError.value = ''
  }
}

// Remove profile photo
const removePhoto = () => {
  profileForm.value.profile_photo = null
  profilePhotoPreview.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

// Handle profile update
const handleUpdateProfile = async () => {
  if (!isProfileFormValid.value) return
  
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
      // Reset photo file input
      profileForm.value.profile_photo = null
      if (fileInput.value) {
        fileInput.value.value = ''
      }
    } else {
      profileError.value = result.error || 'Failed to update profile'
    }
  } catch (error) {
    profileError.value = 'An unexpected error occurred'
  } finally {
    isUpdatingProfile.value = false
  }
}

// Handle password change
const handleChangePassword = async () => {
  if (!isPasswordFormValid.value) return
  
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
      // Reset form
      passwordForm.value = {
        current_password: '',
        password: '',
        password_confirmation: '',
      }
    } else {
      passwordError.value = result.error || 'Failed to change password'
    }
  } catch (error) {
    passwordError.value = 'An unexpected error occurred'
  } finally {
    isChangingPassword.value = false
  }
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
                <VForm @submit.prevent="handleUpdateProfile">
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
                            @change="handlePhotoSelection"
                          >
                          
                          <p class="text-body-2 text-disabled mt-2 mb-0">
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
                        label="Full Name"
                        placeholder="John Doe"
                        :rules="[(v) => !!v || 'Name is required']"
                      />
                    </VCol>

                    <!-- Email -->
                    <VCol
                      cols="12"
                      md="6"
                    >
                      <AppTextField
                        v-model="profileForm.email"
                        label="Email"
                        type="email"
                        placeholder="johndoe@email.com"
                        :rules="[
                          (v) => !!v || 'Email is required',
                          (v) => /.+@.+\..+/.test(v) || 'Email must be valid'
                        ]"
                      />
                    </VCol>

                    <!-- Submit button -->
                    <VCol cols="12">
                      <VBtn
                        type="submit"
                        :loading="isUpdatingProfile"
                        :disabled="!isProfileFormValid"
                      >
                        Save Changes
                      </VBtn>
                    </VCol>
                  </VRow>
                </VForm>
              </VWindowItem>

              <!-- Security Tab -->
              <VWindowItem value="security">
                <VForm @submit.prevent="handleChangePassword">
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
                      >
                        {{ passwordError }}
                      </VAlert>
                    </VCol>

                    <!-- Current Password -->
                    <VCol cols="12">
                      <AppTextField
                        v-model="passwordForm.current_password"
                        label="Current Password"
                        placeholder="············"
                        :type="isCurrentPasswordVisible ? 'text' : 'password'"
                        autocomplete="current-password"
                        :append-inner-icon="isCurrentPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                        @click:append-inner="isCurrentPasswordVisible = !isCurrentPasswordVisible"
                      />
                    </VCol>

                    <!-- New Password -->
                    <VCol cols="12">
                      <AppTextField
                        v-model="passwordForm.password"
                        label="New Password"
                        placeholder="············"
                        :type="isNewPasswordVisible ? 'text' : 'password'"
                        autocomplete="new-password"
                        :append-inner-icon="isNewPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                        @click:append-inner="isNewPasswordVisible = !isNewPasswordVisible"
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
                              :icon="requirement.test(passwordForm.password) ? 'tabler-circle-check' : 'tabler-circle'"
                              :color="requirement.test(passwordForm.password) ? 'success' : 'disabled'"
                              size="16"
                              class="me-2"
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
                        label="Confirm New Password"
                        placeholder="············"
                        :type="isConfirmPasswordVisible ? 'text' : 'password'"
                        autocomplete="new-password"
                        :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                        @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                      />

                      <!-- Password match indicator -->
                      <div
                        v-if="passwordForm.password_confirmation.length > 0"
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

                    <!-- Submit button -->
                    <VCol cols="12">
                      <VBtn
                        type="submit"
                        :loading="isChangingPassword"
                        :disabled="!isPasswordFormValid"
                      >
                        Change Password
                      </VBtn>
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
