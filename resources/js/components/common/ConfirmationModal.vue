<template>
  <VDialog
    :model-value="visible"
    :width="width"
    persistent
    class="confirmation-modal"
    transition="dialog-bottom-transition"
    @update:model-value="handleClose"
  >
    <VCard class="confirmation-card animate-in">
      <VCardTitle class="confirmation-header d-flex align-center gap-3 pa-6">
        <VIcon
          :icon="icon"
          :color="iconColor"
          size="28"
          class="confirmation-icon animate-pulse-subtle"
        />
        <span class="text-h5 font-weight-medium">{{ title }}</span>
      </VCardTitle>

      <VCardText class="confirmation-content pa-6 pt-2">
        <div
          v-if="message"
          class="text-body-1 confirmation-message"
        >
          {{ message }}
        </div>
        <slot
          v-else
          name="content"
        />
        
        <div
          v-if="details"
          class="confirmation-details mt-4 pa-3 bg-grey-50 rounded"
        >
          <div class="text-caption text-medium-emphasis">
            Details:
          </div>
          <div class="text-body-2">
            {{ details }}
          </div>
        </div>
      </VCardText>

      <VCardActions class="confirmation-actions pa-6 pt-2">
        <VSpacer />
        
        <VBtn
          variant="outlined"
          color="grey"
          :disabled="loading"
          class="confirmation-btn confirmation-btn--cancel hover-lift"
          @click="handleCancel"
        >
          <VIcon
            start
            :icon="getActionIcon('cancel')"
          />
          {{ cancelText }}
        </VBtn>

        <VBtn
          :variant="confirmVariant"
          :color="confirmColor"
          :loading="loading"
          class="confirmation-btn confirmation-btn--confirm hover-lift"
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
 * Enhanced with centralized animation system and improved UX
 * Updated with standardized icon system
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

import { useIconSystem } from '@/composables/useIconSystem.js'
import { computed } from 'vue'

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

// Icon system
const { getStatusIcon, getActionIcon } = useIconSystem()

