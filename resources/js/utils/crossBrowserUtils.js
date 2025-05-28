// =====================================================================================
// Cross-Browser Compatibility Utilities
// =====================================================================================

/**
 * Browser feature detection and polyfill support
 * This file provides utilities for cross-browser compatibility
 */

// =====================================================================================
// Feature Detection
// =====================================================================================

/**
 * Check if a CSS feature is supported
 * @param {string} property - CSS property to check
 * @param {string} value - CSS value to check
 * @returns {boolean} - Whether the feature is supported
 */
export function supportsCSS(property, value) {
  if (typeof CSS !== 'undefined' && CSS.supports) {
    return CSS.supports(property, value)
  }
  
  // Fallback for older browsers
  const element = document.createElement('div')
  try {
    element.style[property] = value
    
    return element.style[property] === value
  } catch (e) {
    return false
  }
}

/**
 * Check if container queries are supported
 * @returns {boolean}
 */
export function supportsContainerQueries() {
  return supportsCSS('container-type', 'inline-size')
}

/**
 * Check if CSS Grid is supported
 * @returns {boolean}
 */
export function supportsGrid() {
  return supportsCSS('display', 'grid')
}

/**
 * Check if CSS Flexbox is supported
 * @returns {boolean}
 */
export function supportsFlexbox() {
  return supportsCSS('display', 'flex')
}

/**
 * Check if CSS custom properties are supported
 * @returns {boolean}
 */
export function supportsCustomProperties() {
  return supportsCSS('--test', 'test')
}

/**
 * Check if aspect-ratio is supported
 * @returns {boolean}
 */
export function supportsAspectRatio() {
  return supportsCSS('aspect-ratio', '1/1')
}

/**
 * Check if Intersection Observer API is supported
 * @returns {boolean}
 */
export function supportsIntersectionObserver() {
  return typeof window !== 'undefined' && 'IntersectionObserver' in window
}

/**
 * Check if Resize Observer API is supported
 * @returns {boolean}
 */
export function supportsResizeObserver() {
  return typeof window !== 'undefined' && 'ResizeObserver' in window
}

/**
 * Check if Web Workers are supported
 * @returns {boolean}
 */
export function supportsWebWorkers() {
  return typeof window !== 'undefined' && 'Worker' in window
}

/**
 * Check if Service Workers are supported
 * @returns {boolean}
 */
export function supportsServiceWorkers() {
  return typeof window !== 'undefined' && 'serviceWorker' in navigator
}

// =====================================================================================
// Browser Detection
// =====================================================================================

/**
 * Detect browser information
 * @returns {object} Browser information
 */
export function getBrowserInfo() {
  if (typeof navigator === 'undefined' || !navigator.userAgent) {
    return {
      isChrome: false,
      isFirefox: false,
      isSafari: false,
      isEdge: false,
      isIE: false,
      isOpera: false,
      isMobile: false,
      isTablet: false,
      userAgent: 'unknown',
    }
  }
  
  const userAgent = navigator.userAgent.toLowerCase()
  
  return {
    isChrome: isChrome(),
    isFirefox: isFirefox(),
    isSafari: isSafari(),
    isEdge: isEdge(),
    isIE: isIE(),
    isOpera: isOpera(),
    isMobile: /android|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(userAgent),
    isTablet: /ipad|android(?!.*mobile)|tablet/i.test(userAgent),
    userAgent,
  }
}

/**
 * Check if browser is Chrome
 * @returns {boolean}
 */
export function isChrome() {
  if (typeof navigator === 'undefined') return false
  const userAgent = navigator.userAgent.toLowerCase()
  
  return /chrome/.test(userAgent) && !/edge/.test(userAgent)
}

/**
 * Check if browser is Firefox
 * @returns {boolean}
 */
export function isFirefox() {
  if (typeof navigator === 'undefined') return false
  const userAgent = navigator.userAgent.toLowerCase()
  
  return /firefox/.test(userAgent)
}

/**
 * Check if browser is Safari
 * @returns {boolean}
 */
export function isSafari() {
  if (typeof navigator === 'undefined') return false
  const userAgent = navigator.userAgent.toLowerCase()
  
  return /safari/.test(userAgent) && !/chrome/.test(userAgent)
}

/**
 * Check if browser is Edge
 * @returns {boolean}
 */
export function isEdge() {
  if (typeof navigator === 'undefined') return false
  const userAgent = navigator.userAgent.toLowerCase()
  
  return /edge/.test(userAgent) || /edg/.test(userAgent)
}

