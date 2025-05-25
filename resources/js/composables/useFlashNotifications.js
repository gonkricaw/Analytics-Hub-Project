import { ref } from 'vue'

// Global notification state
const notifications = ref([])
let notificationId = 0

// Notification interface
export function useFlashNotifications() {
  // Add notification
  const addNotification = (notification) => {
    const id = ++notificationId
    const newNotification = {
      id,
      type: 'success',
      title: '',
      message: '',
      timeout: 5000,
      persistent: false,
      show: true,
      ...notification,
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
  const removeNotification = (id) => {
    const index = notifications.value.findIndex(n => n.id === id)
    if (index > -1) {
      notifications.value.splice(index, 1)
    }
  }
  
  // Clear all notifications
  const clearNotifications = () => {
    notifications.value = []
  }
  
  // Convenience methods for different types
  const showSuccess = (message, options = {}) => {
    return addNotification({
      type: 'success',
      message,
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
      timeout: 7000, // Longer timeout for warnings
      ...options,
    })
  }
  
  const showInfo = (message, options = {}) => {
    return addNotification({
      type: 'info',
      message,
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
