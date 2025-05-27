<script setup>
import { useMaintenanceMode } from '@/composables/useMaintenanceMode'
import MaintenancePage from '@/pages/maintenance.vue'
import { useSystemConfigStore } from '@/stores/systemConfig'
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
    await systemConfigStore.initialize()
  } catch (error) {
    console.warn('Failed to initialize system configurations:', error)
  }
})
</script>

<template>
  <VLocaleProvider :rtl="configStore.isAppRTL">
    <!-- ℹ️ This is required to set the background color of active nav link based on currently active global theme's primary -->
    <VApp :style="`--v-global-theme-primary: ${hexToRgb(global.current.value.colors.primary)}`">
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
