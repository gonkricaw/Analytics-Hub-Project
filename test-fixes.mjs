#!/usr/bin/env node

/**
 * Quick test to verify the fixes are working
 */

console.log('🔧 Testing Accessibility Fixes')

try {
  // Test 1: Color contrast function fix
  console.log('\n1. Testing Color Contrast isWCAGCompliant function...')
  const colorContrastModule = await import('./resources/js/composables/useColorContrast.js')
  const { useColorContrast } = colorContrastModule
  
  if (typeof useColorContrast === 'function') {
    const { isWCAGCompliant, calculateContrast } = useColorContrast()
    
    // Test that isWCAGCompliant returns boolean
    const result = isWCAGCompliant('#ffffff', '#000000', 'AA')
    console.log(`   - isWCAGCompliant return type: ${typeof result}`)
    console.log(`   - isWCAGCompliant result: ${result}`)
    
    if (typeof result === 'boolean') {
      console.log('   ✅ isWCAGCompliant now returns boolean correctly')
    } else {
      console.log('   ❌ isWCAGCompliant still returns object')
    }
    
    // Test calculateContrast
    const contrast = calculateContrast('#ffffff', '#000000')
    console.log(`   - Contrast ratio: ${contrast}`)
    
    if (typeof contrast === 'number' && contrast > 20) {
      console.log('   ✅ calculateContrast working correctly')
    } else {
      console.log('   ❌ calculateContrast issue')
    }
  }
  
  // Test 2: Cross-browser utils missing functions
  console.log('\n2. Testing Cross-Browser Utils missing functions...')
  const crossBrowserModule = await import('./resources/js/utils/crossBrowserUtils.js')
  const { 
    supportsIntersectionObserver, 
    supportsResizeObserver, 
    supportsWebWorkers, 
    supportsServiceWorkers 
  } = crossBrowserModule
  
  const functions = [
    { name: 'supportsIntersectionObserver', fn: supportsIntersectionObserver },
    { name: 'supportsResizeObserver', fn: supportsResizeObserver },
    { name: 'supportsWebWorkers', fn: supportsWebWorkers },
    { name: 'supportsServiceWorkers', fn: supportsServiceWorkers }
  ]
  
  functions.forEach(({ name, fn }) => {
    if (typeof fn === 'function') {
      const result = fn()
      console.log(`   ✅ ${name}: ${typeof result} (${result})`)
    } else {
      console.log(`   ❌ ${name}: not found or not a function`)
    }
  })
  
  // Test 3: Performance monitor functions
  console.log('\n3. Testing Performance Monitor functions...')
  const performanceModule = await import('./resources/js/utils/performanceMonitor.js')
  const { performanceMonitor } = performanceModule
  
  const perfFunctions = ['startMeasurement', 'endMeasurement', 'measureComponent']
  perfFunctions.forEach(funcName => {
    if (typeof performanceMonitor[funcName] === 'function') {
      console.log(`   ✅ ${funcName}: function available`)
    } else {
      console.log(`   ❌ ${funcName}: not found`)
    }
  })
  
  console.log('\n🎉 Fix validation complete!')
  
} catch (error) {
  console.error('❌ Test failed:', error.message)
  console.error(error.stack)
}
