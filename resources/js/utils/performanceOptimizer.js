/**
 * Performance Optimization Utilities
 * Provides lazy loading, code splitting, image optimization, and bundle optimization utilities
 */

import { defineAsyncComponent } from 'vue'

/**
 * Enhanced lazy loading for Vue components with loading and error states
 */
export function createLazyComponent(importFn, options = {}) {
  const {
    loadingComponent = null,
    errorComponent = null,
    delay = 200,
    timeout = 30000,
    retries = 3,
  } = options

  return defineAsyncComponent({
    loader: async () => {
      let lastError
      
      for (let i = 0; i < retries; i++) {
        try {
          return await importFn()
        } catch (error) {
          lastError = error
          if (import.meta.env.DEV) {
            console.warn(`Failed to load component, attempt ${i + 1}/${retries}:`, error)
          }
          
          // Wait before retrying (exponential backoff)
          if (i < retries - 1) {
            await new Promise(resolve => setTimeout(resolve, Math.pow(2, i) * 1000))
          }
        }
      }
      
      throw lastError
    },
    loadingComponent,
    errorComponent,
    delay,
    timeout,
  })
}

/**
 * Lazy load dashboard widgets with optimized loading
 */
export const lazyDashboardComponents = {
  JumbotronWidget: createLazyComponent(
    () => import('@/components/dashboard/JumbotronWidget.vue'),
    { delay: 0 }, // Load immediately for above-the-fold content
  ),
  
  LoginStatsWidget: createLazyComponent(
    () => import('@/components/dashboard/LoginStatsWidget.vue'),
    { delay: 100 },
  ),
  
  OnlineUsersWidget: createLazyComponent(
    () => import('@/components/dashboard/OnlineUsersWidget.vue'),
    { delay: 150 },
  ),
  
  NotificationsWidget: createLazyComponent(
    () => import('@/components/dashboard/NotificationsWidget.vue'),
    { delay: 200 },
  ),
  
  FrequentContentWidget: createLazyComponent(
    () => import('@/components/dashboard/FrequentContentWidget.vue'),
    { delay: 250 },
  ),
  
  FrequentUsersWidget: createLazyComponent(
    () => import('@/components/dashboard/FrequentUsersWidget.vue'),
    { delay: 300 },
  ),
  
  MarqueeWidget: createLazyComponent(
    () => import('@/components/dashboard/MarqueeWidget.vue'),
    { delay: 50 },
  ),
  
  DigitalClockWidget: createLazyComponent(
    () => import('@/components/dashboard/DigitalClockWidget.vue'),
    { delay: 0 },
  ),
}

/**
 * Lazy load admin components
 */
export const lazyAdminComponents = {
  UserManagement: createLazyComponent(
    () => import('@/pages/admin/users.vue'),
  ),
  
  RoleManagement: createLazyComponent(
    () => import('@/pages/admin/roles.vue'),
  ),
  
  PermissionManagement: createLazyComponent(
    () => import('@/pages/admin/permissions.vue'),
  ),
  
  UserRoleManagement: createLazyComponent(
    () => import('@/pages/admin/user-roles.vue'),
  ),
  
  ContentManagement: createLazyComponent(
    () => import('@/components/admin/content/ContentManagement.vue'),
  ),
  
  MenuManagement: createLazyComponent(
    () => import('@/components/admin/menu/MenuManagement.vue'),
  ),
  
  EmailTemplateManagement: createLazyComponent(
    () => import('@/pages/admin/email-templates.vue'),
  ),
  
  SystemConfigurationManagement: createLazyComponent(
    () => import('@/pages/admin/system-configurations.vue'),
  ),
}

/**
 * Lazy load modal and dialog components
 */
export const lazyModalComponents = {
  ConfirmationModal: createLazyComponent(
    () => import('@/components/common/ConfirmationModal.vue'),
  ),
  
  NotificationDetailModal: createLazyComponent(
    () => import('@/components/NotificationDetailModal.vue'),
  ),
  
  TermsModal: createLazyComponent(
    () => import('@/components/TermsModal.vue'),
  ),
  
  LogoutModal: createLazyComponent(
    () => import('@/components/LogoutModal.vue'),
  ),
  
  MenuFormModal: createLazyComponent(
    () => import('@/components/admin/menu/MenuFormModal.vue'),
  ),
  
  ContentFormModal: createLazyComponent(
    () => import('@/components/admin/content/ContentFormModal.vue'),
  ),
}

