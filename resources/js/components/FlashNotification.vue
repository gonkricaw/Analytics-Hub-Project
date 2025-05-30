<script setup>
import { computed, nextTick, onUnmounted, ref, watch } from 'vue'

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
  position: {
    type: String,
    default: 'top-right',
    validator: value => ['top-right', 'top-left', 'bottom-right', 'bottom-left', 'top-center'].includes(value),
  },
})

// Emits
const emit = defineEmits(['update:show', 'close'])

// State
const visible = ref(false)
const timeoutId = ref(null)
const progressRef = ref(null)
const isHovered = ref(false)

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

const positionClass = computed(() => {
  const positionMap = {
    'top-right': 'flash-notification--top-right',
    'top-left': 'flash-notification--top-left',
    'bottom-right': 'flash-notification--bottom-right',
    'bottom-left': 'flash-notification--bottom-left',
    'top-center': 'flash-notification--top-center',
  }

  
  return positionMap[props.position] || positionMap['top-right']
})

const transitionName = computed(() => {
  const transitionMap = {
    'top-right': 'slide-right',
    'top-left': 'slide-left',
    'bottom-right': 'slide-right',
    'bottom-left': 'slide-left',
    'top-center': 'fade-up',
  }

  
  return transitionMap[props.position] || 'slide-right'
})

// Watch for show prop changes
watch(() => props.show, newValue => {
  visible.value = newValue
  
  if (newValue && !props.persistent && props.timeout > 0) {
    startTimeout()
  }
}, { immediate: true })

// Handle mouse events for timeout pause
const handleMouseEnter = () => {
  isHovered.value = true
  if (timeoutId.value) {
    clearTimeout(timeoutId.value)
    timeoutId.value = null
  }
}

const handleMouseLeave = () => {
  isHovered.value = false
  if (!props.persistent && props.timeout > 0 && visible.value) {
    startTimeout()
  }
}

// Start timeout with progress animation
const startTimeout = () => {
  if (timeoutId.value) {
    clearTimeout(timeoutId.value)
  }
  
  timeoutId.value = setTimeout(() => {
    handleClose()
  }, props.timeout)
  
  // Animate progress bar if element exists
  nextTick(() => {
    if (progressRef.value) {
      progressRef.value.style.animation = `progress-bar ${props.timeout}ms linear`
    }
  })
}

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
    :name="transitionName"
    appear
    mode="out-in"
  >
    <div
      v-if="visible"
      class="flash-notification"
      :class="[positionClass, `flash-notification--${type}`]"
      @mouseenter="handleMouseEnter"
      @mouseleave="handleMouseLeave"
    >
      <VAlert
        :type="alertType"
        :color="alertColor"
        variant="elevated"
        closable
        class="flash-alert animate-in"
        :class="{ 'flash-alert--hovered': isHovered }"
        @click:close="handleClose"
      >
        <template #prepend>
          <VIcon 
            :icon="alertIcon" 
            class="flash-icon animate-pulse-subtle"
          />
        </template>
        
        <div class="flash-content">
          <div
            v-if="title"
            class="flash-title text-body-1 font-weight-medium mb-1"
          >
            {{ title }}
          </div>
          <div class="flash-message text-body-2">
            {{ message }}
          </div>
        </div>

        <!-- Progress bar for timed notifications -->
        <div 
          v-if="!persistent && timeout > 0 && !isHovered"
          ref="progressRef"
          class="flash-progress"
          :class="`flash-progress--${type}`"
        />
      </VAlert>
    </div>
  </Transition>
</template>

<style scoped>
/* Base notification styles */
.flash-notification {
  position: fixed;
  z-index: 9999;
  max-inline-size: 420px;
  min-inline-size: 320px;
  pointer-events: auto;
}

/* Position variants */
.flash-notification--top-right {
  inset-block-start: 24px;
  inset-inline-end: 24px;
}

.flash-notification--top-left {
  inset-block-start: 24px;
  inset-inline-start: 24px;
}

.flash-notification--bottom-right {
  inset-block-end: 24px;
  inset-inline-end: 24px;
}

.flash-notification--bottom-left {
  inset-block-end: 24px;
  inset-inline-start: 24px;
}

.flash-notification--top-center {
  inset-block-start: 24px;
  inset-inline-start: 50%;
  transform: translateX(-50%);
}

/* Alert component styling */
.flash-alert {
  box-shadow: 
    0 8px 32px rgba(0, 0, 0, 0.12),
    0 2px 8px rgba(0, 0, 0, 0.08) !important;
  border-radius: 12px !important;
  backdrop-filter: blur(8px);
  transition: 
    transform var(--animation-duration-normal) var(--animation-easing-smooth),
    box-shadow var(--animation-duration-normal) var(--animation-easing-smooth),
    opacity var(--animation-duration-fast) var(--animation-easing-smooth);
  will-change: transform, box-shadow, opacity;
}

