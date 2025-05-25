import { useAuth } from '@/composables/useAuth.js'
import { useFlashNotifications } from '@/composables/useFlashNotifications.js'
import { computed, onMounted, onUnmounted, ref } from 'vue'

// Global state
const lastActivity = ref(Date.now())
const isActive = ref(false)
const warningShown = ref(false)
let activityTimer = null
let warningTimer = null
let logoutTimer = null

// Configuration
const INACTIVITY_TIMEOUT = 15 * 60 * 1000 // 15 minutes in milliseconds
const WARNING_TIME = 2 * 60 * 1000 // Show warning 2 minutes before logout
const WARNING_TIMEOUT = INACTIVITY_TIMEOUT - WARNING_TIME

export function useAutoLogout() {
  const { logout, isAuthenticated } = useAuth()
  const { showWarning, showInfo } = useFlashNotifications()

  // Update last activity time
  const updateActivity = () => {
    lastActivity.value = Date.now()
    warningShown.value = false
    resetTimers()
  }

  // Reset all timers
  const resetTimers = () => {
    if (activityTimer) clearTimeout(activityTimer)
    if (warningTimer) clearTimeout(warningTimer)
    if (logoutTimer) clearTimeout(logoutTimer)

    if (isAuthenticated.value && isActive.value) {
      // Set warning timer
      warningTimer = setTimeout(() => {
        showWarning()
      }, WARNING_TIMEOUT)

      // Set logout timer
      logoutTimer = setTimeout(() => {
        performAutoLogout()
      }, INACTIVITY_TIMEOUT)
    }
  }

  // Show warning notification
  const showWarning = () => {
    if (!warningShown.value) {
      warningShown.value = true
      showWarning(
        'Your session will expire in 2 minutes due to inactivity. Move your mouse or click anywhere to stay logged in.',
        {
          title: 'Session Expiring Soon',
          timeout: 0,
          persistent: true,
        }
      )
    }
  }

  // Perform automatic logout
  const performAutoLogout = async () => {
    try {
      await logout()
      showInfo(
        'You have been automatically logged out due to inactivity.',
        {
          title: 'Session Expired',
          timeout: 8000,
        }
      )
    } catch (error) {
      console.error('Auto-logout error:', error)
    }
  }

  // Activity event listeners
  const activityEvents = [
    'mousedown',
    'mousemove',
    'keypress',
    'scroll',
    'touchstart',
    'click',
  ]

  // Add event listeners for activity detection
  const startTracking = () => {
    if (!isActive.value) {
      isActive.value = true
      
      activityEvents.forEach(event => {
        document.addEventListener(event, updateActivity, true)
      })

      updateActivity() // Initialize timers
    }
  }

  // Remove event listeners
  const stopTracking = () => {
    if (isActive.value) {
      isActive.value = false
      warningShown.value = false

      activityEvents.forEach(event => {
        document.removeEventListener(event, updateActivity, true)
      })

      if (activityTimer) clearTimeout(activityTimer)
      if (warningTimer) clearTimeout(warningTimer)
      if (logoutTimer) clearTimeout(logoutTimer)
    }
  }

  // Computed values
  const timeUntilLogout = computed(() => {
    const elapsed = Date.now() - lastActivity.value
    const remaining = INACTIVITY_TIMEOUT - elapsed
    return Math.max(0, remaining)
  })

  const timeUntilWarning = computed(() => {
    const elapsed = Date.now() - lastActivity.value
    const remaining = WARNING_TIMEOUT - elapsed
    return Math.max(0, remaining)
  })

  const isWarningActive = computed(() => {
    return timeUntilWarning.value === 0 && timeUntilLogout.value > 0
  })

  // Watch authentication state
  watch(isAuthenticated, (newValue) => {
    if (newValue) {
      startTracking()
    } else {
      stopTracking()
    }
  }, { immediate: true })

  // Lifecycle hooks
  onMounted(() => {
    if (isAuthenticated.value) {
      startTracking()
    }
  })

  onUnmounted(() => {
    stopTracking()
  })

  return {
    lastActivity: readonly(lastActivity),
    timeUntilLogout,
    timeUntilWarning,
    isWarningActive,
    isActive: readonly(isActive),
    updateActivity,
    startTracking,
    stopTracking,
    resetTimers,
  }
}

// Global instance for easy access
export const autoLogout = useAutoLogout()
