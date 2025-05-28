import { readonly, ref } from 'vue'

// Global notification state
const notifications = ref([])
let notificationId = 0

// Notification interface
export function useFlashNotifications() {
  // Add notification
  const addNotification = notification => {
    const id = ++notificationId

    const newNotification = {
      id,
      type: 'success',
      title: '',
      message: '',
      timeout: 5000,
      persistent: false,
      position: 'top-right',
      show: true,
      ...notification,
    }
    
    // Limit notifications per position to prevent overflow
    const maxNotificationsPerPosition = 5
    const samePositionNotifications = notifications.value.filter(n => n.position === newNotification.position)
    
    if (samePositionNotifications.length >= maxNotificationsPerPosition) {
      // Remove oldest notification of same position
      const oldestId = samePositionNotifications[0].id

      removeNotification(oldestId)
    }
    
    notifications.value.push(newNotification)
    
    // Auto-remove after timeout (if not persistent)
    if (!newNotification.persistent && newNotification.timeout > 0) {
      setTimeout(() => {
        removeNotification(id)
      }, newNotification.timeout)
    }
    
    return id
  }
  
  // Remove notification
  const removeNotification = id => {
    const index = notifications.value.findIndex(n => n.id === id)
    if (index > -1) {
      notifications.value.splice(index, 1)
    }
  }
  
  // Clear all notifications
  const clearNotifications = () => {
    notifications.value = []
  }
  
  // Clear notifications by position
  const clearNotificationsByPosition = position => {
    notifications.value = notifications.value.filter(n => n.position !== position)
  }
  
  // Clear notifications by type
  const clearNotificationsByType = type => {
    notifications.value = notifications.value.filter(n => n.type !== type)
  }
  
  // Get notification count by position
  const getNotificationCountByPosition = position => {
    return notifications.value.filter(n => n.position === position).length
  }
  
  // Convenience methods for different types
  const showSuccess = (message, options = {}) => {
    return addNotification({
      type: 'success',
      message,
      timeout: 4000,
      ...options,
    })
  }
  
  const showError = (message, options = {}) => {
    return addNotification({
      type: 'error',
      message,
      timeout: 0, // Don't auto-hide errors by default
      persistent: true,
      ...options,
    })
  }
  
  const showWarning = (message, options = {}) => {
    return addNotification({
      type: 'warning',
      message,
      timeout: 6000, // Longer timeout for warnings
      ...options,
    })
  }
  
  const showInfo = (message, options = {}) => {
    return addNotification({
      type: 'info',
      message,
      timeout: 5000,
      ...options,
    })
  }
  
  // Convenience methods for different positions
  const showTopRight = (message, options = {}) => {
    return addNotification({
      message,
      position: 'top-right',
      ...options,
    })
  }
  
  const showTopLeft = (message, options = {}) => {
    return addNotification({
      message,
      position: 'top-left',
      ...options,
    })
  }
  
  const showBottomRight = (message, options = {}) => {
    return addNotification({
      message,
      position: 'bottom-right',
      ...options,
    })
  }
  
  const showBottomLeft = (message, options = {}) => {
    return addNotification({
      message,
      position: 'bottom-left',
      ...options,
    })
  }
  
  const showTopCenter = (message, options = {}) => {
    return addNotification({
      message,
      position: 'top-center',
      ...options,
    })
  }
  
  return {
    notifications: readonly(notifications),
    addNotification,
    removeNotification,
    clearNotifications,
    showSuccess,
    showError,
    showWarning,
    showInfo,
  }
}

// Global instance for easy access
export const flashNotifications = useFlashNotifications()
