import { ref } from 'vue'

// Global error state
const globalErrors = ref([])
const isErrorReportingEnabled = ref(true)

export function useErrorHandler() {
  const addError = (error, context = {}) => {
    const errorObj = {
      id: Date.now() + Math.random(),
      message: error.message || 'An unknown error occurred',
      stack: error.stack,
      context,
      timestamp: new Date().toISOString(),
      type: error.name || 'Error',
    }
    
    globalErrors.value.push(errorObj)
    
    // Keep only last 10 errors
    if (globalErrors.value.length > 10) {
      globalErrors.value.shift()
    }
    
    // Log to console in development
    if (import.meta.env.DEV) {
      console.error('Error handled:', errorObj)
    }
    
    // Report to external service if available
    if (isErrorReportingEnabled.value && window.reportError) {
      window.reportError(error, context)
    }
    
    return errorObj.id
  }
  
  const removeError = id => {
    const index = globalErrors.value.findIndex(error => error.id === id)
    if (index > -1) {
      globalErrors.value.splice(index, 1)
    }
  }
  
  const clearErrors = () => {
    globalErrors.value.length = 0
  }
  
  const handleAsync = async (promise, context = {}) => {
    try {
      return await promise
    } catch (error) {
      addError(error, { ...context, type: 'async' })
      throw error // Re-throw to allow local handling
    }
  }
  
  const handleAxiosError = (error, context = {}) => {
    let message = 'Network error occurred'
    let details = {}
    
    if (error.response) {
      // Server responded with error status
      message = error.response.data?.message || `HTTP ${error.response.status} Error`
      details = {
        status: error.response.status,
        statusText: error.response.statusText,
        data: error.response.data,
        url: error.config?.url,
        method: error.config?.method,
      }
    } else if (error.request) {
      // Request was made but no response received
      message = 'No response from server'
      details = {
        url: error.config?.url,
        method: error.config?.method,
        timeout: error.code === 'ECONNABORTED',
      }
    } else {
      // Something else happened
      message = error.message || 'Request setup error'
    }
    
    const errorObj = {
      ...error,
      message,
      isAxiosError: true,
    }
    
    return addError(errorObj, { ...context, ...details, type: 'axios' })
  }
  
  const handleValidationError = (errors, context = {}) => {
    const message = 'Validation failed'

    const errorObj = {
      message,
      name: 'ValidationError',
      validationErrors: errors,
    }
    
    return addError(errorObj, { ...context, type: 'validation' })
  }
  
  const enableErrorReporting = () => {
    isErrorReportingEnabled.value = true
  }
  
  const disableErrorReporting = () => {
    isErrorReportingEnabled.value = false
  }
  
  return {
    globalErrors: readonly(globalErrors),
    addError,
    removeError,
    clearErrors,
    handleAsync,
    handleAxiosError,
    handleValidationError,
    enableErrorReporting,
    disableErrorReporting,
    isErrorReportingEnabled: readonly(isErrorReportingEnabled),
  }
}

// Global error handler setup
export function setupGlobalErrorHandler() {
  const { addError } = useErrorHandler()
  
  // Handle unhandled promise rejections
  window.addEventListener('unhandledrejection', event => {
    addError(event.reason, { type: 'unhandledPromise' })
  })
  
  // Handle uncaught errors
  window.addEventListener('error', event => {
    addError(event.error || new Error(event.message), {
      type: 'uncaughtError',
      filename: event.filename,
      lineno: event.lineno,
      colno: event.colno,
    })
  })
  
  // Vue error handler
  if (window.Vue) {
    const app = window.Vue.createApp ? window.Vue : null
    if (app && app.config) {
      app.config.errorHandler = (error, instance, info) => {
        addError(error, {
          type: 'vueError',
          componentInfo: info,
          componentName: instance?.$options?.name,
        })
      }
    }
  }
}
