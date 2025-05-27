import { useSystemConfiguration } from '@/composables/useSystemConfiguration'
import { defineStore } from 'pinia'
import { computed, readonly, ref } from 'vue'

export const useSystemConfigStore = defineStore('systemConfig', () => {
  const { getPublicConfigurations } = useSystemConfiguration()
  
  // State
  const configurations = ref({})
  const isLoading = ref(false)
  const error = ref(null)
  const lastFetched = ref(null)
  
  // Cache duration in milliseconds (5 minutes)
  const CACHE_DURATION = 5 * 60 * 1000
  
  // Computed getters for specific configuration groups
  const appBranding = computed(() => ({
    app_name: configurations.value['app.name']?.value || 'Indonet Analytics Hub',
    name: configurations.value['app.name']?.value || 'Indonet Analytics Hub', // Alias for backward compatibility
    logo: configurations.value['app.logo']?.value || null,
    favicon: configurations.value['app.favicon']?.value || null,
    footerText: configurations.value['app.footer_text']?.value || null,
  }))
  
  const dashboardConfig = computed(() => ({
    jumbotron: {
      enabled: configurations.value['dashboard.jumbotron.enabled']?.value || true,
      slides: configurations.value['dashboard.jumbotron.slides']?.value || [],
      settings: configurations.value['dashboard.jumbotron.settings']?.value || {
        autoplay: true,
        interval: 5000,
        indicators: true,
        controls: true,
      },
    },
    marquee: {
      enabled: configurations.value['dashboard.marquee.enabled']?.value || true,
      text: configurations.value['dashboard.marquee.text']?.value || '',
      speed: configurations.value['dashboard.marquee.speed']?.value || 'medium',
      pausable: configurations.value['dashboard.marquee.pausable']?.value || true,
    },
  }))
  
  const loginConfig = computed(() => ({
    welcome_message: configurations.value['login.welcome_message']?.value || 'Welcome to Indonet Analytics Hub',
    subtitle: configurations.value['login.subtitle']?.value || 'Please sign in to your account',
    background_image: configurations.value['login.background_image']?.value || null,
    show_logo: configurations.value['login.show_logo']?.value !== false,
    custom_css: configurations.value['login.custom_css']?.value || '',
  }))
  
  const generalSettings = computed(() => ({
    maintenanceMode: configurations.value['general.maintenance_mode']?.value || false,
    maintenanceMessage: configurations.value['general.maintenance_message']?.value || 'System is under maintenance',
    timezone: configurations.value['general.timezone']?.value || 'UTC',
    dateFormat: configurations.value['general.date_format']?.value || 'Y-m-d',
    timeFormat: configurations.value['general.time_format']?.value || 'H:i:s',
  }))
  
  // Actions
  const fetchConfigurations = async (force = false) => {
    // Check cache validity unless forced
    if (!force && lastFetched.value) {
      const now = new Date().getTime()
      const timeSinceLastFetch = now - lastFetched.value
      if (timeSinceLastFetch < CACHE_DURATION) {
        return configurations.value
      }
    }
    
    isLoading.value = true
    error.value = null
    
    try {
      const response = await getPublicConfigurations()
      
      // Transform array of configurations to object for easier access
      const configObj = {}
      if (response.data && Array.isArray(response.data)) {
        response.data.forEach(config => {
          configObj[config.key] = config
        })
      }
      
      configurations.value = configObj
      lastFetched.value = new Date().getTime()
      
      return configurations.value
    } catch (err) {
      error.value = err.message || 'Failed to fetch system configurations'
      console.error('Failed to fetch system configurations:', err)
      throw err
    } finally {
      isLoading.value = false
    }
  }
  
  const getConfigValue = (key, defaultValue = null) => {
    return configurations.value[key]?.value || defaultValue
  }
  
  const getConfigByGroup = (groupPrefix) => {
    const groupConfigs = {}
    Object.entries(configurations.value).forEach(([key, config]) => {
      if (key.startsWith(groupPrefix + '.')) {
        const subKey = key.substring(groupPrefix.length + 1)
        groupConfigs[subKey] = config.value
      }
    })
    return groupConfigs
  }
  
  const refreshCache = () => {
    return fetchConfigurations(true)
  }
  
  const clearCache = () => {
    configurations.value = {}
    lastFetched.value = null
    error.value = null
  }
  
  // Initialize on first access
  const initialize = async () => {
    if (Object.keys(configurations.value).length === 0) {
      try {
        await fetchConfigurations()
      } catch (err) {
        // Silently fail on initialization to prevent blocking app load
        console.warn('Failed to initialize system configurations:', err)
      }
    }
  }
  
  return {
    // State
    configurations: readonly(configurations),
    isLoading: readonly(isLoading),
    error: readonly(error),
    lastFetched: readonly(lastFetched),
    
    // Computed
    appBranding,
    dashboardConfig,
    loginConfig,
    generalSettings,
    
    // Actions
    fetchConfigurations,
    getConfigValue,
    getConfigByGroup,
    refreshCache,
    clearCache,
    initialize,
  }
})
