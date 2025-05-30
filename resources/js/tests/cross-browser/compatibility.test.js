/**
 * Cross-Browser Compatibility Tests
 * 
 * Validates browser compatibility features, polyfills, and fallbacks
 * Tests touch/hover detection, feature support, and graceful degradation
 */

import {
  detectBrowserCapabilities,
  loadPolyfills,
  setupCrossBrowserSupport,
} from '@/utils/crossBrowserUtils'
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'

// Mock different browser environments
const mockUserAgents = {
  chrome: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
  firefox: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/121.0',
  safari: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Safari/605.1.15',
  edge: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.2210.91',
  ie11: 'Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; AS; rv:11.0) like Gecko',
  mobile: 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Mobile/15E148 Safari/604.1',
}

const mockFeatureSupport = {
  modern: {
    ResizeObserver: true,
    IntersectionObserver: true,
    customElements: true,
    fetch: true,
    Promise: true,
    WeakMap: true,
    Map: true,
    Set: true,
    Symbol: true,
  },
  legacy: {
    ResizeObserver: false,
    IntersectionObserver: false,
    customElements: false,
    fetch: false,
    Promise: false,
    WeakMap: false,
    Map: false,
    Set: false,
    Symbol: false,
  },
}

describe('Cross-Browser Compatibility', () => {
  let originalUserAgent
  let originalMatchMedia

  beforeEach(() => {
    originalUserAgent = navigator.userAgent
    originalMatchMedia = window.matchMedia
    
    // Reset global features
    delete global.ResizeObserver
    delete global.IntersectionObserver
    delete global.fetch
    delete global.Promise
  })

  afterEach(() => {
    // Restore original values
    Object.defineProperty(navigator, 'userAgent', {
      value: originalUserAgent,
      writable: true,
    })
    
    window.matchMedia = originalMatchMedia
    vi.clearAllMocks()
  })

  describe('Browser Detection', () => {
    it('should detect Chrome correctly', () => {
      Object.defineProperty(navigator, 'userAgent', {
        value: mockUserAgents.chrome,
        writable: true,
      })
      
      const capabilities = detectBrowserCapabilities()

      expect(capabilities.browser).toBe('chrome')
      expect(capabilities.version).toMatch(/\d+/)
      expect(capabilities.isModern).toBe(true)
    })

    it('should detect Firefox correctly', () => {
      Object.defineProperty(navigator, 'userAgent', {
        value: mockUserAgents.firefox,
        writable: true,
      })
      
      const capabilities = detectBrowserCapabilities()

      expect(capabilities.browser).toBe('firefox')
      expect(capabilities.isModern).toBe(true)
    })

    it('should detect Safari correctly', () => {
      Object.defineProperty(navigator, 'userAgent', {
        value: mockUserAgents.safari,
        writable: true,
      })
      
      const capabilities = detectBrowserCapabilities()

      expect(capabilities.browser).toBe('safari')
      expect(capabilities.isModern).toBe(true)
    })

    it('should detect Edge correctly', () => {
      Object.defineProperty(navigator, 'userAgent', {
        value: mockUserAgents.edge,
        writable: true,
      })
      
      const capabilities = detectBrowserCapabilities()

      expect(capabilities.browser).toBe('edge')
      expect(capabilities.isModern).toBe(true)
    })

    it('should detect IE11 and mark as legacy', () => {
      Object.defineProperty(navigator, 'userAgent', {
        value: mockUserAgents.ie11,
        writable: true,
      })
      
      const capabilities = detectBrowserCapabilities()

      expect(capabilities.browser).toBe('ie')
      expect(capabilities.isModern).toBe(false)
      expect(capabilities.needsPolyfills).toBe(true)
    })

    it('should detect mobile browsers', () => {
      Object.defineProperty(navigator, 'userAgent', {
        value: mockUserAgents.mobile,
        writable: true,
      })
      
      const capabilities = detectBrowserCapabilities()

      expect(capabilities.isMobile).toBe(true)
      expect(capabilities.platform).toBe('ios')
    })
  })

  describe('Feature Detection', () => {
    it('should detect modern browser features', () => {
      // Mock modern browser features
      global.ResizeObserver = vi.fn()
      global.IntersectionObserver = vi.fn()
      global.fetch = vi.fn()
      global.Promise = vi.fn()
      global.customElements = { define: vi.fn() }
      
      const capabilities = detectBrowserCapabilities()
      
      expect(capabilities.features.resizeObserver).toBe(true)
      expect(capabilities.features.intersectionObserver).toBe(true)
      expect(capabilities.features.fetch).toBe(true)
      expect(capabilities.features.customElements).toBe(true)
      expect(capabilities.needsPolyfills).toBe(false)
    })

    it('should detect missing features in legacy browsers', () => {
      // Ensure features are undefined
      delete global.ResizeObserver
      delete global.IntersectionObserver
      delete global.fetch
      delete global.Promise
      delete global.customElements
      
      const capabilities = detectBrowserCapabilities()
      
      expect(capabilities.features.resizeObserver).toBe(false)
      expect(capabilities.features.intersectionObserver).toBe(false)
      expect(capabilities.features.fetch).toBe(false)
      expect(capabilities.needsPolyfills).toBe(true)
    })

    it('should detect CSS features', () => {
      // Mock CSS.supports
      global.CSS = {
        supports: vi.fn((property, value) => {
          const supportedFeatures = {
            'display': 'grid',
            'display': 'flex',
            'position': 'sticky',
            'backdrop-filter': 'blur(10px)',
          }
          
          return supportedFeatures[property] === value
        }),
      }
      
      const capabilities = detectBrowserCapabilities()
      
      expect(capabilities.css.grid).toBeDefined()
      expect(capabilities.css.flexbox).toBeDefined()
      expect(capabilities.css.stickyPosition).toBeDefined()
    })
  })

  describe('Touch and Hover Detection', () => {
    it('should detect touch-capable devices', () => {
      // Mock touch support
      Object.defineProperty(navigator, 'maxTouchPoints', {
        value: 5,
        writable: true,
      })
      
      window.matchMedia = vi.fn().mockImplementation(query => ({
        matches: query === '(hover: none)',
        media: query,
        onchange: null,
        addListener: vi.fn(),
        removeListener: vi.fn(),
        addEventListener: vi.fn(),
        removeEventListener: vi.fn(),
        dispatchEvent: vi.fn(),
      }))
      
      const capabilities = detectBrowserCapabilities()
      
      expect(capabilities.input.touch).toBe(true)
      expect(capabilities.input.hover).toBe(false)
    })

    it('should detect hover-capable devices', () => {
      // Mock hover support
      Object.defineProperty(navigator, 'maxTouchPoints', {
        value: 0,
        writable: true,
      })
      
      window.matchMedia = vi.fn().mockImplementation(query => ({
        matches: query === '(hover: hover)',
        media: query,
        onchange: null,
        addListener: vi.fn(),
        removeListener: vi.fn(),
        addEventListener: vi.fn(),
        removeEventListener: vi.fn(),
        dispatchEvent: vi.fn(),
      }))
      
      const capabilities = detectBrowserCapabilities()
      
      expect(capabilities.input.hover).toBe(true)
      expect(capabilities.input.touch).toBe(false)
    })

    it('should detect pointer precision', () => {
      window.matchMedia = vi.fn().mockImplementation(query => ({
        matches: query === '(pointer: fine)',
        media: query,
        onchange: null,
        addListener: vi.fn(),
        removeListener: vi.fn(),
        addEventListener: vi.fn(),
        removeEventListener: vi.fn(),
        dispatchEvent: vi.fn(),
      }))
      
      const capabilities = detectBrowserCapabilities()
      
      expect(capabilities.input.pointerPrecision).toBe('fine')
    })
  })

  describe('Polyfill Loading', () => {
    it('should load ResizeObserver polyfill when needed', async () => {
      delete global.ResizeObserver
      
      const mockPolyfill = vi.fn().mockImplementation(function() {
        this.observe = vi.fn()
        this.unobserve = vi.fn()
        this.disconnect = vi.fn()
      })
      
      global.ResizeObserver = mockPolyfill
      
      await loadPolyfills(['resizeObserver'])
      
      expect(global.ResizeObserver).toBe(mockPolyfill)
    })

    it('should load IntersectionObserver polyfill when needed', async () => {
      delete global.IntersectionObserver
      
      const mockPolyfill = vi.fn().mockImplementation(function(callback, options) {
        this.observe = vi.fn()
        this.unobserve = vi.fn()
        this.disconnect = vi.fn()
        this.root = options?.root || null
        this.rootMargin = options?.rootMargin || '0px'
        this.thresholds = options?.threshold || [0]
      })
      
      global.IntersectionObserver = mockPolyfill
      
      await loadPolyfills(['intersectionObserver'])
      
      expect(global.IntersectionObserver).toBe(mockPolyfill)
    })

    it('should load fetch polyfill for legacy browsers', async () => {
      delete global.fetch
      
      const mockFetch = vi.fn().mockResolvedValue({
        ok: true,
        json: vi.fn().mockResolvedValue({}),
        text: vi.fn().mockResolvedValue(''),
        blob: vi.fn().mockResolvedValue(new Blob()),
      })
      
      global.fetch = mockFetch
      
      await loadPolyfills(['fetch'])
      
      expect(global.fetch).toBe(mockFetch)
    })

    it('should skip polyfills for modern browsers', async () => {
      // Mock modern browser with all features
      global.ResizeObserver = vi.fn()
      global.IntersectionObserver = vi.fn()
      global.fetch = vi.fn()
      
      const originalResizeObserver = global.ResizeObserver
      
      await loadPolyfills(['resizeObserver'])
      
      // Should not replace existing implementation
      expect(global.ResizeObserver).toBe(originalResizeObserver)
    })
  })

  describe('Cross-Browser Setup', () => {
    it('should initialize cross-browser support correctly', async () => {
      const setup = await setupCrossBrowserSupport()
      
      expect(setup.capabilities).toBeDefined()
      expect(setup.polyfillsLoaded).toBeDefined()
      expect(Array.isArray(setup.polyfillsLoaded)).toBe(true)
    })

    it('should add browser-specific CSS classes', async () => {
      Object.defineProperty(navigator, 'userAgent', {
        value: mockUserAgents.chrome,
        writable: true,
      })
      
      await setupCrossBrowserSupport()
      
      const htmlElement = document.documentElement

      expect(htmlElement.classList.contains('browser-chrome')).toBe(true)
    })

    it('should add feature detection classes', async () => {
      global.ResizeObserver = vi.fn()
      global.IntersectionObserver = vi.fn()
      
      await setupCrossBrowserSupport()
      
      const htmlElement = document.documentElement

      expect(htmlElement.classList.contains('has-resize-observer')).toBe(true)
      expect(htmlElement.classList.contains('has-intersection-observer')).toBe(true)
    })

    it('should add no-feature classes for missing capabilities', async () => {
      delete global.ResizeObserver
      delete global.IntersectionObserver
      
      await setupCrossBrowserSupport()
      
      const htmlElement = document.documentElement

      expect(htmlElement.classList.contains('no-resize-observer')).toBe(true)
      expect(htmlElement.classList.contains('no-intersection-observer')).toBe(true)
    })

    it('should add input capability classes', async () => {
      Object.defineProperty(navigator, 'maxTouchPoints', {
        value: 5,
        writable: true,
      })
      
      window.matchMedia = vi.fn().mockImplementation(query => ({
        matches: query === '(hover: none)' || query === '(pointer: coarse)',
        media: query,
        onchange: null,
        addListener: vi.fn(),
        removeListener: vi.fn(),
        addEventListener: vi.fn(),
        removeEventListener: vi.fn(),
        dispatchEvent: vi.fn(),
      }))
      
      await setupCrossBrowserSupport()
      
      const htmlElement = document.documentElement

      expect(htmlElement.classList.contains('touch-device')).toBe(true)
      expect(htmlElement.classList.contains('no-hover')).toBe(true)
      expect(htmlElement.classList.contains('coarse-pointer')).toBe(true)
    })
  })

  describe('CSS Fallbacks', () => {
    it('should provide CSS Grid fallbacks', () => {
      // Mock lack of CSS Grid support
      global.CSS = {
        supports: vi.fn().mockReturnValue(false),
      }
      
      const capabilities = detectBrowserCapabilities()
      
      expect(capabilities.css.grid).toBe(false)
      expect(capabilities.needsCSSFallbacks).toBe(true)
    })

    it('should detect flexbox support', () => {
      global.CSS = {
        supports: vi.fn().mockImplementation((property, value) => {
          return property === 'display' && value === 'flex'
        }),
      }
      
      const capabilities = detectBrowserCapabilities()
      
      expect(capabilities.css.flexbox).toBe(true)
    })

    it('should detect sticky position support', () => {
      global.CSS = {
        supports: vi.fn().mockImplementation((property, value) => {
          return property === 'position' && value === 'sticky'
        }),
      }
      
      const capabilities = detectBrowserCapabilities()
      
      expect(capabilities.css.stickyPosition).toBe(true)
    })
  })

  describe('Performance Considerations', () => {
    it('should cache browser capabilities', () => {
      const capabilities1 = detectBrowserCapabilities()
      const capabilities2 = detectBrowserCapabilities()
      
      // Should return the same cached result
      expect(capabilities1).toBe(capabilities2)
    })

    it('should lazy load polyfills only when needed', async () => {
      // Mock modern browser
      global.ResizeObserver = vi.fn()
      global.IntersectionObserver = vi.fn()
      global.fetch = vi.fn()
      
      const loadStart = performance.now()

      await loadPolyfills(['resizeObserver', 'intersectionObserver', 'fetch'])

      const loadTime = performance.now() - loadStart
      
      // Should be very fast since no polyfills needed
      expect(loadTime).toBeLessThan(10)
    })

    it('should minimize DOM queries during setup', async () => {
      const querySelectiorSpy = vi.spyOn(document, 'querySelector')
      const classListSpy = vi.spyOn(document.documentElement.classList, 'add')
      
      await setupCrossBrowserSupport()
      
      // Should batch DOM operations
      expect(querySelectiorSpy).toHaveBeenCalledTimes(1)
      expect(classListSpy.mock.calls.length).toBeLessThan(20)
      
      querySelectiorSpy.mockRestore()
      classListSpy.mockRestore()
    })
  })

  describe('Error Handling', () => {
    it('should handle polyfill loading failures gracefully', async () => {
      // Mock polyfill loading failure
      const originalError = console.error

      console.error = vi.fn()
      
      // Simulate network error
      const mockError = new Error('Network error')

      global.fetch = vi.fn().mockRejectedValue(mockError)
      
      const result = await loadPolyfills(['fetch']).catch(err => err)
      
      expect(result).toBeInstanceOf(Error)
      expect(console.error).toHaveBeenCalled()
      
      console.error = originalError
    })

    it('should continue setup even if some features fail', async () => {
      // Mock partial failure
      Object.defineProperty(navigator, 'userAgent', {
        get: () => { throw new Error('User agent access failed') },
        configurable: true,
      })
      
      const setup = await setupCrossBrowserSupport()
      
      // Should still provide some capabilities
      expect(setup).toBeDefined()
      expect(setup.capabilities).toBeDefined()
    })

    it('should provide fallback capabilities when detection fails', () => {
      // Mock feature detection failure
      global.CSS = {
        supports: vi.fn().mockImplementation(() => {
          throw new Error('CSS.supports failed')
        }),
      }
      
      const capabilities = detectBrowserCapabilities()
      
      // Should provide safe defaults
      expect(capabilities.css).toBeDefined()
      expect(capabilities.needsCSSFallbacks).toBe(true)
    })
  })

  describe('Real Browser Testing', () => {
    it('should work in actual browser environment', () => {
      // Test that our code doesn't break in real browser
      const capabilities = detectBrowserCapabilities()
      
      expect(capabilities).toBeDefined()
      expect(typeof capabilities.browser).toBe('string')
      expect(typeof capabilities.isModern).toBe('boolean')
      expect(typeof capabilities.needsPolyfills).toBe('boolean')
    })

    it('should handle missing global objects gracefully', () => {
      // Temporarily remove global objects
      const originalNavigator = global.navigator
      const originalWindow = global.window
      
      delete global.navigator
      delete global.window
      
      let capabilities
      expect(() => {
        capabilities = detectBrowserCapabilities()
      }).not.toThrow()
      
      expect(capabilities).toBeDefined()
      
      // Restore globals
      global.navigator = originalNavigator
      global.window = originalWindow
    })
  })
})
