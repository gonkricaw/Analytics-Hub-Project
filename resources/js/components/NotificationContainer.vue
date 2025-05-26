<script setup>
import FlashNotification from '@/components/FlashNotification.vue'
import { useFlashNotifications } from '@/composables/useFlashNotifications.js'

const { notifications, removeNotification } = useFlashNotifications()

// Handle notification close
const handleNotificationClose = id => {
  removeNotification(id)
}
</script>

<template>
  <Teleport to="body">
    <div class="flash-notifications-container">
      <FlashNotification
        v-for="notification in notifications"
        :key="notification.id"
        :show="notification.show"
        :type="notification.type"
        :title="notification.title"
        :message="notification.message"
        :timeout="notification.timeout"
        :persistent="notification.persistent"
        @close="handleNotificationClose(notification.id)"
      />
    </div>
  </Teleport>
</template>

<style scoped>
.flash-notifications-container {
  position: fixed;
  z-index: 9999;
  inset-block-start: 0;
  inset-inline-end: 0;
  pointer-events: none;
}

.flash-notifications-container > * {
  margin-block-end: 12px;
  pointer-events: auto;
}
</style>