/**
 * Image optimization utilities
 */
export class ImageOptimizer {
  constructor() {
    this.cache = new Map()
    this.observer = null
    this.setupIntersectionObserver()
  }

  /**
   * Setup intersection observer for lazy loading images
   */
  setupIntersectionObserver() {
    if ('IntersectionObserver' in window) {
      this.observer = new IntersectionObserver(
        entries => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              this.loadImage(entry.target)
              this.observer.unobserve(entry.target)
            }
          })
        },
        {
          rootMargin: '50px',
          threshold: 0.1,
        },
      )
    }
  }

  /**
   * Optimize image loading with lazy loading and caching
   */
  optimizeImage(img, options = {}) {
    const {
      lazy = true,
      placeholder = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMzAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2Y1ZjVmNSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBkb21pbmFudC1iYXNlbGluZT0ibWlkZGxlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmaWxsPSIjOTk5Ij5Mb2FkaW5nLi4uPC90ZXh0Pjwvc3ZnPg==',
      quality = 80,
      format = 'webp',
    } = options

    if (!img) return

    // Set placeholder initially
    if (lazy && placeholder) {
      img.src = placeholder
    }

    // Add loading attribute for native lazy loading support
    if (lazy && 'loading' in HTMLImageElement.prototype) {
      img.loading = 'lazy'
    }

    // Use intersection observer for browsers that don't support native lazy loading
    if (lazy && this.observer && !('loading' in HTMLImageElement.prototype)) {
      this.observer.observe(img)
    }

    // Add error handling
    img.onerror = () => {
      img.src = placeholder
      img.alt = 'Failed to load image'
    }

    return img
  }

  /**
   * Load image with optimization
   */
  async loadImage(img) {
    const src = img.dataset.src || img.src
    
    if (this.cache.has(src)) {
      img.src = this.cache.get(src)
      
      return
    }

    try {
      // Create a new image to preload
      const newImg = new Image()
      
      await new Promise((resolve, reject) => {
        newImg.onload = resolve
        newImg.onerror = reject
        newImg.src = src
      })

      // Cache the loaded image
      this.cache.set(src, src)
      img.src = src
      img.classList.add('image-loaded')
      
    } catch (error) {
      if (import.meta.env.DEV) {
        console.warn('Failed to load image:', src, error)
      }
    }
  }

  /**
   * Generate responsive image srcset
   */
  generateSrcSet(baseSrc, sizes = [320, 640, 1024, 1440]) {
    return sizes
      .map(size => {
        const optimizedSrc = this.getOptimizedImageUrl(baseSrc, { width: size })
        
        return `${optimizedSrc} ${size}w`
      })
      .join(', ')
  }

  /**
   * Get optimized image URL (placeholder for actual image optimization service)
   */
  getOptimizedImageUrl(src, options = {}) {
    const { width, height, quality = 80, format = 'webp' } = options
    
    // This would typically integrate with an image optimization service
    // For now, return the original URL
    return src
  }
}

/**
 * Bundle size optimization utilities
 */
export class BundleOptimizer {
  constructor() {
    this.loadedChunks = new Set()
    this.preloadQueue = []
  }

  /**
   * Preload critical resources
   */
  preloadCriticalResources() {
    // Only preload in development or when explicitly needed
    if (import.meta.env.DEV) {
      const criticalResources = [
        // Critical fonts only - CSS and JS are handled by Vite
        '/fonts/inter-latin-400-normal.woff2',
        '/fonts/inter-latin-600-normal.woff2',
      ]

      criticalResources.forEach(resource => {
        // Check if resource exists before preloading
        this.preloadResourceIfExists(resource)
      })
    }
  }

