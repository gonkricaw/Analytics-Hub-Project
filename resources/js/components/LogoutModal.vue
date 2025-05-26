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
const emit = defineEmits(['update:modelValue', 'confirmed'])

// Authentication composable
const { logout } = useAuth()

// State
const isLoading = ref(false)

// Computed
const dialogModel = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value),
})

// Handle logout
const handleLogout = async () => {
  isLoading.value = true
  
  try {
    await logout()
    emit('confirmed')
    dialogModel.value = false
  } catch (error) {
    console.error('Logout error:', error)
  } finally {
    isLoading.value = false
  }
}

// Handle cancel
const handleCancel = () => {
  dialogModel.value = false
}
</script>

<template>
  <VDialog
    v-model="dialogModel"
    max-width="400"
  >
    <VCard>
      <VCardTitle class="pa-6">
        <div class="d-flex align-center">
          <VIcon
            icon="tabler-logout"
            class="me-3"
            color="warning"
          />
          <span>Confirm Logout</span>
        </div>
      </VCardTitle>

      <VDivider />

      <VCardText class="pa-6">
        <p class="text-body-1 mb-0">
          Are you sure you want to logout? You will need to sign in again to access your account.
        </p>
      </VCardText>

      <VDivider />

      <VCardActions class="pa-6">
        <VSpacer />
        <VBtn
          variant="outlined"
          :disabled="isLoading"
          @click="handleCancel"
        >
          Cancel
        </VBtn>
        <VBtn
          color="warning"
          :loading="isLoading"
          @click="handleLogout"
        >
          <VIcon
            icon="tabler-logout"
            start
          />
          Logout
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>
