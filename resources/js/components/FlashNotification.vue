<script setup>
import { computed, ref, watch } from 'vue'

// Props
const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  type: {
    type: String,
    default: 'success',
    validator: value => ['success', 'error', 'warning', 'info'].includes(value),
  },
  title: {
    type: String,
    default: '',
  },
  message: {
    type: String,
    required: true,
  },
  timeout: {
    type: Number,
    default: 5000,
  },
  persistent: {
    type: Boolean,
    default: false,
  },
})

// Emits
const emit = defineEmits(['update:show', 'close'])

// State
const visible = ref(false)
const timeoutId = ref(null)

// Computed
const alertType = computed(() => {
  const typeMap = {
    success: 'success',
    error: 'error',
    warning: 'warning',
    info: 'info',
  }

  
  return typeMap[props.type] || 'success'
})

const alertIcon = computed(() => {
  const iconMap = {
    success: 'tabler-circle-check',
    error: 'tabler-circle-x',
    warning: 'tabler-alert-triangle',
    info: 'tabler-info-circle',
  }

  
  return iconMap[props.type] || 'tabler-circle-check'
})

const alertColor = computed(() => {
  const colorMap = {
    success: 'success',
    error: 'error',
    warning: 'warning',
    info: 'info',
  }

  
  return colorMap[props.type] || 'success'
})

// Watch for show prop changes
watch(() => props.show, newValue => {
  visible.value = newValue
  
  if (newValue && !props.persistent && props.timeout > 0) {
    // Clear existing timeout
    if (timeoutId.value) {
      clearTimeout(timeoutId.value)
    }
    
    // Set new timeout
    timeoutId.value = setTimeout(() => {
      handleClose()
    }, props.timeout)
  }
}, { immediate: true })

// Handle close
const handleClose = () => {
  visible.value = false
  emit('update:show', false)
  emit('close')
  
  if (timeoutId.value) {
    clearTimeout(timeoutId.value)
    timeoutId.value = null
  }
}

// Cleanup on unmount
onUnmounted(() => {
  if (timeoutId.value) {
    clearTimeout(timeoutId.value)
  }
})
</script>

<template>
  <Transition
    name="flash-notification"
    appear
  >
    <div
      v-if="visible"
      class="flash-notification"
      :class="`flash-notification--${type}`"
    >
      <VAlert
        :type="alertType"
        :color="alertColor"
        variant="elevated"
        closable
        class="flash-alert"
        @click:close="handleClose"
      >
        <template #prepend>
          <VIcon :icon="alertIcon" />
        </template>
        
        <div>
          <div
            v-if="title"
            class="text-body-1 font-weight-medium mb-1"
          >
            {{ title }}
          </div>
          <div class="text-body-2">
            {{ message }}
          </div>
        </div>
      </VAlert>
    </div>
  </Transition>
</template>

<style scoped>
.flash-notification {
  position: fixed;
  z-index: 9999;
  inline-size: 100%;
  inset-block-start: 24px;
  inset-inline-end: 24px;
  max-inline-size: 400px;
}

.flash-alert {
  box-shadow: 0 8px 32px rgba(0, 0, 0, 12%) !important;
}

/* Animation styles */
.flash-notification-enter-active,
.flash-notification-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.flash-notification-enter-from {
  opacity: 0;
  transform: translateX(100%) scale(0.95);
}

.flash-notification-leave-to {
  opacity: 0;
  transform: translateX(100%) scale(0.95);
}

.flash-notification-enter-to,
.flash-notification-leave-from {
  opacity: 1;
  transform: translateX(0) scale(1);
}

/* Responsive styles */
@media (max-width: 768px) {
  .flash-notification {
    inset-block-start: 16px;
    inset-inline: 16px;
    max-inline-size: none;
  }
}
</style>