  /**
   * Preload resource with appropriate type detection
   */
  preloadResource(href, as = null) {
    if (typeof document === 'undefined') return

    const link = document.createElement('link')

    link.rel = 'preload'
    link.href = href
    
    // Auto-detect resource type if not provided
    if (!as) {
      if (href.endsWith('.css')) as = 'style'
      else if (href.endsWith('.js')) as = 'script'
      else if (href.endsWith('.woff2') || href.endsWith('.woff')) as = 'font'
      else if (href.match(/\.(jpe?g|png|webp|avif)$/)) as = 'image'
    }
    
    if (as) {
      link.as = as
      
      // Add crossorigin for fonts
      if (as === 'font') {
        link.crossOrigin = 'anonymous'
      }
    }

    document.head.appendChild(link)
  }

  /**
   * Preload resource only if it exists (to avoid 404s)
   */
  preloadResourceIfExists(href, as = null) {
    if (typeof document === 'undefined') return

    // Check if resource exists before preloading
    fetch(href, { method: 'HEAD' })
      .then(response => {
        if (response.ok) {
          this.preloadResource(href, as)
        }
      })
      .catch(() => {
        // Resource doesn't exist, skip preloading
        if (import.meta.env.DEV) {
          console.warn(`Skipping preload for non-existent resource: ${href}`)
        }
      })
  }

  /**
   * Prefetch non-critical resources
   */
  prefetchNonCriticalResources() {
    const nonCriticalResources = [
      // Admin pages (prefetch for admin users)
      '/js/admin.js',

      // Chart libraries
      '/js/charts.js',

      // Additional UI components
      '/js/ui-extended.js',
    ]

    // Use requestIdleCallback if available
    if ('requestIdleCallback' in window) {
      window.requestIdleCallback(() => {
        nonCriticalResources.forEach(resource => {
          this.prefetchResource(resource)
        })
      })
    } else {
      setTimeout(() => {
        nonCriticalResources.forEach(resource => {
          this.prefetchResource(resource)
        })
      }, 2000)
    }
  }

  /**
   * Prefetch resource for future navigation
   */
  prefetchResource(href) {
    if (typeof document === 'undefined') return

    const link = document.createElement('link')

    link.rel = 'prefetch'
    link.href = href
    document.head.appendChild(link)
  }

  /**
   * Get bundle analysis information
   */
  analyzeBundleSize() {
    if (typeof performance === 'undefined') return null

    const navigationEntries = performance.getEntriesByType('navigation')
    const resourceEntries = performance.getEntriesByType('resource')
    
    const jsResources = resourceEntries.filter(entry => 
      entry.name.includes('.js') && !entry.name.includes('hot-update'),
    )
    
    const cssResources = resourceEntries.filter(entry => 
      entry.name.includes('.css'),
    )

    return {
      totalJSSize: jsResources.reduce((sum, entry) => sum + (entry.transferSize || 0), 0),
      totalCSSSize: cssResources.reduce((sum, entry) => sum + (entry.transferSize || 0), 0),
      jsFiles: jsResources.length,
      cssFiles: cssResources.length,
      loadTime: navigationEntries[0]?.loadEventEnd || 0,
    }
  }
}

/**
 * Memory optimization utilities
 */
export class MemoryOptimizer {
  constructor() {
    this.cache = new Map()
    this.maxCacheSize = 100
    this.setupMemoryMonitoring()
  }

  /**
   * Setup memory monitoring and cleanup
   */
  setupMemoryMonitoring() {
    // Cleanup cache when it gets too large
    setInterval(() => {
      if (this.cache.size > this.maxCacheSize) {
        this.cleanupCache()
      }
    }, 30000) // Check every 30 seconds

    // Cleanup on page visibility change
    if (typeof document !== 'undefined') {
      document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
          this.performGarbageCollection()
        }
      })
    }
  }

  /**
   * Clean up cache using LRU strategy
   */
  cleanupCache() {
    const entries = Array.from(this.cache.entries())
    const sortedEntries = entries.sort((a, b) => a[1].lastAccessed - b[1].lastAccessed)
    
    // Remove oldest 25% of entries
    const toRemove = Math.floor(entries.length * 0.25)
    for (let i = 0; i < toRemove; i++) {
      this.cache.delete(sortedEntries[i][0])
    }
  }

  /**
   * Perform garbage collection optimizations
   */
  performGarbageCollection() {
    // Clear component cache
    this.cache.clear()
    
    // Force garbage collection if available (Chrome DevTools)
    if (window.gc && typeof window.gc === 'function') {
      window.gc()
    }
  }

  /**
   * Cache data with TTL and access tracking
   */
  cacheData(key, data, ttl = 300000) { // 5 minutes default TTL
    this.cache.set(key, {
      data,
      timestamp: Date.now(),
      ttl,
      lastAccessed: Date.now(),
    })
  }

  /**
   * Get cached data
   */
  getCachedData(key) {
    const cached = this.cache.get(key)
    
    if (!cached) return null
    
    // Check if expired
    if (Date.now() - cached.timestamp > cached.ttl) {
      this.cache.delete(key)
      
      return null
    }
    
    // Update last accessed time
    cached.lastAccessed = Date.now()
    
    return cached.data
  }
}