.flash-alert--hovered {
  transform: translateY(-2px) scale(1.02);
  box-shadow: 
    0 12px 40px rgba(0, 0, 0, 0.16),
    0 4px 12px rgba(0, 0, 0, 0.12) !important;
}

/* Content styling */
.flash-content {
  flex: 1;
  min-width: 0;
}

.flash-title {
  line-height: 1.4;
  color: rgb(var(--v-theme-on-surface));
}

.flash-message {
  line-height: 1.5;
  color: rgba(var(--v-theme-on-surface), 0.87);
  word-wrap: break-word;
}

/* Icon animation */
.flash-icon {
  transition: transform var(--animation-duration-normal) var(--animation-easing-smooth);
}

.flash-notification--success .flash-icon {
  color: rgb(var(--v-theme-success)) !important;
}

.flash-notification--error .flash-icon {
  color: rgb(var(--v-theme-error)) !important;
}

.flash-notification--warning .flash-icon {
  color: rgb(var(--v-theme-warning)) !important;
}

.flash-notification--info .flash-icon {
  color: rgb(var(--v-theme-info)) !important;
}

/* Progress bar */
.flash-progress {
  position: absolute;
  bottom: 0;
  left: 0;
  height: 3px;
  width: 100%;
  border-radius: 0 0 12px 12px;
  opacity: 0.8;
  animation-fill-mode: forwards;
}

.flash-progress--success {
  background: linear-gradient(90deg, rgb(var(--v-theme-success)), rgba(var(--v-theme-success), 0.6));
}

.flash-progress--error {
  background: linear-gradient(90deg, rgb(var(--v-theme-error)), rgba(var(--v-theme-error), 0.6));
}

.flash-progress--warning {
  background: linear-gradient(90deg, rgb(var(--v-theme-warning)), rgba(var(--v-theme-warning), 0.6));
}

.flash-progress--info {
  background: linear-gradient(90deg, rgb(var(--v-theme-info)), rgba(var(--v-theme-info), 0.6));
}

/* Animation keyframes */
@keyframes progress-bar {
  from {
    width: 100%;
  }
  to {
    width: 0%;
  }
}

/* Slide right transitions */
.slide-right-enter-active,
.slide-right-leave-active {
  transition: all var(--animation-duration-normal) var(--animation-easing-smooth);
}

.slide-right-enter-from {
  opacity: 0;
  transform: translateX(100%) scale(0.95);
}

.slide-right-leave-to {
  opacity: 0;
  transform: translateX(100%) scale(0.95);
}

/* Slide left transitions */
.slide-left-enter-active,
.slide-left-leave-active {
  transition: all var(--animation-duration-normal) var(--animation-easing-smooth);
}

.slide-left-enter-from {
  opacity: 0;
  transform: translateX(-100%) scale(0.95);
}

.slide-left-leave-to {
  opacity: 0;
  transform: translateX(-100%) scale(0.95);
}

/* Fade up transitions */
.fade-up-enter-active,
.fade-up-leave-active {
  transition: all var(--animation-duration-normal) var(--animation-easing-smooth);
}

.fade-up-enter-from {
  opacity: 0;
  transform: translateY(-20px) scale(0.95);
}

.fade-up-leave-to {
  opacity: 0;
  transform: translateY(-20px) scale(0.95);
}

/* Utility classes from our animation system */
.animate-in {
  animation: fadeInUp var(--animation-duration-normal) var(--animation-easing-smooth);
}

.animate-pulse-subtle {
  animation: pulse 2s ease-in-out infinite;
  animation-duration: var(--animation-duration-slow);
}

/* Responsive styles */
@media (max-width: 768px) {
  .flash-notification {
    inset-inline: 16px !important;
    max-inline-size: none;
    min-inline-size: auto;
    transform: none !important;
  }
  
  .flash-notification--top-center {
    inset-inline-start: 16px;
  }
  
  .flash-notification--top-right,
  .flash-notification--top-left {
    inset-block-start: 16px;
  }
  
  .flash-notification--bottom-right,
  .flash-notification--bottom-left {
    inset-block-end: 16px;
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .flash-alert,
  .flash-icon,
  .slide-right-enter-active,
  .slide-right-leave-active,
  .slide-left-enter-active,
  .slide-left-leave-active,
  .fade-up-enter-active,
  .fade-up-leave-active {
    transition: none !important;
    animation: none !important;
  }
  
  .animate-pulse-subtle {
    animation: none !important;
  }
}

/* Dark mode adjustments */
@media (prefers-color-scheme: dark) {
  .flash-alert {
    backdrop-filter: blur(12px);
    box-shadow: 
      0 8px 32px rgba(0, 0, 0, 0.24),
      0 2px 8px rgba(0, 0, 0, 0.16) !important;
  }
  
  .flash-alert--hovered {
    box-shadow: 
      0 12px 40px rgba(0, 0, 0, 0.32),
      0 4px 12px rgba(0, 0, 0, 0.24) !important;
  }
}
</style>
