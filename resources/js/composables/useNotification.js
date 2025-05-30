/**
 * Notification composable
 * Provides notification functionality that integrates with the existing flash notifications system
 */
import { useFlashNotifications } from './useFlashNotifications'

/**
 * Notification interface for displaying system notifications
 * @returns {object} Notification methods
 */
export function useNotification() {
  const { 
    showSuccess: flashSuccess, 
    showError: flashError, 
    showWarning: flashWarning, 
    showInfo: flashInfo, 
  } = useFlashNotifications()

  /**
   * Show notification with specified type
   * @param {string} message - Message to display
   * @param {string} type - Notification type ('success', 'error', 'warning', 'info')
   * @param {object} options - Additional notification options
   */
  const showNotification = (message, type = 'info', options = {}) => {
    const typeMap = {
      success: flashSuccess,
      error: flashError,
      warning: flashWarning,
      info: flashInfo,
    }

    const showFunction = typeMap[type] || flashInfo
    
    return showFunction(message, {
      title: type.charAt(0).toUpperCase() + type.slice(1),
      ...options,
    })
  }

  /**
   * Show success notification
   * @param {string} message - Success message to display
   * @param {object} options - Additional notification options
   */
  const showSuccess = (message, options = {}) => {
    return showNotification(message, 'success', options)
  }

  /**
   * Show error notification
   * @param {string} message - Error message to display
   * @param {object} options - Additional notification options
   */
  const showError = (message, options = {}) => {
    return showNotification(message, 'error', options)
  }

  /**
   * Show warning notification
   * @param {string} message - Warning message to display
   * @param {object} options - Additional notification options
   */
  const showWarning = (message, options = {}) => {
    return showNotification(message, 'warning', options)
  }

  /**
   * Show info notification
   * @param {string} message - Info message to display
   * @param {object} options - Additional notification options
   */
  const showInfo = (message, options = {}) => {
    return showNotification(message, 'info', options)
  }

  return {
    showNotification,
    showSuccess,
    showError,
    showWarning,
    showInfo,
  }
}
