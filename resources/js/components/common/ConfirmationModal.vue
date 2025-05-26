<template>
  <VDialog
    :model-value="visible"
    :width="width"
    persistent
    class="confirmation-modal"
    @update:model-value="handleClose"
  >
    <VCard class="pa-2">
      <VCardTitle class="d-flex align-center gap-3 pa-6">
        <VIcon
          :icon="icon"
          :color="iconColor"
          size="28"
        />
        <span class="text-h5 font-weight-medium">{{ title }}</span>
      </VCardTitle>

      <VCardText class="pa-6 pt-2">
        <div
          v-if="message"
          class="text-body-1"
        >
          {{ message }}
        </div>
        <slot
          v-else
          name="content"
        />
        
        <div
          v-if="details"
          class="mt-4 pa-3 bg-grey-50 rounded"
        >
          <div class="text-caption text-medium-emphasis">
            Details:
          </div>
          <div class="text-body-2">
            {{ details }}
          </div>
        </div>
      </VCardText>

      <VCardActions class="pa-6 pt-2">
        <VSpacer />
        
        <VBtn
          variant="outlined"
          color="grey"
          :disabled="loading"
          @click="handleCancel"
        >
          <VIcon
            start
            icon="fas fa-times"
          />
          {{ cancelText }}
        </VBtn>

        <VBtn
          :variant="confirmVariant"
          :color="confirmColor"
          :loading="loading"
          @click="handleConfirm"
        >
          <VIcon
            start
            :icon="confirmIcon"
          />
          {{ confirmText }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<script setup>
/**
 * ConfirmationModal Component
 * 
 * A reusable confirmation dialog for destructive or important actions
 * Supports different types (danger, warning, info, success) with appropriate styling
 * 
 * @example
 * <ConfirmationModal
 *   :visible="showDeleteConfirm"
 *   type="danger"
 *   title="Delete Content"
 *   message="Are you sure you want to delete this content? This action cannot be undone."
 *   :loading="deleting"
 *   @confirm="handleDelete"
 *   @cancel="showDeleteConfirm = false"
 * />
 */

const props = defineProps({
  /**
   * Whether the modal is visible
   */
  visible: {
    type: Boolean,
    default: false,
  },

  /**
   * Type of confirmation - affects styling and default icons
   */
  type: {
    type: String,
    default: 'danger',
    validator: value => ['danger', 'warning', 'info', 'success'].includes(value),
  },

  /**
   * Modal title
   */
  title: {
    type: String,
    required: true,
  },

  /**
   * Main confirmation message
   */
  message: {
    type: String,
    default: '',
  },

  /**
   * Additional details shown below the message
   */
  details: {
    type: String,
    default: '',
  },

  /**
   * Text for confirm button
   */
  confirmText: {
    type: String,
    default: 'Confirm',
  },

  /**
   * Text for cancel button
   */
  cancelText: {
    type: String,
    default: 'Cancel',
  },

  /**
   * Whether the action is loading
   */
  loading: {
    type: Boolean,
    default: false,
  },

  /**
   * Modal width
   */
  width: {
    type: [String, Number],
    default: 500,
  },

  /**
   * Custom icon (overrides type-based icon)
   */
  customIcon: {
    type: String,
    default: '',
  },

  /**
   * Custom confirm button icon
   */
  customConfirmIcon: {
    type: String,
    default: '',
  },

  /**
   * Whether modal can be closed by clicking outside or ESC
   */
  persistent: {
    type: Boolean,
    default: true,
  },
})

const emit = defineEmits([
  /**
   * Emitted when user confirms the action
   */
  'confirm',
  
  /**
   * Emitted when user cancels or closes the modal
   */
  'cancel',
  
  /**
   * Emitted when modal visibility should change
   */
  'update:visible',
])

// Computed properties for styling based on type
const typeConfig = computed(() => {
  const configs = {
    danger: {
      icon: 'fas fa-exclamation-triangle',
      iconColor: 'error',
      confirmColor: 'error',
      confirmVariant: 'flat',
      confirmIcon: 'fas fa-trash',
    },
    warning: {
      icon: 'fas fa-exclamation-circle',
      iconColor: 'warning',
      confirmColor: 'warning',
      confirmVariant: 'flat',
      confirmIcon: 'fas fa-check',
    },
    info: {
      icon: 'fas fa-info-circle',
      iconColor: 'info',
      confirmColor: 'info',
      confirmVariant: 'flat',
      confirmIcon: 'fas fa-check',
    },
    success: {
      icon: 'fas fa-check-circle',
      iconColor: 'success',
      confirmColor: 'success',
      confirmVariant: 'flat',
      confirmIcon: 'fas fa-check',
    },
  }
  
  return configs[props.type] || configs.danger
})

const icon = computed(() => props.customIcon || typeConfig.value.icon)
const iconColor = computed(() => typeConfig.value.iconColor)
const confirmColor = computed(() => typeConfig.value.confirmColor)
const confirmVariant = computed(() => typeConfig.value.confirmVariant)
const confirmIcon = computed(() => props.customConfirmIcon || typeConfig.value.confirmIcon)

// Methods
const handleConfirm = () => {
  emit('confirm')
}

const handleCancel = () => {
  emit('cancel')
  emit('update:visible', false)
}

const handleClose = value => {
  if (!value && !props.persistent) {
    handleCancel()
  }
}

// Prevent closing when loading
watch(() => props.loading, loading => {
  if (loading) {
    // Ensure modal stays open during loading
    return
  }
})
</script>

<style scoped>
.confirmation-modal {
  z-index: 9999;
}

.confirmation-modal :deep(.v-card) {
  overflow: visible;
}

.confirmation-modal :deep(.v-card-title) {
  border-block-end: 1px solid rgb(var(--v-theme-on-surface), 0.12);
}

.confirmation-modal :deep(.v-card-actions) {
  border-block-start: 1px solid rgb(var(--v-theme-on-surface), 0.12);
}

/* Loading state styles */
.confirmation-modal :deep(.v-btn--loading) {
  pointer-events: none;
}

/* Icon animations */
.confirmation-modal :deep(.v-icon) {
  transition: all 0.2s ease-in-out;
}

/* Responsive adjustments */
@media (max-width: 600px) {
  .confirmation-modal :deep(.v-dialog) {
    margin: 16px;
  }

  .confirmation-modal :deep(.v-card-title) {
    padding: 16px;
    font-size: 1.1rem;
  }

  .confirmation-modal :deep(.v-card-text) {
    padding: 16px;
  }

  .confirmation-modal :deep(.v-card-actions) {
    flex-direction: column;
    padding: 16px;
    gap: 8px;
  }

  .confirmation-modal :deep(.v-card-actions .v-btn) {
    inline-size: 100%;
  }
}

/* Dark mode adjustments */
.v-theme--dark .confirmation-modal :deep(.bg-grey-50) {
  background-color: rgb(var(--v-theme-surface-variant)) !important;
}
</style>
