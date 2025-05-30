import { computed, nextTick, onMounted, ref } from 'vue'

/**
 * Comprehensive accessibility utilities for the Indonet Analytics Hub
 * Provides focus management, keyboard navigation, screen reader support, and ARIA utilities
 */

export function useAccessibility() {
  // Focus management state
  const focusStack = ref([])
  const currentFocusedElement = ref(null)
  const trapFocus = ref(false)
  
  // Keyboard navigation state
  const navigationMode = ref('mouse') // 'mouse' | 'keyboard'
  const keyboardNavigationEnabled = ref(true)
  
  // Screen reader state
  const announcements = ref([])
  const liveRegion = ref(null)
  
  // High contrast and reduced motion preferences
  const prefersReducedMotion = ref(false)
  const prefersHighContrast = ref(false)
  const prefersDarkMode = ref(false)

  /**
   * Initialize accessibility utilities
   */
  const initAccessibility = () => {
    detectAccessibilityPreferences()
    setupKeyboardNavigation()
    setupFocusManagement()
    createLiveRegion()
    setupColorContrastSupport()
  }

  /**
   * Detect user accessibility preferences
   */
  const detectAccessibilityPreferences = () => {
    if (typeof window !== 'undefined' && window.matchMedia) {
      // Detect reduced motion preference
      try {
        const reducedMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)')

        prefersReducedMotion.value = reducedMotionQuery.matches
        reducedMotionQuery.addEventListener('change', e => {
          prefersReducedMotion.value = e.matches
          updateAnimationPreferences()
        })
      } catch (error) {
        console.warn('Could not detect reduced motion preference:', error)
      }

      // Detect high contrast preference
      try {
        const highContrastQuery = window.matchMedia('(prefers-contrast: high)')

        prefersHighContrast.value = highContrastQuery.matches
        highContrastQuery.addEventListener('change', e => {
          prefersHighContrast.value = e.matches
          updateContrastStyles()
        })
      } catch (error) {
        console.warn('Could not detect high contrast preference:', error)
      }

      // Detect color scheme preference
      try {
        const darkModeQuery = window.matchMedia('(prefers-color-scheme: dark)')

        prefersDarkMode.value = darkModeQuery.matches
        darkModeQuery.addEventListener('change', e => {
          prefersDarkMode.value = e.matches
        })
      } catch (error) {
        console.warn('Could not detect dark mode preference:', error)
      }
    }
  }

  /**
   * Setup keyboard navigation handling
   */
  const setupKeyboardNavigation = () => {
    if (typeof window !== 'undefined') {
      // Track navigation mode based on user input
      window.addEventListener('keydown', () => {
        navigationMode.value = 'keyboard'
        document.body.classList.add('keyboard-navigation')
        document.body.classList.remove('mouse-navigation')
      })

      window.addEventListener('mousedown', () => {
        navigationMode.value = 'mouse'
        document.body.classList.add('mouse-navigation')
        document.body.classList.remove('keyboard-navigation')
      })

      // Setup global keyboard shortcuts
      window.addEventListener('keydown', handleGlobalKeyboardShortcuts)
    }
  }

  /**
   * Handle global keyboard shortcuts
   */
  const handleGlobalKeyboardShortcuts = event => {
    // Skip links (Alt + 1)
    if (event.altKey && event.key === '1') {
      event.preventDefault()
      focusMainContent()
    }

    // Navigation menu (Alt + 2)
    if (event.altKey && event.key === '2') {
      event.preventDefault()
      focusNavigation()
    }

    // Search (Alt + 3)
    if (event.altKey && event.key === '3') {
      event.preventDefault()
      focusSearch()
    }

    // Escape key handling for modal/dialog closure
    if (event.key === 'Escape' && trapFocus.value) {
      handleEscapeKey()
    }
  }

  /**
   * Focus management utilities
   */
  const setupFocusManagement = () => {
    if (typeof window !== 'undefined') {
      // Track focus changes
      window.addEventListener('focusin', event => {
        currentFocusedElement.value = event.target
      })
    }
  }

  /**
   * Create ARIA live region for announcements
   */
  const createLiveRegion = () => {
    if (typeof window !== 'undefined' && !liveRegion.value) {
      liveRegion.value = document.createElement('div')
      liveRegion.value.setAttribute('aria-live', 'polite')
      liveRegion.value.setAttribute('aria-atomic', 'true')
      liveRegion.value.setAttribute('class', 'sr-only')
      liveRegion.value.style.cssText = `
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
        border: 0 !important;
      `
      document.body.appendChild(liveRegion.value)
    }
  }

  /**
   * Announce message to screen readers
   */
  const announce = (message, priority = 'polite') => {
    if (!message || typeof window === 'undefined') return

    announcements.value.push({
      id: Date.now(),
      message,
      priority,
      timestamp: new Date(),
    })

    if (liveRegion.value) {
      liveRegion.value.setAttribute('aria-live', priority)
      liveRegion.value.textContent = message
      
      // Clear after announcement
      setTimeout(() => {
        if (liveRegion.value) {
          liveRegion.value.textContent = ''
        }
      }, 100)
    }
  }

  /**
   * Focus trap utilities
   */
  const enableFocusTrap = container => {
    if (!container) return

    trapFocus.value = true
    focusStack.value.push(document.activeElement)

    const focusableElements = getFocusableElements(container)
    if (focusableElements.length > 0) {
      focusableElements[0].focus()
    }

    const handleTrapKeydown = event => {
      if (event.key === 'Tab') {
        const firstElement = focusableElements[0]
        const lastElement = focusableElements[focusableElements.length - 1]

        if (event.shiftKey) {
          if (document.activeElement === firstElement) {
            event.preventDefault()
            lastElement.focus()
          }
        } else {
          if (document.activeElement === lastElement) {
            event.preventDefault()
            firstElement.focus()
          }
        }
      }
    }

    container.addEventListener('keydown', handleTrapKeydown)
    container._focusTrapHandler = handleTrapKeydown
  }

  const disableFocusTrap = container => {
    trapFocus.value = false
    
    if (container && container._focusTrapHandler) {
      container.removeEventListener('keydown', container._focusTrapHandler)
      delete container._focusTrapHandler
    }

    // Restore focus to previous element
    const previousElement = focusStack.value.pop()
    if (previousElement && typeof previousElement.focus === 'function') {
      nextTick(() => {
        previousElement.focus()
      })
    }
  }

  /**
   * Get all focusable elements within a container
   */
  const getFocusableElements = container => {
    const focusableSelectors = [
      'a[href]',
      'button:not([disabled])',
      'input:not([disabled])',
      'select:not([disabled])',
      'textarea:not([disabled])',
      '[tabindex]:not([tabindex="-1"])',
      '[contenteditable="true"]',
    ].join(', ')

    return Array.from(container.querySelectorAll(focusableSelectors))
      .filter(element => isElementVisible(element))
  }

  /**
   * Check if element is visible and focusable
   */
  const isElementVisible = element => {
    const style = window.getComputedStyle(element)
    
    return (
      style.display !== 'none' &&
      style.visibility !== 'hidden' &&
      style.opacity !== '0' &&
      element.offsetWidth > 0 &&
      element.offsetHeight > 0
    )
  }

  /**
   * Skip link navigation functions
   */
  const focusMainContent = () => {
    const mainContent = document.getElementById('main-content') || 
                       document.querySelector('main') ||
                       document.querySelector('[role="main"]')

    if (mainContent) {
      mainContent.setAttribute('tabindex', '-1')
      mainContent.focus()
      announce('Skipped to main content')
    }
  }

  const focusNavigation = () => {
    const navigation = document.getElementById('navigation') ||
                      document.querySelector('nav') ||
                      document.querySelector('[role="navigation"]')

    if (navigation) {
      const firstLink = navigation.querySelector('a, button')
      if (firstLink) {
        firstLink.focus()
        announce('Skipped to navigation')
      }
    }
  }

  const focusSearch = () => {
    const searchInput = document.getElementById('search') ||
                       document.querySelector('input[type="search"]') ||
                       document.querySelector('input[placeholder*="search" i]')

    if (searchInput) {
      searchInput.focus()
      announce('Skipped to search')
    }
  }

  /**
   * Update animation preferences based on user settings
   */
  const updateAnimationPreferences = () => {
    if (typeof document !== 'undefined') {
      if (prefersReducedMotion.value) {
        document.body.classList.add('reduced-motion')
      } else {
        document.body.classList.remove('reduced-motion')
      }
    }
  }

  /**
   * Update contrast styles based on user settings
   */
  const updateContrastStyles = () => {
    if (typeof document !== 'undefined') {
      if (prefersHighContrast.value) {
        document.body.classList.add('high-contrast')
      } else {
        document.body.classList.remove('high-contrast')
      }
    }
  }

  /**
   * Setup color contrast support
   */
  const setupColorContrastSupport = () => {
    if (typeof document !== 'undefined') {
      updateAnimationPreferences()
      updateContrastStyles()
    }
  }

  /**
   * Handle escape key for modal/dialog closure
   */
  const handleEscapeKey = () => {
    // Emit escape event for components to handle
    window.dispatchEvent(new CustomEvent('accessibility:escape'))
  }

  /**
   * ARIA utilities
   */
  const generateId = (prefix = 'a11y') => {
    return `${prefix}-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`
  }

  const setAriaLabel = (element, label) => {
    if (element && label) {
      element.setAttribute('aria-label', label)
    }
  }

  const setAriaDescribedBy = (element, describedById) => {
    if (element && describedById) {
      element.setAttribute('aria-describedby', describedById)
    }
  }

  const setAriaExpanded = (element, expanded) => {
    if (element) {
      element.setAttribute('aria-expanded', expanded.toString())
    }
  }

  /**
   * Form accessibility utilities
   */
  const associateLabel = (inputId, labelText) => {
    const input = document.getElementById(inputId)
    if (input && labelText) {
      const labelId = `${inputId}-label`
      let label = document.getElementById(labelId)
      
      if (!label) {
        label = document.createElement('label')
        label.id = labelId
        label.setAttribute('for', inputId)
        label.textContent = labelText
        input.parentNode.insertBefore(label, input)
      }
      
      input.setAttribute('aria-labelledby', labelId)
    }
  }

  const setFormError = (inputId, errorMessage) => {
    const input = document.getElementById(inputId)
    if (input) {
      const errorId = `${inputId}-error`
      let errorElement = document.getElementById(errorId)
      
      if (errorMessage) {
        if (!errorElement) {
          errorElement = document.createElement('div')
          errorElement.id = errorId
          errorElement.setAttribute('role', 'alert')
          errorElement.className = 'error-message'
          input.parentNode.appendChild(errorElement)
        }
        
        errorElement.textContent = errorMessage
        input.setAttribute('aria-describedby', errorId)
        input.setAttribute('aria-invalid', 'true')
        announce(`Error: ${errorMessage}`, 'assertive')
      } else {
        if (errorElement) {
          errorElement.remove()
        }
        input.removeAttribute('aria-describedby')
        input.removeAttribute('aria-invalid')
      }
    }
  }

  /**
   * Color contrast utilities
   */
  const checkColorContrast = (foreground, background) => {
    // Simple contrast ratio calculation
    const getLuminance = hex => {
      const rgb = hex.substring(1).match(/.{2}/g).map(x => parseInt(x, 16) / 255)
      const [r, g, b] = rgb.map(x => x <= 0.03928 ? x / 12.92 : Math.pow((x + 0.055) / 1.055, 2.4))
      
      return 0.2126 * r + 0.7152 * g + 0.0722 * b
    }

    const contrast = (getLuminance(foreground) + 0.05) / (getLuminance(background) + 0.05)
    
    return contrast >= 4.5 // WCAG AA standard
  }

  /**
   * Computed accessibility state
   */
  const accessibilityFeatures = computed(() => ({
    reducedMotion: prefersReducedMotion.value,
    highContrast: prefersHighContrast.value,
    darkMode: prefersDarkMode.value,
    keyboardNavigation: navigationMode.value === 'keyboard',
    focusTrapped: trapFocus.value,
  }))

  const accessibilityClasses = computed(() => ({
    'reduced-motion': prefersReducedMotion.value,
    'high-contrast': prefersHighContrast.value,
    'keyboard-navigation': navigationMode.value === 'keyboard',
    'mouse-navigation': navigationMode.value === 'mouse',
    'focus-trapped': trapFocus.value,
    'focus-visible': true, // Always include focus-visible for better accessibility
  }))

  /**
   * Manual preference update for testing
   */
  const updatePreferences = preferences => {
    if (preferences.reducedMotion !== undefined) {
      prefersReducedMotion.value = preferences.reducedMotion
      updateAnimationPreferences()
    }
    if (preferences.highContrast !== undefined) {
      prefersHighContrast.value = preferences.highContrast
      updateContrastStyles()
    }
    if (preferences.darkMode !== undefined) {
      prefersDarkMode.value = preferences.darkMode
    }
  }

  // Initialize on mount
  onMounted(() => {
    initAccessibility()
  })

  return {
    // State
    focusStack,
    currentFocusedElement,
    navigationMode,
    announcements,
    accessibilityFeatures,
    accessibilityClasses,

    // Initialization
    initAccessibility,

    // Focus management
    enableFocusTrap,
    disableFocusTrap,
    getFocusableElements,
    isElementVisible,

    // Skip links
    focusMainContent,
    focusNavigation,
    focusSearch,

    // Screen reader
    announce,
    createLiveRegion,

    // ARIA utilities
    generateId,
    setAriaLabel,
    setAriaDescribedBy,
    setAriaExpanded,

    // Form accessibility
    associateLabel,
    setFormError,

    // Color contrast
    checkColorContrast,
    
    // Screen reader announcements (alias for announce)
    announceToScreenReader: announce,

    // Preferences
    prefersReducedMotion,
    prefersHighContrast,
    prefersDarkMode,
    updateAnimationPreferences,
    updateContrastStyles,
    updatePreferences,
  }
}