/**
 * Performance monitoring utilities
 */
export class PerformanceMonitor {
  constructor() {
    this.metrics = new Map()
    this.setupPerformanceObserver()
  }

  /**
   * Setup performance observer for monitoring
   */
  setupPerformanceObserver() {
    if ('PerformanceObserver' in window) {
      // Monitor largest contentful paint
      const lcpObserver = new PerformanceObserver(list => {
        const entries = list.getEntries()
        const lastEntry = entries[entries.length - 1]

        this.metrics.set('lcp', lastEntry.startTime)
      })

      lcpObserver.observe({ entryTypes: ['largest-contentful-paint'] })

      // Monitor first input delay
      const fidObserver = new PerformanceObserver(list => {
        const entries = list.getEntries()
        const firstEntry = entries[0]

        this.metrics.set('fid', firstEntry.processingStart - firstEntry.startTime)
      })

      fidObserver.observe({ entryTypes: ['first-input'] })

      // Monitor cumulative layout shift
      const clsObserver = new PerformanceObserver(list => {
        let clsValue = 0
        const entries = list.getEntries()
        
        entries.forEach(entry => {
          if (!entry.hadRecentInput) {
            clsValue += entry.value
          }
        })
        
        this.metrics.set('cls', clsValue)
      })

      clsObserver.observe({ entryTypes: ['layout-shift'] })
    }
  }

  /**
   * Get performance metrics
   */
  getMetrics() {
    return {
      lcp: this.metrics.get('lcp') || 0,
      fid: this.metrics.get('fid') || 0,
      cls: this.metrics.get('cls') || 0,
      bundleSize: this.getBundleSize(),
      memoryUsage: this.getMemoryUsage(),
    }
  }

  /**
   * Get bundle size information
   */
  getBundleSize() {
    if (typeof performance === 'undefined') return null

    const resourceEntries = performance.getEntriesByType('resource')

    const totalSize = resourceEntries.reduce((sum, entry) => {
      return sum + (entry.transferSize || 0)
    }, 0)

    return {
      total: totalSize,
      js: resourceEntries
        .filter(entry => entry.name.includes('.js'))
        .reduce((sum, entry) => sum + (entry.transferSize || 0), 0),
      css: resourceEntries
        .filter(entry => entry.name.includes('.css'))
        .reduce((sum, entry) => sum + (entry.transferSize || 0), 0),
    }
  }

  /**
   * Get memory usage information
   */
  getMemoryUsage() {
    if ('memory' in performance) {
      return {
        used: performance.memory.usedJSHeapSize,
        total: performance.memory.totalJSHeapSize,
        limit: performance.memory.jsHeapSizeLimit,
      }
    }
    
    return null
  }
}

// Create singleton instances
export const imageOptimizer = new ImageOptimizer()
export const bundleOptimizer = new BundleOptimizer()
export const memoryOptimizer = new MemoryOptimizer()
export const performanceMonitor = new PerformanceMonitor()

// Auto-initialize performance optimizations
if (typeof window !== 'undefined') {
  // Only preload critical resources in development
  if (import.meta.env.DEV) {
    bundleOptimizer.preloadCriticalResources()
  }
  
  // Prefetch non-critical resources after load (only in development)
  window.addEventListener('load', () => {
    if (import.meta.env.DEV) {
      bundleOptimizer.prefetchNonCriticalResources()
    }
  })
}
