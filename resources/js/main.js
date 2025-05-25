import App from '@/App.vue'
import { setupGlobalErrorHandler } from '@/composables/useErrorHandler'
import { registerPlugins } from '@core/utils/plugins'
import { createApp } from 'vue'

// Styles
import '@core-scss/template/index.scss'
import '@styles/styles.scss'

// Setup global error handling
setupGlobalErrorHandler()

// Create vue app
const app = createApp(App)

// Global error handler for Vue
app.config.errorHandler = (error, instance, info) => {
  console.error('Vue Error:', error)
  console.error('Component Info:', info)
  
  // Report to error handler if available
  if (window.useErrorHandler) {
    window.useErrorHandler().addError(error, {
      type: 'vueError',
      componentInfo: info,
      componentName: instance?.$options?.name,
    })
  }
}

// Register plugins
registerPlugins(app)

// Mount vue app
app.mount('#app')