/**
 * Check if browser is Internet Explorer
 * @returns {boolean}
 */
export function isIE() {
  if (typeof navigator === 'undefined') return false
  const userAgent = navigator.userAgent.toLowerCase()
  
  return /msie|trident/.test(userAgent)
}

/**
 * Check if browser is Opera
 * @returns {boolean}
 */
export function isOpera() {
  if (typeof navigator === 'undefined') return false
  const userAgent = navigator.userAgent.toLowerCase()
  
  return /opera|opr/.test(userAgent)
}

/**
 * Check if device has touch capabilities
 * @returns {boolean}
 */
export function isTouchDevice() {
  if (typeof window === 'undefined') return false
  
  return 'ontouchstart' in window || navigator.maxTouchPoints > 0
}

// =====================================================================================
// Viewport Utilities
// =====================================================================================

/**
 * Get viewport dimensions with cross-browser compatibility
 * @returns {object} Viewport dimensions
 */
export function getViewportSize() {
  return {
    width: Math.max(
      document.documentElement.clientWidth || 0,
      window.innerWidth || 0,
    ),
    height: Math.max(
      document.documentElement.clientHeight || 0,
      window.innerHeight || 0,
    ),
  }
}

/**
 * Check if viewport is mobile size
 * @returns {boolean}
 */
export function isMobileViewport() {
  const { width } = getViewportSize()
  
  return width < 600
}

/**
 * Check if viewport is tablet size
 * @returns {boolean}
 */
export function isTabletViewport() {
  const { width } = getViewportSize()
  
  return width >= 600 && width < 960
}

/**
 * Check if viewport is desktop size
 * @returns {boolean}
 */
export function isDesktopViewport() {
  const { width } = getViewportSize()
  
  return width >= 960
}

// =====================================================================================
// Polyfill Utilities
// =====================================================================================

/**
 * Polyfill for ResizeObserver
 */
export function polyfillResizeObserver() {
  if (typeof ResizeObserver === 'undefined') {
    // Simple fallback using window resize
    window.ResizeObserver = class ResizeObserver {
      constructor(callback) {
        this.callback = callback
        this.elements = new Set()
        this.handleResize = () => {
          this.elements.forEach(element => {
            this.callback([{
              target: element,
              contentRect: element.getBoundingClientRect(),
            }])
          })
        }
      }

      observe(element) {
        if (this.elements.size === 0) {
          window.addEventListener('resize', this.handleResize)
        }
        this.elements.add(element)
      }

      unobserve(element) {
        this.elements.delete(element)
        if (this.elements.size === 0) {
          window.removeEventListener('resize', this.handleResize)
        }
      }

      disconnect() {
        window.removeEventListener('resize', this.handleResize)
        this.elements.clear()
      }
    }
  }
}

/**
 * Polyfill for IntersectionObserver
 */
export function polyfillIntersectionObserver() {
  if (typeof IntersectionObserver === 'undefined') {
    // Basic polyfill - very simplified
    window.IntersectionObserver = class IntersectionObserver {
      constructor(callback, options = {}) {
        this.callback = callback
        this.options = options
        this.elements = new Set()
        this.handleScroll = () => {
          this.elements.forEach(element => {
            const rect = element.getBoundingClientRect()
            const isIntersecting = rect.top < window.innerHeight && rect.bottom > 0

            this.callback([{
              target: element,
              isIntersecting,
              intersectionRatio: isIntersecting ? 1 : 0,
            }])
          })
        }
      }

      observe(element) {
        if (this.elements.size === 0) {
          window.addEventListener('scroll', this.handleScroll)
          window.addEventListener('resize', this.handleScroll)
        }
        this.elements.add(element)
        this.handleScroll() // Initial check
      }

      unobserve(element) {
        this.elements.delete(element)
        if (this.elements.size === 0) {
          window.removeEventListener('scroll', this.handleScroll)
          window.removeEventListener('resize', this.handleScroll)
        }
      }

      disconnect() {
        window.removeEventListener('scroll', this.handleScroll)
        window.removeEventListener('resize', this.handleScroll)
        this.elements.clear()
      }
    }
  }
}

// =====================================================================================
// Touch Support
// =====================================================================================

/**
 * Check if device supports touch
 * @returns {boolean}
 */
export function supportsTouchEvents() {
  return 'ontouchstart' in window || navigator.maxTouchPoints > 0
}

/**
 * Add touch-friendly classes to body
 */
