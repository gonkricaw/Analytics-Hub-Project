<script setup>
import { useNotificationStore } from '@/stores/notificationStore'
import { onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const notificationStore = useNotificationStore()

// Initialize notifications when component mounts
onMounted(async () => {
  try {
    await notificationStore.fetchNotifications()

    // Start polling for new notifications every 30 seconds
    notificationStore.startPolling(30000)
  } catch (error) {
    if (import.meta.env.DEV) {
      console.error('Failed to load notifications:', error)
    }
  }
})

// Cleanup when component unmounts
onUnmounted(() => {
  notificationStore.stopPolling()
})

const removeNotification = async notificationId => {
  try {
    await notificationStore.removeNotification(notificationId)
  } catch (error) {
    if (import.meta.env.DEV) {
      console.error('Failed to remove notification:', error)
    }
  }
}

const markRead = async notificationIds => {
  try {
    // Mark multiple notifications as read
    for (const id of notificationIds) {
      await notificationStore.markAsRead(id)
    }
  } catch (error) {
    if (import.meta.env.DEV) {
      console.error('Failed to mark notifications as read:', error)
    }
  }
}

const markUnRead = async notificationIds => {
  try {
    // Mark multiple notifications as unread
    for (const id of notificationIds) {
      await notificationStore.markAsUnread(id)
    }
  } catch (error) {
    if (import.meta.env.DEV) {
      console.error('Failed to mark notifications as unread:', error)
    }
  }
}

const handleNotificationClick = async notification => {
  try {
    await notificationStore.handleNotificationClick(notification)
  } catch (error) {
    if (import.meta.env.DEV) {
      console.error('Failed to handle notification click:', error)
    }
  }
}

const handleViewAll = () => {
  router.push({ name: 'notifications' })
}
</script>

<template>
  <Notifications
    :notifications="notificationStore.formattedNotifications"
    @remove="removeNotification"
    @read="markRead"
    @unread="markUnRead"
    @click:notification="handleNotificationClick"
    @view-all="handleViewAll"
  />
</template>
