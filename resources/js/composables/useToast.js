/**
 * Toast notification composable
 * Provides a simple toast interface that integrates with the existing flash notifications system
 */
import { useFlashNotifications } from './useFlashNotifications'

/**
 * Simple toast interface for displaying notifications
 * @returns {object} Toast methods
 */
export function useToast() {
  const { showSuccess, showError, showWarning, showInfo } = useFlashNotifications()

  /**
   * Toast object with convenience methods
   */
  const toast = {
    /**
     * Show success toast
     * @param {string} message - Success message to display
     * @param {object} options - Additional notification options
     */
    success: (message, options = {}) => {
      return showSuccess(message, {
        title: 'Success',
        ...options,
      })
    },

    /**
     * Show error toast
     * @param {string} message - Error message to display
     * @param {object} options - Additional notification options
     */
    error: (message, options = {}) => {
      return showError(message, {
        title: 'Error',
        ...options,
      })
    },

    /**
     * Show warning toast
     * @param {string} message - Warning message to display
     * @param {object} options - Additional notification options
     */
    warning: (message, options = {}) => {
      return showWarning(message, {
        title: 'Warning',
        ...options,
      })
    },

    /**
     * Show info toast
     * @param {string} message - Info message to display
     * @param {object} options - Additional notification options
     */
    info: (message, options = {}) => {
      return showInfo(message, {
        title: 'Info',
        ...options,
      })
    },

    /**
     * Show toast with custom type
     * @param {string} type - Toast type (success, error, warning, info)
     * @param {string} message - Message to display
     * @param {object} options - Additional notification options
     */
    show: (type, message, options = {}) => {
      const typeMap = {
        success: showSuccess,
        error: showError,
        warning: showWarning,
        info: showInfo,
      }

      const showFunction = typeMap[type] || showInfo
      
      return showFunction(message, options)
    },
  }

  return {
    toast,
  }
}