export function addTouchClasses() {
  const body = document.body
  
  if (supportsTouchEvents()) {
    body.classList.add('touch-device')
  } else {
    body.classList.add('no-touch')
  }

  // Add hover support class
  if (window.matchMedia('(hover: hover)').matches) {
    body.classList.add('hover-supported')
  } else {
    body.classList.add('hover-none')
  }
}

// =====================================================================================
// CSS Custom Properties Fallback
// =====================================================================================

/**
 * Set CSS custom property with fallback
 * @param {HTMLElement} element - Target element
 * @param {string} property - CSS custom property name
 * @param {string} value - Property value
 * @param {string} fallback - Fallback value for older browsers
 */
export function setCSSProperty(element, property, value, fallback = null) {
  if (supportsCustomProperties()) {
    element.style.setProperty(property, value)
  } else if (fallback) {
    // Apply fallback for older browsers
    const propertyName = property.replace(/^--/, '').replace(/-([a-z])/g, g => g[1].toUpperCase())

    element.style[propertyName] = fallback
  }
}

// =====================================================================================
// Responsive Image Loading
// =====================================================================================

/**
 * Lazy load images with IntersectionObserver
 * @param {string} selector - Image selector
 */
export function lazyLoadImages(selector = 'img[data-src]') {
  const images = document.querySelectorAll(selector)
  
  if (!images.length) return

  const imageObserver = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target

        img.src = img.dataset.src
        img.classList.remove('lazy')
        img.classList.add('lazy-loaded')
        imageObserver.unobserve(img)
      }
    })
  })

  images.forEach(img => imageObserver.observe(img))
}

// =====================================================================================
// Focus Management
// =====================================================================================

/**
 * Improve focus visibility for keyboard navigation
 */
export function enhanceFocusVisibility() {
  let hadKeyboardEvent = false

  // Listen for keyboard events
  document.addEventListener('keydown', e => {
    if (e.key === 'Tab') {
      hadKeyboardEvent = true
    }
  })

  // Listen for mouse events
  document.addEventListener('mousedown', () => {
    hadKeyboardEvent = false
  })

  // Apply focus styles only for keyboard navigation
  document.addEventListener('focusin', e => {
    if (hadKeyboardEvent) {
      e.target.classList.add('keyboard-focus')
    }
  })

  document.addEventListener('focusout', e => {
    e.target.classList.remove('keyboard-focus')
  })
}

// =====================================================================================
// Initialization
// =====================================================================================

/**
 * Initialize cross-browser compatibility utilities
 */
export function initCrossBrowserUtils() {
  // Add browser info to body classes
  const browserInfo = getBrowserInfo()
  const body = document.body

  Object.keys(browserInfo).forEach(key => {
    if (browserInfo[key] === true) {
      body.classList.add(key.replace(/^is/, '').toLowerCase())
    }
  })

  // Add feature detection classes
  const features = {
    'supports-grid': supportsGrid(),
    'supports-custom-properties': supportsCustomProperties(),
    'supports-container-queries': supportsContainerQueries(),
    'supports-aspect-ratio': supportsAspectRatio(),
  }

  Object.keys(features).forEach(feature => {
    if (features[feature]) {
      body.classList.add(feature)
    } else {
      body.classList.add(`no-${feature}`)
    }
  })

  // Initialize polyfills
  polyfillResizeObserver()
  polyfillIntersectionObserver()

  // Add touch classes
  addTouchClasses()

  // Enhance focus visibility
  enhanceFocusVisibility()

  // Initialize lazy loading
  lazyLoadImages()
}

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initCrossBrowserUtils)
}

// Auto-initialize only in browser environment (not in tests)
if (typeof window !== 'undefined' && typeof document !== 'undefined' && !globalThis.vitest) {
  initCrossBrowserUtils()
}

// =====================================================================================
// Main Cross-Browser Utilities Object
// =====================================================================================

/**
 * Detect browser features and capabilities
 * @returns {Object} Object containing feature detection results
 */
export function detectFeatures() {
  return {
    // CSS Features
    grid: supportsGrid(),
    flexbox: supportsFlexbox(),
    containerQueries: supportsContainerQueries(),
    customProperties: supportsCustomProperties(),
    
    // JavaScript Features
    intersectionObserver: supportsIntersectionObserver(),
    resizeObserver: supportsResizeObserver(),
    webWorkers: supportsWebWorkers(),
    serviceWorkers: supportsServiceWorkers(),
    
    // Input Features
    touch: isTouchDevice(),
    
    // Browser Information
    isChrome: isChrome(),
    isFirefox: isFirefox(),
    isSafari: isSafari(),
    isEdge: isEdge(),
    
    // Performance Features
    performanceObserver: typeof PerformanceObserver !== 'undefined',
    webGL: checkWebGLSupport(),
    
    // Accessibility Features
    reducedMotion: prefersReducedMotion(),
    highContrast: prefersHighContrast(),
  }
}

