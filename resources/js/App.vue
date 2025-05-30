<script setup>
import SkipLinks from '@/components/accessibility/SkipLinks.vue'
import { useAccessibility } from '@/composables/useAccessibility'
import { useColorContrast } from '@/composables/useColorContrast'
import { useMaintenanceMode } from '@/composables/useMaintenanceMode'
import MaintenancePage from '@/pages/maintenance.vue'
import { useSystemConfigStore } from '@/stores/systemConfig'
import { performanceMonitor } from '@/utils/performanceOptimizer'
import ScrollToTop from '@core/components/ScrollToTop.vue'
import initCore from '@core/initCore'
import {
    initConfigStore,
    useConfigStore,
} from '@core/stores/config'
import { hexToRgb } from '@core/utils/colorConverter'
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useTheme } from 'vuetify'

const { global } = useTheme()
const route = useRoute()

// ℹ️ Sync current theme with initial loader theme
initCore()
initConfigStore()

const configStore = useConfigStore()
const systemConfigStore = useSystemConfigStore()
const { isMaintenanceMode, canBypassMaintenance } = useMaintenanceMode()
const { accessibilityClasses, initAccessibility } = useAccessibility()
const { initContrastMonitoring, validateThemeContrast, generateAccessibilityReport } = useColorContrast()

// Computed property to determine if maintenance mode should be shown
const shouldShowMaintenance = computed(() => {
  // Don't show maintenance mode if:
  // 1. Maintenance mode is disabled
  // 2. User can bypass maintenance mode (admin)
  // 3. Current route is already the maintenance page (prevent infinite loop)
  return isMaintenanceMode.value && 
         !canBypassMaintenance.value && 
         route.path !== '/maintenance'
})

// Initialize system configurations on app mount
onMounted(async () => {
  try {
    // Initialize accessibility features
    initAccessibility()
    
    // Initialize color contrast monitoring
    initContrastMonitoring()
    
    // Initialize system configurations with retry logic
    let retries = 3
    while (retries > 0) {
      try {
        await systemConfigStore.initialize()
        break
      } catch (configError) {
        retries--
        if (retries === 0) {
          // Log critical errors only
          if (import.meta.env.DEV) {
            console.error('All system configuration initialization attempts failed:', configError)
          }
        } else {
          await new Promise(r => setTimeout(r, 1000)) // Wait 1 second before retrying
        }
      }
    }
    
    // Start performance monitoring
    performanceMonitor.getMetrics()
    
    // Validate theme contrast after a brief delay to ensure DOM is ready
    setTimeout(() => {
      validateThemeContrast()
      
      // Generate accessibility report in development mode only
      if (import.meta.env.DEV) {
        const report = generateAccessibilityReport()
        if (report.failed > 0) {
          console.warn('Color Contrast Issues Detected:', report)
        } else {
          console.log('✅ All color contrast checks passed')
        }
      }
    }, 1000)
  } catch (error) {
    // Only log critical errors in development
    if (import.meta.env.DEV) {
      console.error('Failed to initialize app:', error)
    }
  }
})
</script>

<template>
  <VLocaleProvider :rtl="configStore.isAppRTL">
    <!-- ℹ️ This is required to set the background color of active nav link based on currently active global theme's primary -->
    <VApp 
      :style="`--v-global-theme-primary: ${hexToRgb(global.current.value.colors.primary)}`"
      :class="accessibilityClasses"
    >
      <!-- Skip Links for Accessibility -->
      <SkipLinks />
      
      <!-- Show maintenance page if maintenance mode is active and user cannot bypass -->
      <MaintenancePage v-if="shouldShowMaintenance" />
      
      <!-- Normal application flow -->
      <template v-else>
        <RouterView />
        <ScrollToTop />
      </template>
    </VApp>
  </VLocaleProvider>
</template>
