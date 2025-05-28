/**
 * Performance Monitor Utility
 * 
 * Provides comprehensive performance monitoring for the Analytics Hub
 * Tracks interactions, memory usage, bundle sizes, and loading times
 * 
 * @author Indonet Analytics Hub Team
 * @version 1.0.0
 */

// Performance metrics storage
const metrics = {
  interactions: {},
  memory: {
    used: 0,
    total: 0,
    timestamp: Date.now(),
  },
  performance: {
    navigationStart: 0,
    loadComplete: 0,
    domContentLoaded: 0,
    firstPaint: 0,
    firstContentfulPaint: 0,
  },
  bundleSize: {
    estimated: 0,
    compressed: 0,
  },
  loadingTimes: {
    components: {},
    routes: {},
    assets: {},
  },
}

/**
 * Track user interactions for performance analysis
 * @param {string} action - The action being tracked
 * @param {Object} data - Additional data about the interaction
 */
const trackInteraction = (action, data = {}) => {
  const timestamp = Date.now()
  
  if (!metrics.interactions[action]) {
    metrics.interactions[action] = {
      count: 0,
      totalTime: 0,
      averageTime: 0,
      lastOccurrence: null,
      data: [],
    }
  }
  
  const interaction = metrics.interactions[action]

  interaction.count += 1
  interaction.lastOccurrence = timestamp
  
  // Track timing if provided
  if (data.startTime) {
    const duration = timestamp - data.startTime

    interaction.totalTime += duration
    interaction.averageTime = interaction.totalTime / interaction.count
  }
  
  // Store interaction data
  interaction.data.push({
    timestamp,
    ...data,
  })
  
  // Keep only last 100 interactions per action
  if (interaction.data.length > 100) {
    interaction.data = interaction.data.slice(-100)
  }
}

/**
 * Update memory usage metrics
 */
const updateMemoryMetrics = () => {
  if (typeof window !== 'undefined' && window.performance && window.performance.memory) {
    metrics.memory = {
      used: window.performance.memory.usedJSHeapSize,
      total: window.performance.memory.totalJSHeapSize,
      limit: window.performance.memory.jsHeapSizeLimit,
      timestamp: Date.now(),
    }
  } else {
    // Fallback for browsers without memory API
    metrics.memory = {
      used: 0,
      total: 0,
      limit: 0,
      timestamp: Date.now(),
      note: 'Memory API not available',
    }
  }
}

/**
 * Update performance timing metrics
 */
const updatePerformanceMetrics = () => {
  if (typeof window !== 'undefined' && window.performance && window.performance.timing) {
    const timing = window.performance.timing
    
    metrics.performance = {
      navigationStart: timing.navigationStart,
      loadComplete: timing.loadEventEnd - timing.navigationStart,
      domContentLoaded: timing.domContentLoadedEventEnd - timing.navigationStart,
      firstPaint: 0, // Will be updated if available
      firstContentfulPaint: 0, // Will be updated if available
    }
    
    // Try to get paint metrics
    if (window.performance.getEntriesByType) {
      const paintEntries = window.performance.getEntriesByType('paint')

      paintEntries.forEach(entry => {
        if (entry.name === 'first-paint') {
          metrics.performance.firstPaint = entry.startTime
        } else if (entry.name === 'first-contentful-paint') {
          metrics.performance.firstContentfulPaint = entry.startTime
        }
      })
    }
  }
}

/**
 * Estimate bundle size metrics
 */
const updateBundleSizeMetrics = () => {
  if (typeof window !== 'undefined' && window.performance && window.performance.getEntriesByType) {
    const resources = window.performance.getEntriesByType('resource')
    let totalSize = 0
    let compressedSize = 0
    
    resources.forEach(resource => {
      if (resource.name.includes('.js') || resource.name.includes('.css')) {
        // Estimate size based on transfer size
        if (resource.transferSize) {
          compressedSize += resource.transferSize
        }
        if (resource.decodedBodySize) {
          totalSize += resource.decodedBodySize
        }
      }
    })
    
    metrics.bundleSize = {
      estimated: totalSize,
      compressed: compressedSize,
      compressionRatio: totalSize > 0 ? (compressedSize / totalSize) : 0,
    }
  }
}

/**
 * Track component loading times
 * @param {string} componentName - Name of the component
 * @param {number} loadTime - Time taken to load in milliseconds
 */
const trackComponentLoad = (componentName, loadTime) => {
  if (!metrics.loadingTimes.components[componentName]) {
    metrics.loadingTimes.components[componentName] = {
      count: 0,
      totalTime: 0,
      averageTime: 0,
      minTime: Infinity,
      maxTime: 0,
    }
  }
  
  const component = metrics.loadingTimes.components[componentName]

  component.count += 1
  component.totalTime += loadTime
  component.averageTime = component.totalTime / component.count
  component.minTime = Math.min(component.minTime, loadTime)
  component.maxTime = Math.max(component.maxTime, loadTime)
}

/**
 * Track route loading times
 * @param {string} routeName - Name of the route
 * @param {number} loadTime - Time taken to load in milliseconds
 */
const trackRouteLoad = (routeName, loadTime) => {
  if (!metrics.loadingTimes.routes[routeName]) {
    metrics.loadingTimes.routes[routeName] = {
      count: 0,
      totalTime: 0,
      averageTime: 0,
      minTime: Infinity,
      maxTime: 0,
    }
  }
  
  const route = metrics.loadingTimes.routes[routeName]

  route.count += 1
  route.totalTime += loadTime
  route.averageTime = route.totalTime / route.count
  route.minTime = Math.min(route.minTime, loadTime)
  route.maxTime = Math.max(route.maxTime, loadTime)
}

