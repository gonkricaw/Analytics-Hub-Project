import { onMounted, ref } from 'vue'

// Performance monitoring
const performanceMetrics = ref({
  loadTime: 0,
  renderTime: 0,
  firstContentfulPaint: 0,
  largestContentfulPaint: 0,
  cumulativeLayoutShift: 0,
  memoryUsage: 0,
})

export function usePerformance() {
  const measureLoadTime = () => {
    if (typeof window !== 'undefined' && window.performance) {
      const navigation = performance.getEntriesByType('navigation')[0]
      if (navigation) {
        performanceMetrics.value.loadTime = navigation.loadEventEnd - navigation.loadEventStart
      }
    }
  }

  const measureRenderTime = () => {
    if (typeof window !== 'undefined' && window.performance) {
      const paint = performance.getEntriesByType('paint')
      const fcp = paint.find(entry => entry.name === 'first-contentful-paint')
      if (fcp) {
        performanceMetrics.value.firstContentfulPaint = fcp.startTime
      }
    }
  }

  const measureMemoryUsage = () => {
    if (typeof window !== 'undefined' && window.performance && window.performance.memory) {
      performanceMetrics.value.memoryUsage = window.performance.memory.usedJSHeapSize
    }
  }

  const measureWebVitals = () => {
    // Largest Contentful Paint
    if ('PerformanceObserver' in window) {
      try {
        const lcpObserver = new PerformanceObserver(entryList => {
          const entries = entryList.getEntries()
          const lastEntry = entries[entries.length - 1]

          performanceMetrics.value.largestContentfulPaint = lastEntry.startTime
        })

        lcpObserver.observe({ entryTypes: ['largest-contentful-paint'] })

        // Cumulative Layout Shift
        const clsObserver = new PerformanceObserver(entryList => {
          for (const entry of entryList.getEntries()) {
            if (!entry.hadRecentInput) {
              performanceMetrics.value.cumulativeLayoutShift += entry.value
            }
          }
        })

        clsObserver.observe({ entryTypes: ['layout-shift'] })
      } catch (error) {
        console.warn('Performance monitoring not fully supported:', error)
      }
    }
  }

  const logPerformanceMetrics = () => {
    if (import.meta.env.DEV) {
      console.table(performanceMetrics.value)
    }
  }

  const reportPerformanceMetrics = () => {
    // Send metrics to analytics service if available
    if (window.analytics && typeof window.analytics.track === 'function') {
      window.analytics.track('Performance Metrics', performanceMetrics.value)
    }
  }

  onMounted(() => {
    // Wait for page to fully load before measuring
    setTimeout(() => {
      measureLoadTime()
      measureRenderTime()
      measureMemoryUsage()
      measureWebVitals()
      
      // Log metrics after a delay to capture final values
      setTimeout(() => {
        logPerformanceMetrics()
        reportPerformanceMetrics()
      }, 2000)
    }, 100)
  })

  return {
    performanceMetrics: readonly(performanceMetrics),
    measureLoadTime,
    measureRenderTime,
    measureMemoryUsage,
    measureWebVitals,
    logPerformanceMetrics,
    reportPerformanceMetrics,
  }
}

// Lazy loading helper
export function lazyLoad(importFunc, options = {}) {
  const {
    loadingComponent = null,
    errorComponent = null,
    delay = 200,
    timeout = 5000,
  } = options

  return defineAsyncComponent({
    loader: importFunc,
    loadingComponent,
    errorComponent,
    delay,
    timeout,
    onError(error, retry, fail, attempts) {
      console.error(`Failed to load component (attempt ${attempts}):`, error)
      
      // Retry up to 3 times
      if (attempts < 3) {
        setTimeout(retry, 1000 * attempts)
      } else {
        fail()
      }
    },
  })
}

// Image lazy loading
export function useLazyImage() {
  const imageLoaded = ref(false)
  const imageError = ref(false)
  
  const loadImage = src => {
    return new Promise((resolve, reject) => {
      const img = new Image()

      img.onload = () => {
        imageLoaded.value = true
        resolve(img)
      }
      img.onerror = error => {
        imageError.value = true
        reject(error)
      }
      img.src = src
    })
  }
  
  return {
    imageLoaded,
    imageError,
    loadImage,
  }
}

// Resource preloading
export function useResourcePreloader() {
  const preloadedResources = new Set()
  
  const preloadScript = src => {
    if (preloadedResources.has(src)) return Promise.resolve()
    
    return new Promise((resolve, reject) => {
      const link = document.createElement('link')

      link.rel = 'modulepreload'
      link.href = src
      link.onload = () => {
        preloadedResources.add(src)
        resolve()
      }
      link.onerror = reject
      document.head.appendChild(link)
    })
  }
  
  const preloadStyle = href => {
    if (preloadedResources.has(href)) return Promise.resolve()
    
    return new Promise((resolve, reject) => {
      const link = document.createElement('link')

      link.rel = 'preload'
      link.as = 'style'
      link.href = href
      link.onload = () => {
        preloadedResources.add(href)
        resolve()
      }
      link.onerror = reject
      document.head.appendChild(link)
    })
  }
  
  const preloadImage = src => {
    if (preloadedResources.has(src)) return Promise.resolve()
    
    return new Promise((resolve, reject) => {
      const link = document.createElement('link')

      link.rel = 'preload'
      link.as = 'image'
      link.href = src
      link.onload = () => {
        preloadedResources.add(src)
        resolve()
      }
      link.onerror = reject
      document.head.appendChild(link)
    })
  }
  
  return {
    preloadScript,
    preloadStyle,
    preloadImage,
    preloadedResources: readonly(preloadedResources),
  }
}

// Bundle splitting helper
export function createChunkName(route) {
  // Convert route path to valid chunk name
  return route.replace(/[^a-z0-9]/gi, '-').replace(/-+/g, '-').replace(/^-|-$/g, '')
}

// Memory management
export function useMemoryManagement() {
  const cleanupTasks = []
  
  const addCleanupTask = task => {
    cleanupTasks.push(task)
  }
  
  const cleanup = () => {
    cleanupTasks.forEach(task => {
      try {
        task()
      } catch (error) {
        console.error('Cleanup task failed:', error)
      }
    })
    cleanupTasks.length = 0
  }
  
  const checkMemoryUsage = () => {
    if (window.performance && window.performance.memory) {
      const memory = window.performance.memory
      const usagePercent = (memory.usedJSHeapSize / memory.jsHeapSizeLimit) * 100
      
      if (usagePercent > 80) {
        console.warn('High memory usage detected:', usagePercent.toFixed(2) + '%')

        // Trigger garbage collection if possible
        if (window.gc) {
          window.gc()
        }
      }
      
      return {
        used: memory.usedJSHeapSize,
        total: memory.totalJSHeapSize,
        limit: memory.jsHeapSizeLimit,
        usagePercent,
      }
    }
    
    return null
  }
  
  return {
    addCleanupTask,
    cleanup,
    checkMemoryUsage,
  }
}