// Computed properties for styling based on type
const typeConfig = computed(() => {
  const configs = {
    danger: {
      icon: getStatusIcon('error'),
      iconColor: 'error',
      confirmColor: 'error',
      confirmVariant: 'flat',
      confirmIcon: getActionIcon('delete'),
    },
    warning: {
      icon: getStatusIcon('warning'),
      iconColor: 'warning',
      confirmColor: 'warning',
      confirmVariant: 'flat',
      confirmIcon: getActionIcon('confirm'),
    },
    info: {
      icon: getStatusIcon('info'),
      iconColor: 'info',
      confirmColor: 'info',
      confirmVariant: 'flat',
      confirmIcon: getActionIcon('confirm'),
    },
    success: {
      icon: getStatusIcon('success'),
      iconColor: 'success',
      confirmColor: 'success',
      confirmVariant: 'flat',
      confirmIcon: getActionIcon('confirm'),
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
/* Base modal styles */
.confirmation-modal {
  z-index: 9999;
}

.confirmation-card {
  border-radius: 16px !important;
  box-shadow: 
    0 24px 64px rgba(0, 0, 0, 0.16),
    0 8px 32px rgba(0, 0, 0, 0.08) !important;
  backdrop-filter: blur(8px);
  overflow: visible !important;
  transform-origin: center;
  transition: transform var(--animation-duration-normal) var(--animation-easing-smooth);
}

/* Header styling */
.confirmation-header {
  border-block-end: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  background: linear-gradient(135deg, rgba(var(--v-theme-surface), 0.95), rgba(var(--v-theme-surface-variant), 0.3));
  border-radius: 16px 16px 0 0 !important;
}

.confirmation-icon {
  transition: transform var(--animation-duration-normal) var(--animation-easing-smooth);
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
}

/* Content styling */
.confirmation-content {
  line-height: 1.6;
}

.confirmation-message {
  color: rgba(var(--v-theme-on-surface), 0.87);
  font-weight: 400;
}

.confirmation-details {
  border: 1px solid rgba(var(--v-theme-on-surface), 0.08);
  transition: 
    background-color var(--animation-duration-fast) var(--animation-easing-smooth),
    border-color var(--animation-duration-fast) var(--animation-easing-smooth);
}

/* Actions styling */
.confirmation-actions {
  border-block-start: 1px solid rgba(var(--v-theme-on-surface), 0.12);
  background: linear-gradient(135deg, rgba(var(--v-theme-surface-variant), 0.3), rgba(var(--v-theme-surface), 0.95));
  border-radius: 0 0 16px 16px !important;
  gap: 12px;
}

.confirmation-btn {
  border-radius: 10px !important;
  font-weight: 500;
  text-transform: none;
  letter-spacing: 0.25px;
  min-width: 100px;
  transition: 
    transform var(--animation-duration-fast) var(--animation-easing-smooth),
    box-shadow var(--animation-duration-fast) var(--animation-easing-smooth),
    background-color var(--animation-duration-fast) var(--animation-easing-smooth);
}

.confirmation-btn--cancel {
  border: 2px solid rgba(var(--v-theme-on-surface), 0.23) !important;
}

.confirmation-btn--cancel:hover {
  border-color: rgba(var(--v-theme-on-surface), 0.4) !important;
  background-color: rgba(var(--v-theme-on-surface), 0.04) !important;
}

.confirmation-btn--confirm {
  box-shadow: 0 4px 12px rgba(var(--v-theme-primary), 0.3);
}

.confirmation-btn--confirm:hover {
  box-shadow: 0 6px 16px rgba(var(--v-theme-primary), 0.4);
}

/* Loading state styles */
.confirmation-btn--loading {
  pointer-events: none;
}

/* Animation classes from our centralized system */
.animate-in {
  animation: fadeInUp var(--animation-duration-normal) var(--animation-easing-smooth);
}

.animate-pulse-subtle {
  animation: pulse var(--animation-duration-slow) ease-in-out infinite;
}

.hover-lift:hover {
  transform: translateY(-2px);
}

/* Responsive styles */
@media (max-width: 600px) {
  .confirmation-modal :deep(.v-dialog) {
    margin: 16px;
  }

  .confirmation-header {
    padding: 20px !important;
    font-size: 1.1rem;
  }

  .confirmation-content {
    padding: 20px !important;
  }

  .confirmation-actions {
    flex-direction: column;
    padding: 20px !important;
    gap: 12px;
  }

  .confirmation-btn {
    width: 100% !important;
    min-height: 48px;
  }
}

/* Dark mode adjustments */
.v-theme--dark .confirmation-card {
  box-shadow: 
    0 24px 64px rgba(0, 0, 0, 0.4),
    0 8px 32px rgba(0, 0, 0, 0.2) !important;
  backdrop-filter: blur(12px);
}

.v-theme--dark .confirmation-details,
.v-theme--dark .confirmation-modal :deep(.bg-grey-50) {
  background-color: rgba(var(--v-theme-surface-variant), 0.7) !important;
  border-color: rgba(var(--v-theme-on-surface), 0.15);
}

.v-theme--dark .confirmation-header {
  background: linear-gradient(135deg, rgba(var(--v-theme-surface), 0.98), rgba(var(--v-theme-surface-variant), 0.5));
}

.v-theme--dark .confirmation-actions {
  background: linear-gradient(135deg, rgba(var(--v-theme-surface-variant), 0.5), rgba(var(--v-theme-surface), 0.98));
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .confirmation-card,
  .confirmation-icon,
  .confirmation-details,
  .confirmation-btn,
  .animate-in,
  .animate-pulse-subtle,
  .hover-lift {
    transition: none !important;
    animation: none !important;
    transform: none !important;
  }
}

/* Focus states for accessibility */
.confirmation-btn:focus-visible {
  outline: 2px solid rgb(var(--v-theme-primary));
  outline-offset: 2px;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  .confirmation-header,
  .confirmation-actions {
    border-color: rgba(var(--v-theme-on-surface), 0.4);
  }
  
  .confirmation-btn--cancel {
    border-color: rgba(var(--v-theme-on-surface), 0.6) !important;
  }
}
</style>