/**
 * Get comprehensive performance metrics
 * @returns {Object} Complete performance metrics
 */
const getMetrics = () => {
  // Update metrics before returning
  updateMemoryMetrics()
  updatePerformanceMetrics()
  updateBundleSizeMetrics()
  
  return {
    ...metrics,
    timestamp: Date.now(),
    summary: {
      totalInteractions: Object.keys(metrics.interactions).length,
      totalInteractionCount: Object.values(metrics.interactions).reduce((sum, interaction) => sum + interaction.count, 0),
      averageMemoryUsage: metrics.memory.used,
      loadPerformanceScore: calculatePerformanceScore(),
    },
  }
}

/**
 * Calculate overall performance score (0-100)
 * @returns {number} Performance score
 */
const calculatePerformanceScore = () => {
  let score = 100
  
  // Deduct points for slow loading
  if (metrics.performance.loadComplete > 3000) {
    score -= Math.min(30, (metrics.performance.loadComplete - 3000) / 100)
  }
  
  // Deduct points for high memory usage
  if (metrics.memory.used > 50 * 1024 * 1024) { // 50MB
    score -= Math.min(20, (metrics.memory.used - 50 * 1024 * 1024) / (1024 * 1024))
  }
  
  // Deduct points for large bundle size
  if (metrics.bundleSize.estimated > 1024 * 1024) { // 1MB
    score -= Math.min(20, (metrics.bundleSize.estimated - 1024 * 1024) / (100 * 1024))
  }
  
  return Math.max(0, Math.round(score))
}

/**
 * Reset all metrics
 */
const resetMetrics = () => {
  metrics.interactions = {}
  metrics.memory = { used: 0, total: 0, timestamp: Date.now() }
  metrics.loadingTimes = { components: {}, routes: {}, assets: {} }
  updatePerformanceMetrics()
  updateBundleSizeMetrics()
}

/**
 * Start automatic monitoring
 */
const startMonitoring = () => {
  // Update metrics every 30 seconds
  const interval = setInterval(() => {
    updateMemoryMetrics()
    updatePerformanceMetrics()
    updateBundleSizeMetrics()
  }, 30000)
  
  // Initial update
  updateMemoryMetrics()
  updatePerformanceMetrics()
  updateBundleSizeMetrics()
  
  return () => clearInterval(interval)
}

// Performance measurement tracking
const activeMeasurements = new Map()

/**
 * Start a performance measurement
 * @param {string} name - Name of the measurement
 * @returns {string} Measurement ID
 */
const startMeasurement = name => {
  const measurementId = `${name}-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`
  const startTime = performance.now()
  
  activeMeasurements.set(measurementId, {
    name,
    startTime,
    startMark: `${measurementId}-start`,
  })
  
  // Create performance mark for browser performance tools
  if (typeof performance !== 'undefined' && performance.mark) {
    performance.mark(`${measurementId}-start`)
  }
  
  return measurementId
}

/**
 * End a performance measurement
 * @param {string} measurementId - The measurement ID returned by startMeasurement
 * @returns {number} Duration in milliseconds
 */
const endMeasurement = measurementId => {
  const measurement = activeMeasurements.get(measurementId)
  if (!measurement) {
    if (import.meta.env.DEV) {
      console.warn(`No active measurement found for ID: ${measurementId}`)
    }
    
    return 0
  }
  
  const endTime = performance.now()
  const duration = endTime - measurement.startTime
  
  // Create performance mark and measure for browser performance tools
  if (typeof performance !== 'undefined' && performance.mark && performance.measure) {
    performance.mark(`${measurementId}-end`)
    performance.measure(measurement.name, measurement.startMark, `${measurementId}-end`)
  }
  
  // Store the measurement result
  if (!metrics.performance.measurements) {
    metrics.performance.measurements = {}
  }
  if (!metrics.performance.measurements[measurement.name]) {
    metrics.performance.measurements[measurement.name] = []
  }
  
  metrics.performance.measurements[measurement.name].push({
    duration,
    timestamp: Date.now(),
  })
  
  // Clean up
  activeMeasurements.delete(measurementId)
  
  return duration
}

/**
 * Measure component performance (convenience wrapper)
 * @param {string} componentName - Name of the component
 * @param {Function} fn - Function to measure
 * @returns {Promise} Promise resolving to the function result
 */
const measureComponent = async (componentName, fn) => {
  const measurementId = startMeasurement(`component-${componentName}`)
  
  try {
    const result = await fn()
    const duration = endMeasurement(measurementId)
    
    // Track component load time
    trackComponentLoad(componentName, duration)
    
    return result
  } catch (error) {
    endMeasurement(measurementId) // Still end measurement on error
    throw error
  }
}

/**
 * Export performance monitor object
 */
export const performanceMonitor = {
  getMetrics,
  trackInteraction,
  trackComponentLoad,
  trackRouteLoad,
  resetMetrics,
  startMonitoring,
  calculatePerformanceScore,
  startMeasurement,
  endMeasurement,
  measureComponent,
}

// Auto-start monitoring if in browser environment
if (typeof window !== 'undefined') {
  // Start monitoring after page load
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', startMonitoring)
  } else {
    startMonitoring()
  }
}

export default performanceMonitor