/**
 * Load required polyfills based on feature detection
 * @param {Object} options - Polyfill configuration options
 * @returns {Promise} Promise that resolves when polyfills are loaded
 */
export async function loadPolyfills(options = {}) {
  const features = detectFeatures()
  const polyfillsToLoad = []
  
  // Intersection Observer polyfill
  if (!features.intersectionObserver && options.intersectionObserver !== false) {
    polyfillsToLoad.push(polyfillIntersectionObserver())
  }
  
  // Resize Observer polyfill
  if (!features.resizeObserver && options.resizeObserver !== false) {
    polyfillsToLoad.push(polyfillResizeObserver())
  }
  
  // Custom Elements polyfill for older browsers
  if (typeof customElements === 'undefined' && options.customElements !== false) {
    polyfillsToLoad.push(loadCustomElementsPolyfill())
  }
  
  // Fetch polyfill for older browsers
  if (typeof fetch === 'undefined' && options.fetch !== false) {
    polyfillsToLoad.push(loadFetchPolyfill())
  }
  
  return Promise.all(polyfillsToLoad)
}

/**
 * Implement graceful degradation strategies
 * @param {string} feature - Feature to check
 * @param {Object} options - Degradation options
 */
export function gracefulDegradation(feature, options = {}) {
  const features = detectFeatures()
  
  if (!features[feature]) {
    if (options.fallback) {
      options.fallback()
    }
    
    if (options.addClass) {
      document.body.classList.add(`no-${feature}`)
    }
    
    if (options.message) {
      if (import.meta.env.DEV) {
        console.warn(`Feature '${feature}' not supported. ${options.message}`)
      }
    }
    
    return false
  }
  
  if (options.onSupported) {
    options.onSupported()
  }
  
  return true
}

/**
 * Check and enable modern features with fallbacks
 * @param {Object} features - Features to check and enable
 */
export function modernFeatures(features = {}) {
  const supportedFeatures = detectFeatures()
  const results = {}
  
  Object.keys(features).forEach(featureName => {
    const featureConfig = features[featureName]
    const isSupported = supportedFeatures[featureName]
    
    results[featureName] = {
      supported: isSupported,
      enabled: false,
    }
    
    if (isSupported) {
      if (featureConfig.enable) {
        featureConfig.enable()
        results[featureName].enabled = true
      }
    } else {
      if (featureConfig.fallback) {
        featureConfig.fallback()
      }
    }
  })
  
  return results
}

// Helper functions for polyfills

/**
 * Load Custom Elements polyfill
 */
async function loadCustomElementsPolyfill() {
  if (typeof customElements === 'undefined') {
    try {
      await import('https://unpkg.com/@webcomponents/custom-elements@1.4.3/custom-elements.min.js')
    } catch (error) {
      if (import.meta.env.DEV) {
        console.warn('Failed to load Custom Elements polyfill:', error)
      }
    }
  }
}

/**
 * Load Fetch polyfill
 */
async function loadFetchPolyfill() {
  if (typeof fetch === 'undefined') {
    try {
      await import('https://unpkg.com/whatwg-fetch@3.6.2/fetch.js')
    } catch (error) {
      if (import.meta.env.DEV) {
        console.warn('Failed to load Fetch polyfill:', error)
      }
    }
  }
}

// Main cross-browser utilities object
export const crossBrowserUtils = {
  // Feature Detection
  detectFeatures,
  supportsCSS,
  supportsGrid,
  supportsFlexbox,
  supportsContainerQueries,
  supportsCustomProperties,
  supportsIntersectionObserver,
  supportsResizeObserver,
  supportsWebWorkers,
  supportsServiceWorkers,
  
  // Browser Detection
  getBrowserInfo,
  isChrome,
  isFirefox,
  isSafari,
  isEdge,
  isTouchDevice,
  
  // Polyfills and Compatibility
  loadPolyfills,
  gracefulDegradation,
  modernFeatures,
  polyfillIntersectionObserver,
  polyfillResizeObserver,
  
  // Utilities
  addTouchClasses,
  enhanceFocusVisibility,
  lazyLoadImages,
  initCrossBrowserUtils,
}

export default crossBrowserUtils
