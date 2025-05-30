<script setup>
import FlashNotification from '@/components/FlashNotification.vue'
import { useFlashNotifications } from '@/composables/useFlashNotifications.js'
import { computed } from 'vue'

const { notifications, removeNotification } = useFlashNotifications()

// Handle notification close
const handleNotificationClose = id => {
  removeNotification(id)
}

// Group notifications by position
const groupedNotifications = computed(() => {
  const groups = {
    'top-right': [],
    'top-left': [],
    'bottom-right': [],
    'bottom-left': [],
    'top-center': [],
  }
  
  notifications.value.forEach(notification => {
    const position = notification.position || 'top-right'

    groups[position].push(notification)
  })
  
  return groups
})
</script>

<template>
  <Teleport to="body">
    <!-- Container for each position -->
    <div 
      v-for="(notificationList, position) in groupedNotifications" 
      :key="position"
      class="flash-notifications-container"
      :class="`flash-notifications-container--${position}`"
    >
      <TransitionGroup
        :name="`stack-${position}`"
        tag="div"
        class="notification-stack"
      >
        <FlashNotification
          v-for="(notification, index) in notificationList"
          :key="notification.id"
          :show="notification.show"
          :type="notification.type"
          :title="notification.title"
          :message="notification.message"
          :timeout="notification.timeout"
          :persistent="notification.persistent"
          :position="position"
          :style="{
            '--stack-index': index,
            'z-index': 9999 - index
          }"
          @close="handleNotificationClose(notification.id)"
        />
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<style scoped>
.flash-notifications-container {
  position: fixed;
  z-index: 9999;
  pointer-events: none;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

/* Position-specific container styles */
.flash-notifications-container--top-right {
  top: 0;
  right: 0;
  align-items: flex-end;
}

.flash-notifications-container--top-left {
  top: 0;
  left: 0;
  align-items: flex-start;
}

.flash-notifications-container--bottom-right {
  bottom: 0;
  right: 0;
  align-items: flex-end;
  flex-direction: column-reverse;
}

.flash-notifications-container--bottom-left {
  bottom: 0;
  left: 0;
  align-items: flex-start;
  flex-direction: column-reverse;
}

.flash-notifications-container--top-center {
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  align-items: center;
}

.notification-stack {
  display: flex;
  flex-direction: inherit;
  gap: inherit;
  width: 100%;
}

.flash-notifications-container > *,
.notification-stack > * {
  pointer-events: auto;
}

/* Stack transition animations */
.stack-top-right-move,
.stack-top-left-move,
.stack-top-center-move {
  transition: transform var(--animation-duration-normal) var(--animation-easing-smooth);
}

.stack-bottom-right-move,
.stack-bottom-left-move {
  transition: transform var(--animation-duration-normal) var(--animation-easing-smooth);
}

.stack-top-right-enter-active,
.stack-top-left-enter-active,
.stack-top-center-enter-active,
.stack-bottom-right-enter-active,
.stack-bottom-left-enter-active {
  transition: all var(--animation-duration-normal) var(--animation-easing-smooth);
}

.stack-top-right-leave-active,
.stack-top-left-leave-active,
.stack-top-center-leave-active,
.stack-bottom-right-leave-active,
.stack-bottom-left-leave-active {
  transition: all var(--animation-duration-normal) var(--animation-easing-smooth);
}

.stack-top-right-enter-from,
.stack-top-right-leave-to {
  opacity: 0;
  transform: translateX(100%) scale(0.8);
}

.stack-top-left-enter-from,
.stack-top-left-leave-to {
  opacity: 0;
  transform: translateX(-100%) scale(0.8);
}

.stack-top-center-enter-from,
.stack-top-center-leave-to {
  opacity: 0;
  transform: translateY(-50px) scale(0.8);
}

.stack-bottom-right-enter-from,
.stack-bottom-right-leave-to {
  opacity: 0;
  transform: translateX(100%) scale(0.8);
}

.stack-bottom-left-enter-from,
.stack-bottom-left-leave-to {
  opacity: 0;
  transform: translateX(-100%) scale(0.8);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .flash-notifications-container {
    left: 16px !important;
    right: 16px !important;
    transform: none !important;
  }
  
  .flash-notifications-container--top-center {
    left: 16px;
    transform: none;
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .stack-top-right-move,
  .stack-top-left-move,
  .stack-top-center-move,
  .stack-bottom-right-move,
  .stack-bottom-left-move,
  .stack-top-right-enter-active,
  .stack-top-left-enter-active,
  .stack-top-center-enter-active,
  .stack-bottom-right-enter-active,
  .stack-bottom-left-enter-active,
  .stack-top-right-leave-active,
  .stack-top-left-leave-active,
  .stack-top-center-leave-active,
  .stack-bottom-right-leave-active,
  .stack-bottom-left-leave-active {
    transition: none !important;
  }
}
</style>
